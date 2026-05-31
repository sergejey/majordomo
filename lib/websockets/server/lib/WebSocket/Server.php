<?php

namespace WebSocket;

/**
 * Shiny WSS
 *
 * @author Nico Kaiser <nico@kaiser.me>
 * @author Simon Samtleben <web@lemmingzshadow.net>
 */
class Server extends Socket
{
        protected $clients = [];
        /**
         * map of client socket id => last activity timestamp
         * used for timeout/disconnect of stalled clients
         * @var array
         */
        protected $clientActivity = [];
        protected $applications   = [];
        private $_ipStorage       = [];
        private $_requestStorage  = [];

        // server settings:
        private $_checkOrigin          = true;
        private $_allowedOrigins       = [];
        private $_maxClients           = 30;
        private $_maxConnectionsPerIp  = 5;
        private $_maxRequestsPerMinute = 50;
        /**
         * Timeout for inactive clients in seconds
         */
        private $_clientTimeout = 30;

        public function __construct($host = 'localhost', $port = 8000, $ssl = false)
        {
                parent::__construct($host, $port, $ssl);
                $this->log('Server created');
        }

        /**
         * Set client inactivity timeout (seconds)
         * @param int $seconds
         */
        public function setClientTimeout($seconds)
        {
                $this->_clientTimeout = (int) $seconds;
        }

        /**
         * Creates a connection from a socket resource
         *
         * @param resource $resource A socket resource
         * @return Connection
         */
        protected function createConnection($resource)
        {
                return new Connection($this, $resource);
        }

        /**
         * Main server method. Listens for connections, handles connectes/disconnectes, e.g.
         */
        public function run()
        {
                while (true) {
                        // periodically check for timed-out clients
                        $now = time();
                        foreach ($this->clientActivity as $sockId => $ts) {
                                if ($now - $ts > $this->_clientTimeout) {
                                        $client = $this->clients[(int) $sockId] ?? null;
                                        if ($client) {
                                                $this->log("[info] Client {$client->getClientIp()} timed out (>{$this->_clientTimeout}s), disconnecting");
                                                $client->onDisconnect();
                                                // removeClientOnClose will be called by close()/onDisconnect(), but ensure cleanup
                                                $this->_removeSocket($client->getClientSocket());
                                        }
                                        unset($this->clientActivity[$sockId]);
                                }
                        }

                        $changed_sockets = $this->allsockets;
                        $write           = null;
                        $except          = null;

                        $num_changed = @stream_select($changed_sockets, $write, $except, 0, 5000);
                        if ($num_changed === false) {
                                $this->log('[warn] stream_select() failed, cleaning sockets');
                                foreach ($this->clients as $key => $client) {
                                        if (! isset($this->allsockets[$key]) || ! is_resource($key)) {
                                                $this->removeClientOnError($client);
                                                unset($this->clients[$key]);
                                        }
                                }
                                $this->allsockets = array_values(array_filter($this->allsockets, 'is_resource'));
                                continue;
                        }

                        foreach ($changed_sockets as $socket) {
                                if ($socket === $this->master) {
                                        $ressource = @stream_socket_accept($this->master, 0);
                                        if ($ressource === false) {
                                                $this->log('[warn] Socket accept failed');
                                                continue;
                                        }
                                        $client                          = $this->createConnection($ressource);
                                        $this->clients[(int) $ressource] = $client;
                                        $this->allsockets[]              = $ressource;
                                        // set initial activity timestamp
                                        $this->clientActivity[(int) $ressource] = time();

                                        if (count($this->clients) > $this->_maxClients) {
                                                $this->log('Attention: Client Limit Reached!');
                                                $client->onDisconnect();
                                                if ($this->getApplication('status') !== false) {
                                                        $this->getApplication('status')->statusMsg('Attention: Client Limit Reached!', 'warning');
                                                }
                                                continue;
                                        }

                                        $this->_addIpToStorage($client->getClientIp());
                                        if ($this->_checkMaxConnectionsPerIp($client->getClientIp()) === false) {
                                                $this->log('Connection/Ip limit for ip ' . $client->getClientIp() . ' was reached!');
                                                $client->onDisconnect();
                                                if ($this->getApplication('status') !== false) {
                                                        $this->getApplication('status')->statusMsg('Connection/Ip limit for ip ' . $client->getClientIp() . ' was reached!', 'warning');
                                                }
                                                continue;
                                        }
                                } else {
                                        $client = $this->clients[(int) $socket] ?? null;
                                        if (! $client) {
                                                unset($this->clients[(int) $socket]);
                                                $this->allsockets = array_filter($this->allsockets, function ($s) use ($socket) {
                                                    return $s !== $socket;
                                                });
                                                continue;
                                        }

                                        $data = $this->readBuffer($socket);
                                        if ($data === false) {
                                                $this->removeClientOnError($client);
                                                $this->_removeSocket($socket);
                                                continue;
                                        }

                                        // update last activity timestamp on any successful read
                                        $this->clientActivity[(int) $socket] = time();

                                        $bytes = strlen($data);
                                        if ($bytes === 0) {
                                                $client->onDisconnect();
                                                $this->_removeSocket($socket);
                                                continue;
                                        }

                                        if ($client->waitingForData === false && $this->_checkRequestLimit($client->getClientId()) === false) {
                                                $client->onDisconnect();
                                                unset($this->clients[(int) $socket]);
                                                $this->allsockets = array_filter($this->allsockets, function ($s) use ($socket) {
                                                    return $s !== $socket;
                                                });
                                                continue;
                                        }

                                        // preserve legacy debug behavior but improve binary preview
                                        if (defined('DEBUG_WEBSOCKETS') && constant('DEBUG_WEBSOCKETS') === "1") {
                                                if ($this->isJson($data)) {
                                                        $preview = substr($data, 0, 100);
                                                        $this->log("[debug] Received JSON from {$client->getClientIp()}: $preview (len: $bytes)");
                                                } else {
                                                        $preview = $this->binaryPreview($data, 32);
                                                        $this->log("[debug] Received binary/non-JSON from {$client->getClientIp()}, length: $bytes, preview: $preview (Mem: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " mb)");
                                                }
                                        } else {
                                                // normal info logging for binary payloads
                                                if (! $this->isJson($data)) {
                                                        $preview = $this->binaryPreview($data, 24);
                                                        $this->log("[info] Received binary/non-JSON from {$client->getClientIp()}, length: $bytes, preview: $preview (Mem: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " mb)");
                                                }
                                        }

                                        $client->onData($data);
                                }
                        }
                }
        }

