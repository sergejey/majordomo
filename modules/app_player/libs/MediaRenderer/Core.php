<?php

class Core {

    private $user_agent;
    public $cache;

    public function __construct()
    {
        $this->user_agent = 'Majordomo/ver-x.x UDAP/2.0 Win/7';
    }
    
    public function search($ip='239.255.255.250', $st = 'ssdp:all', $mx = 2, $man = 'ssdp:discover', $from = null, $port = null, $sockTimout = '1')
    {
        //create the socket
    	$socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true);
        $request = 'M-SEARCH * HTTP/1.1'."\r\n";
        $request .= 'HOST: 239.255.255.250:1900'."\r\n";
        $request .= 'MAN: "'.$man.'"'."\r\n";
        $request .= 'MX: '.$mx.''."\r\n";
        $request .= 'ST: '.$st.''."\r\n";
        $request .= 'USER-AGENT: '.$this->user_agent."\r\n";
        $request .= "\r\n";
		
        // search device of other net
        socket_sendto($socket, $request, strlen($request), 0, $ip, 1900);

        // send the data from socket
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>$sockTimout, 'usec'=>'128'));
        $response = array();
        do {
            $buf = null;
            if (($len = @socket_recvfrom($socket, $buf, 2048, 0, $ip, $port)) == -1) {
                echo "socket_read() failed: " . socket_strerror(socket_last_error()) . "\n";
            }
            if(!is_null($buf)){
                $data = $this->parseSearchResponse($buf);
                $response[$data['usn']] = $data;
            }
        } while(!is_null($buf));
        socket_close($socket);

        return $response;
    }

 
   
    private function parseSearchResponse($response)
    {
        //var_dump($response);
        $messages = explode("\r\n", $response);
        $parsedResponse = array();
        foreach( $messages as $row ) {
            if( stripos( $row, 'http' ) === 0 )
                $parsedResponse['http'] = $row;
            if( stripos( $row, 'cach' ) === 0 )
                $parsedResponse['cache-control'] = str_ireplace( 'cache-control: ', '', $row );
            if( stripos( $row, 'date') === 0 )
                $parsedResponse['date'] = str_ireplace( 'date: ', '', $row );
            if( stripos( $row, 'ext') === 0 )
                $parsedResponse['ext'] = str_ireplace( 'ext: ', '', $row );
            if( stripos( $row, 'loca') === 0 )
                $parsedResponse['location'] = str_ireplace( 'location: ', '', $row );
            if( stripos( $row, 'serv') === 0 )
                $parsedResponse['server'] = str_ireplace( 'server: ', '', $row );
            if( stripos( $row, 'st:') === 0 )
                $parsedResponse['st'] = str_ireplace( 'st: ', '', $row );
            if( stripos( $row, 'usn:') === 0 )
                $parsedResponse['usn'] = str_ireplace( 'usn: ', '', $row );
            if( stripos( $row, 'cont') === 0 )
                $parsedResponse['content-length'] = str_ireplace( 'content-length: ', '', $row );
        }
        $parsedResponse['description'] = $this->getDescription($parsedResponse['location']);
        return $parsedResponse;
    }
    
    public function getDescription($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        libxml_use_internal_errors(true); 
        $xml = simplexml_load_string($content);
        $json = json_encode($xml);
        $desc = (array)json_decode($json, true);
        curl_close($ch);
        return $desc;
    }

    public function getHeader($url)
    {
//        var_dump($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
//        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        var_dump($httpCode);
        $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
//        var_dump($content);
        $header = substr($content, 0, $size);
        curl_close($ch);
//        var_dump($header);
        $messages = explode("\r\n", $header);
        $parsed = [];
        foreach($messages as $m){
            //            var_dump($m);
            if(count(explode(':',$m))>1){
                list($param, $value) = explode(':',$m, 2);
                $parsed[$param] = $value;
            }
            else{
                $parsed[$m] = $m;
            }
        }
        $parsed['httpCode'] = $httpCode;
        return $parsed;
    }

    public function baseUrl($url)
    {
        $url = parse_url($url);
        return $url['scheme'].'://'.$url['host'].':'.$url['port'];
    }

    public function setUserAgent($agent)
    {
        $this->user_agent = $agent;
    }
    //получаем hostname адрес локального компьютера
    private function getLocalIp() { 
      return gethostbyname(trim(`hostname`)); 
    }
}
