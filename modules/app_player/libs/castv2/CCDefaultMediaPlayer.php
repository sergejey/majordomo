<?php

// Make it really easy to play videos by providing functions for the Chromecast Default Media Player

require_once("CCBaseSender.php");

class CCDefaultMediaPlayer extends CCBaseSender
{
	public $appid="CC1AD845";
	
	public function play($url,$streamType,$contentType,$autoPlay,$currentTime) {
		// Start a playing
		// First ensure there's an instance of the DMP running
		$this->launch();
		$json = '{"type":"LOAD","media":{"contentId":"' . $url . '","streamType":"' . $streamType . '","contentType":"' . $contentType . '"},"autoplay":' . $autoPlay . ',"currentTime":' . $currentTime . ',"requestId":'.$this->chromecast->requestId.'}';
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media", $json);
		$r = "";
		while (!preg_match("/\"playerState\":\"PLAYING\"/",$r)) {
			$r = $this->chromecast->getCastMessage();
		}
		// Grab the mediaSessionId
		if (preg_match("/\"mediaSessionId\":([^\,]*)/",$r,$m)) {
			$this->mediaid = $m[1];
		}
		if (!$this->mediaid) {
			$this->mediaid=1;
		}
	}

	public function getMediaSession() {
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"GET_STATUS", "requestId":'.$this->chromecast->requestId.'}');
		$request_started=time();
		while ((time()-$request_started)<2 && !$this->chromecast->sessionid) {
			$this->chromecast->getCastMessage();
		}
		$this->mediaid=$this->chromecast->sessionid;
	}
	
	public function pause() {
		// Pause
		$this->launch(); // Auto-reconnects
		$this->getMediaSession();
		if ($this->mediaid) {
			$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"PAUSE", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->chromecast->requestId.'}');
		}
		$this->chromecast->getCastMessage();
	}

	public function restart() {
		// Restart (after pause)
		$this->launch(); // Auto-reconnects
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"PLAY", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->chromecast->requestId.'}');
		$this->chromecast->getCastMessage();
	}
	
	public function seek($secs) {
		// Seek
		$this->launch(); // Auto-reconnects
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"SEEK", "mediaSessionId":' . $this->mediaid . ', "currentTime":' . $secs . ',"requestId":'.$this->chromecast->requestId.'}');
		$this->chromecast->getCastMessage();
	}
	
	public function stop() {
		// Stop
		$this->launch(); // Auto-reconnects
		$this->getMediaSession();
		if ($this->mediaid) {
			$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"STOP", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->chromecast->requestId.'}');//
			$this->chromecast->getCastMessage();
		}
	}
	
	public function getStatus() {
		// Stop
		$this->launch(); // Auto-reconnects
		$this->getMediaSession();
                if (!$this->mediaid){
                    // nothing starting
                    // надо подумать что отправлять
		    return false;
		}
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.media",'{"type":"GET_STATUS", "mediaSessionId":' . $this->mediaid . ', "requestId":'.$this->chromecast->requestId.'}');
		while (!preg_match("/\"type\":\"MEDIA_STATUS\"/",$r)) {
			$r = $this->chromecast->getCastMessage();
		}
        $r = substr($r, strpos($r,'{"type'),50000);
        return json_decode($r,TRUE);
	}
	
	public function Mute() {
		// Mute a video
		$this->launch(); // Auto-reconnects
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.receiver", '{"type":"SET_VOLUME", "volume": { "muted": true }, "requestId":'.$this->chromecast->requestId.' }');
		$this->chromecast->getCastMessage();
	}
	
	public function UnMute() {
		// Mute a video
		$this->launch(); // Auto-reconnects
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.receiver", '{"type":"SET_VOLUME", "volume": { "muted": false }, "requestId":1 }');
		$this->chromecast->getCastMessage();
	}
	
	public function SetVolume($volume) {
		// Mute a video
		$this->launch(); // Auto-reconnects
		$this->chromecast->sendMessage("urn:x-cast:com.google.cast.receiver", '{"type":"SET_VOLUME", "volume": { "level": ' . $volume . ' }, "requestId":'.$this->chromecast->requestId.' }');
		$this->chromecast->getCastMessage();
	}
}

?>