        protected function isJson($string)
        {
                if (! is_string($string)) {
                        return false;
                }

                json_decode($string);
                return (json_last_error() === JSON_ERROR_NONE);
        }

        protected function _removeSocket($socket)
        {
                $key = array_search($socket, $this->allsockets, true);
                if ($key !== false) {
                        unset($this->allsockets[$key]);
                        // reindex to keep numeric array consistent
                        $this->allsockets = array_values($this->allsockets);
                }
                $clientId = (int) $socket;
                if (isset($this->clients[$clientId])) {
                        unset($this->clients[$clientId]);
                }

                if (isset($this->clientActivity[$clientId])) {
                        unset($this->clientActivity[$clientId]);
                }

                if (is_resource($socket)) {
                        @fclose($socket);
                }
        }

        /**
         * Returns a server application.
         *
         * @param string $key Name of application.
         * @return object The application object.
         */
        public function getApplication($key)
        {
                if (empty($key)) {
                        return false;
                }
                if (array_key_exists($key, $this->applications)) {
                        return $this->applications[$key];
                }
                return false;
        }

        /**
         * Adds a new application object to the application storage.
         *
         * @param string $key Name of application.
         * @param object $application The application object.
         */
        public function registerApplication($key, $application)
        {
                $this->applications[$key] = $application;

                // status is kind of a system-app, needs some special cases:
                if ($key === 'status') {
                        $serverInfo = [
                                'maxClients'          => $this->_maxClients,
                                'maxConnectionsPerIp' => $this->_maxConnectionsPerIp,
                                'maxRequetsPerMinute' => $this->_maxRequestsPerMinute,
                        ];
                        $this->applications[$key]->setServerInfo($serverInfo);
                }
        }

        /**
         * Echos a message to standard output.
         *
         * @param string $message Message to display.
         * @param string $type Type of message.
         */
        public function log($message, $type = 'info')
        {
                $mem = round(memory_get_usage(true) / 1024 / 1024, 2) . ' mb';
                echo date('Y-m-d H:i:s') . ' [' . ($type ? $type : 'error') . '] ' . $message . ' (Mem: ' . $mem . ')' . PHP_EOL;
        }

