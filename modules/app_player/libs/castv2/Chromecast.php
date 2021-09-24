<?php

// Chris Ridings
// www.chrisridings.com
require_once ("CCprotoBuf.php");
require_once ("mdns.php");

class GChromecast
{
	function __construct($ip, $port)
	{
		// Establish Chromecast connection
		// Don't pay much attention to the Chromecast's certificate.
		// It'll be for the wrong host address anyway if we
		// use port forwarding!
		$contextOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, ]];
		$context = stream_context_create($contextOptions);
		if ($this->socket = @stream_socket_client('ssl://' . $ip . ":" . $port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context)) {
			stream_set_timeout($this->socket,0,64);
		}
		else {
			throw new Exception("Failed to connect to remote Chromecast");
		}
		$this->requestId = 1;
		if (!$this->ip = $ip) return;
		if (!$this->port = $port) return;
	}
	
	function testLive()
	{
		//return;
		// If there is a difference of 10 seconds or more between $this->lastactivetime and the current time, then we've been kicked off and need to reconnect
		$diff = time() - $this->lastactivetime;
		if ($diff > 15) {
			// Reconnect
			$contextOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, ]];
			$context = stream_context_create($contextOptions);
			if ($this->socket = stream_socket_client('ssl://' . $this->ip . ":" . $this->port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context)) {
				stream_set_timeout($this->socket,5);
			}
			else {
				throw new Exception("Failed to connect to remote Chromecast");
			}
			$this->cc_connect();
			$this->connect();
		}
	}
	
	function cc_connect()
	{

		// CONNECT TO CHROMECAST
		// This connects to the chromecast in general.
		// Generally this is called by launch($appid) automatically upon launching an app
		// but if you want to connect to an existing running application then call this first,
		// then call getStatus() to make sure you get a transportid.
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = "receiver-0";
		$c->urnnamespace = "urn:x-cast:com.google.cast.tp.connection";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"CONNECT"}';
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
	}
	
	function launch($appid)
	{
		// Launches the chromecast app on the connected chromecast
		// CONNECT

		$this->cc_connect();
		$this->getStatus();
		// LAUNCH
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = "receiver-0";
		$c->urnnamespace = "urn:x-cast:com.google.cast.receiver";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"LAUNCH","appId":"' . $appid . '","requestId":' . $this->requestId . '}';

		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
		$oldtransportid = $this->transportid;
		while ($this->transportid == "" || $this->transportid == $oldtransportid) {
			$r = $this->getCastMessage();
			usleep(10);
		}
	}
	
	function getStatus()
	{

		// Get the status of the chromecast in general and return it
		// also fills in the transportId of any currently running app
		$this->cc_connect();
		$this->testLive();
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = "receiver-0";
		$c->urnnamespace = "urn:x-cast:com.google.cast.receiver";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"GET_STATUS","requestId":' . $this->requestId . '}';

		$c = fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->requestId++;
		$r = "";
		while (!preg_match("/RECEIVER_STATUS/s", $r)) {
			$r = $this->getCastMessage();
			$response = substr($r, strpos($r,'{"requestId"'),50000);
		}
		$out = json_decode($response,TRUE);
		$this->appid = $out['status']['applications'][0]['appId'];
		$this->namespaces = $out['status']['applications'][0]['namespaces'];
		$this->sessionId = $out['status']['applications'][0]['sessionId'];
		$this->transportId = $out['status']['applications'][0]['transportId'];
		$this->displayName = $out['status']['applications'][0]['displayName'];
		$this->requestId = $out['requestId'];
		return $out;
	}
	
	function getMediaSession() {
		$this->getStatus(); 
		if ($this->appid != '233637DE' AND is_array($this->namespaces) AND in_array("urn:x-cast:com.google.cast.media", array_column($this->namespaces, 'name'))) {
			$this->connect();
		} else {
			$this->launch('CC1AD845');
			$this->connect();
			$this->getStatus();
		}
		$this->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"GET_STATUS", "requestId":'.$this->requestId.'}');
		$timestart = time();
		while (!preg_match("/\"type\":\"MEDIA_STATUS\"/",$response) ) {
			$count++;
			$response = $this->getCastMessage();
			if (time()- $timestart > 2) break;
		}
	    if ($response){
		    $response = substr($response, strpos($response,'{"type'),50000);
            return json_decode($response,TRUE);
		} else {
			return ;
		}
	}
	
	function connect()
	{
		// This connects to the transport of the currently running app
		// (you need to have launched it yourself or connected and got the status)
		//if ($tl == 0) {
		//	$this->testLive();
		//};
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = $this->transportid;
		$c->urnnamespace = "urn:x-cast:com.google.cast.tp.connection";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"CONNECT"}';

		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
	}
	
	function getCastMessage()
	{
		// Get the Chromecast Message/Response
		// Later on we could update CCprotoBuf to decode this
		// but for now all we need is the transport id  and session id if it is
		// in the packet and we can read that directly.
		$response = fread($this->socket, 2000);
		$timestart = time();
		while (preg_match("/urn:x-cast:com.google.cast.tp.heartbeat/", $response) && preg_match("/\"PING\"/", $response)) {
			$this->pong();
			$response = fread($this->socket, 2000);
			usleep(100);
			if (time()- $timestart > 2) break;
		} 
		// get transport id
		if (preg_match("/transportId/s", $response)) {
			preg_match("/transportId\"\:\"([^\"]*)/", $response, $matches);
			$this->transportid = $matches[1];
		}
		// get app id
		if (preg_match("/appId/s", $response)) {
			preg_match("/appId\"\:\"([^\"]*)/", $response, $matches);
			$this->appid = $matches[1];
		}
		// Grab the mediaSessionId
		if (preg_match("/\"mediaSessionId\":([^\,]*)/",$response,$m)) {
			$this->mediaid = $m[1];
		}
		if (!$this->mediaid) {
			$this->mediaid=1;
		}
		// playaer state
		if (preg_match("/playerState/s", $response)) {
			preg_match("/playerState\"\:\"([^\"]*)/", $response, $matches);
			$this->state = $matches[1];
		}
		// played url
		if (preg_match("/contentId/s", $response)) {
			preg_match("/contentId\"\:\"([^\"]*)/", $response, $matches);
			$this->contentid = $matches[1];
		}
		return $response;
	}
	
	function sendMessage($urn, $message)
	{
		// Send the given message to the given urn

		//$this->testLive();
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = $this->transportid;
		// Override - if the $urn is urn:x-cast:com.google.cast.receiver then
		// send to receiver-0 and not the running app
		if ($urn == "urn:x-cast:com.google.cast.receiver") {
			$c->receiver_id = "receiver-0";
		}
		if ($urn == "urn:x-cast:com.google.cast.tp.connection") {
			$c->receiver_id = "receiver-0";
		}
		$c->urnnamespace = $urn;
		$c->payloadtype = 0;
		$c->payloadutf8 = $message;
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
		$response = $this->getCastMessage();
		return $response;
	}
	
	function pingpong()
	{
		// Officially you should run this every 5 seconds or so to keep
		// the device alive. Doesn't seem to be necessary if an app is running
		// that doesn't have a short timeout.
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = "receiver-0";
		$c->urnnamespace = "urn:x-cast:com.google.cast.tp.heartbeat";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"PING"}';
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
		$response = $this->getCastMessage();
	}
	
	function pong()
	{
		// To answer a pingpong
		$c = new CastMessage();
		$c->source_id = "sender-0";
		$c->receiver_id = "receiver-0";
		$c->urnnamespace = "urn:x-cast:com.google.cast.tp.heartbeat";
		$c->payloadtype = 0;
		$c->payloadutf8 = '{"type":"PONG"}';
		fwrite($this->socket, $c->encode());
		fflush($this->socket);
		$this->lastactivetime = time();
		$this->requestId++;
	}
	
	function SetVolume($volume) {
		// Mute a video
        $this->getMediaSession(); // Auto-reconnects
		$this->sendMessage("urn:x-cast:com.google.cast.receiver", '{"type":"SET_VOLUME", "volume": { "level": ' . $volume . ' }, "requestId":'.$this->requestId.' }');
		$this->getCastMessage();
	}
	
	function seek($secs) {
		// Seek
        $this->getMediaSession(); // Auto-reconnects
		if ($this->mediaid) {
		    $this->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"SEEK", "mediaSessionId":' . $this->mediaid . ', "currentTime":' . $secs . ',"requestId":'.$this->requestId.'}');
		    $this->getCastMessage();
		}
	}
	
	function stop() {
		// Stop
		$this->getMediaSession(); // Auto-reconnects
		if ($this->mediaid) {
			$this->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"STOP", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->requestId.'}');
			$this->getCastMessage();
		}
	}
	
	function load($url, $currentTime) {
		$this->getMediaSession(); // Auto-reconnects
		
		if (preg_match('/\.mp3/', $url)) {
            $content_type = 'audio/mp3';
        } elseif (preg_match('/mp4/', $url)) {
            $content_type = 'video/mp4';
        } elseif (preg_match('/m4a/', $url)) {
            $content_type = 'audio/mp4';
        } elseif (preg_match('/^http/', $url)) {
            $content_type = '';
            if ($fp = fopen($url, 'r')) {
                $meta = stream_get_meta_data($fp);
                if (is_array($meta['wrapper_data'])) {
                    $items = $meta['wrapper_data'];
                    foreach ($items as $line) {
                        if (preg_match('/Content-Type:(.+)/is', $line, $m)) {
                            $content_type = trim($m[1]);
                        }
                    }
                }
                fclose($fp);
            }
        }
        if (!$content_type) {
            $content_type = 'audio/mpeg';
        }
		$json = '{"type":"LOAD","media":{"contentId":"' . $url . '","streamType":"BUFFERED","contentType":"' . $content_type . '"},"autoplay":"false","currentTime":' . $currentTime . ',"requestId":'.$this->requestId.'}';
		$this->sendMessage("urn:x-cast:com.google.cast.media", $json);
		$r = "";
		while (!preg_match("/\"playerState\":\"PLAYING\"/",$r)) {
			$r = $this->getCastMessage();
		}
		// Grab the mediaSessionId
		if (preg_match("/\"mediaSessionId\":([^\,]*)/",$r,$m)) {
			$this->mediaid = $m[1];
			$result = true;
		}
		if (!$this->mediaid) {
			$this->mediaid=1;
			$result = false;
		}
	return $result;
	}
	
	// cheked functions
	function Mute() {
		while (!preg_match("/\"muted\":true/",$r)) {
            $this->requestId++;
			$this->sendMessage("urn:x-cast:com.google.cast.receiver", '{"type":"SET_VOLUME", "volume": { "muted": true }, "requestId":'.$this->requestId.' }');
			$r = $this->getCastMessage();
			usleep(1000);
		}		
		return true;
	}
	
	function UnMute() {
		while (!preg_match("/\"muted\":false/",$r)) {
			$this->requestId++;
            $this->sendMessage("urn:x-cast:com.google.cast.receiver", '{"type":"SET_VOLUME", "volume": { "muted": false }, "requestId":'.$this->requestId.' }');
			$r = $this->getCastMessage();
			usleep(1000);
		}		
		return true;
	}
	function pause() {
		// Pause
		$this->getMediaSession(); // Auto-reconnects
		if ($this->state != 'PAUSED') {
			$this->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"PAUSE", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->requestId.'}');
			$this->getCastMessage();
		} else if ($this->state == 'PAUSED') {
			$this->play();
		}
		return true;
	}
	
	function play() {
		// Restart (after pause)
	    $this->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"PLAY", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->requestId.'}');
		$this->getCastMessage();
		return true;
	}
}
?>