        /**
         * Removes a client from client storage.
         *
         * @param Object $client Client object.
         */
        public function removeClientOnClose($client)
        {
                $clientId   = $client->getClientId();
                $clientIp   = $client->getClientIp();
                $clientPort = $client->getClientPort();
                $resource   = $client->getClientSocket();

                $this->_removeIpFromStorage($client->getClientIp());
                if (isset($this->_requestStorage[$clientId])) {
                        unset($this->_requestStorage[$clientId]);
                }
                // central removal helper will close socket and unset arrays
                $this->_removeSocket($resource);
                unset($this->clients[(int) $resource]);
                if (isset($this->clientActivity[(int) $resource])) {
                        unset($this->clientActivity[(int) $resource]);
                }

                unset($client);

                // trigger status application:
                if ($this->getApplication('status') !== false) {
                        $this->getApplication('status')->clientDisconnected($clientIp, $clientPort);
                }
                unset($clientId, $clientIp, $clientPort, $resource);
        }

        /**
         * Removes a client and all references in case of timeout/error.
         * @param object $client The client object to remove.
         */
        public function removeClientOnError($client)
        { // remove reference in clients app:
                if ($client->getClientApplication() !== false) {
                        $client->getClientApplication()->onDisconnect($client);
                }

                $resource   = $client->getClientSocket();
                $clientId   = $client->getClientId();
                $clientIp   = $client->getClientIp();
                $clientPort = $client->getClientPort();
                $this->_removeIpFromStorage($client->getClientIp());
                if (isset($this->_requestStorage[$clientId])) {
                        unset($this->_requestStorage[$clientId]);
                }
                // central removal helper will close socket and unset arrays
                $this->_removeSocket($resource);
                unset($this->clients[(int) $resource]);
                if (isset($this->clientActivity[(int) $resource])) {
                        unset($this->clientActivity[(int) $resource]);
                }

                unset($client);

                // trigger status application:
                if ($this->getApplication('status') !== false) {
                        $this->getApplication('status')->clientDisconnected($clientIp, $clientPort);
                }
                unset($resource, $clientId, $clientIp, $clientPort);
        }

        /**
         * Checks if the submitted origin (part of websocket handshake) is allowed
         * to connect. Allowed origins can be set at server startup.
         *
         * @param string $domain The origin-domain from websocket handshake.
         * @return bool If domain is allowed to connect method returns true.
         */
        public function checkOrigin($domain)
        {
                $domain = str_replace('http://', '', $domain);
                $domain = str_replace('https://', '', $domain);
                $domain = str_replace('www.', '', $domain);
                $domain = str_replace('/', '', $domain);

                return isset($this->_allowedOrigins[$domain]);
        }

        /**
         * Adds a new ip to ip storage.
         *
         * @param string $ip An ip address.
         */
        private function _addIpToStorage($ip)
        {
                if (isset($this->_ipStorage[$ip])) {
                        $this->_ipStorage[$ip]++;
                } else {
                        $this->_ipStorage[$ip] = 1;
                }
        }

        /**
         * Removes an ip from ip storage.
         *
         * @param string $ip An ip address.
         * @return bool True if ip could be removed.
         */
        private function _removeIpFromStorage($ip)
        {
                if (! isset($this->_ipStorage[$ip])) {
                        return false;
                }
                if ($this->_ipStorage[$ip] === 1) {
                        unset($this->_ipStorage[$ip]);
                        return true;
                }
                $this->_ipStorage[$ip]--;

                return true;
        }

        /**
         * Checks if an ip has reached the maximum connection limit.
         *
         * @param string $ip An ip address.
         * @return bool False if ip has reached max. connection limit. True if connection is allowed.
         */
        private function _checkMaxConnectionsPerIp($ip)
        {
                if (empty($ip)) {
                        return false;
                }
                if (! isset($this->_ipStorage[$ip])) {
                        return true;
                }
                return ($this->_ipStorage[$ip] > $this->_maxConnectionsPerIp) ? false : true;
        }

        /**
         * Checkes if a client has reached its max. requests per minute limit.
         *
         * @param string $clientId A client id. (unique client identifier)
         * @return bool True if limit is not yet reached. False if request limit is reached.
         */
        private function _checkRequestLimit($clientId)
        {
                // no data in storage - no danger:
                if (! isset($this->_requestStorage[$clientId])) {
                        $this->_requestStorage[$clientId] = [
                                'lastRequest'   => time(),
                                'totalRequests' => 1,
                        ];
                        return true;
                }

                // time since last request > 1min - no danger:
                if (time() - $this->_requestStorage[$clientId]['lastRequest'] > 60) {
                        $this->_requestStorage[$clientId] = [
                                'lastRequest'   => time(),
                                'totalRequests' => 1,
                        ];
                        return true;
                }

                // did requests in last minute - check limits:
                if ($this->_requestStorage[$clientId]['totalRequests'] > $this->_maxRequestsPerMinute) {
                        return false;
                }

                $this->_requestStorage[$clientId]['totalRequests']++;
                return true;
        }

        /**
         * Set whether the client origin should be checked on new connections.
         *
         * @param bool $doOriginCheck
         * @return bool True if value could validated and set successfully.
         */
        public function setCheckOrigin($doOriginCheck)
        {
                if (is_bool($doOriginCheck) === false) {
                        return false;
                }
                $this->_checkOrigin = $doOriginCheck;
                return true;
        }

        /**
         * Return value indicating if client origins are checked.
         * @return bool True if origins are checked.
         */
        public function getCheckOrigin()
        {
                return $this->_checkOrigin;
        }

        /**
         * Adds a domain to the allowed origin storage.
         *
         * @param string $domain A domain name from which connections to server are allowed.
         * @return bool True if domain was added to storage.
         */
        public function setAllowedOrigin($domain)
        {
                $domain = str_replace('http://', '', $domain);
                $domain = str_replace('www.', '', $domain);
                $domain = (strpos($domain, '/') !== false) ? substr($domain, 0, strpos($domain, '/')) : $domain;
                if (empty($domain)) {
                        return false;
                }
                $this->_allowedOrigins[$domain] = true;
                return true;
        }

        /**
         * Create a short preview for binary data: hex snippet + printable ascii
         * @param string $data
         * @param int $maxBytes how many bytes to preview
         * @return string
         */
        protected function binaryPreview($data, $maxBytes = 32)
        {
                if (! is_string($data) || $data === '') {
                        return '';
                }

                $slice = substr($data, 0, $maxBytes);
                $hex   = bin2hex($slice);
                // printable ascii fallback
                $ascii = preg_replace('/[^\x20-\x7E]/', '.', $slice);
                return sprintf('hex=%s ascii=%s%s', substr($hex, 0, 64), $ascii, (strlen($data) > $maxBytes ? '...' : ''));
        }

        /**
         * Sets value for the max. connection per ip to this server.
         *
         * @param int $limit Connection limit for an ip.
         * @return bool True if value could be set.
         */
        public function setMaxConnectionsPerIp($limit)
        {
                if (! is_int($limit)) {
                        return false;
                }
                $this->_maxConnectionsPerIp = $limit;
                return true;
        }

        /**
         * Returns the max. connections per ip value.
         *
         * @return int Max. simoultanous  allowed connections for an ip to this server.
         */
        public function getMaxConnectionsPerIp()
        {
                return $this->_maxConnectionsPerIp;
        }

        /**
         * Sets how many requests a client is allowed to do per minute.
         *
         * @param int $limit Requets/Min limit (per client).
         * @return bool True if value could be set.
         */
        public function setMaxRequestsPerMinute($limit)
        {
                if (! is_int($limit)) {
                        return false;
                }
                $this->_maxRequestsPerMinute = $limit;
                return true;
        }

        /**
         * Sets how many clients are allowed to connect to server until no more
         * connections are accepted.
         *
         * @param in $max Max. total connections to server.
         * @return bool True if value could be set.
         */
        public function setMaxClients($max)
        {
                if ((int) $max === 0) {
                        return false;
                }
                $this->_maxClients = (int) $max;
                return true;
        }

        /**
         * Returns total max. connection limit of server.
         *
         * @return int Max. connections to this server.
         */
        public function getMaxClients()
        {
                return $this->_maxClients;
        }
}
