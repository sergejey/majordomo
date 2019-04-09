<?php

/*
	Addon Chromecast for app_player
*/

class chromecast extends app_player_addon {

	// Constructor
	function __construct($terminal) {
		$this->title = 'Google Chromecast';
		$this->description = 'Цифровой медиаплеер от компании Google.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		// Network
		$this->terminal['PLAYER_PORT'] = (empty($this->terminal['PLAYER_PORT'])?8009:$this->terminal['PLAYER_PORT']);
		
		// Chromecast
		include_once(DIR_MODULES.'app_player/libs/castv2/Chromecast.php');
	}
	// Get player status
	function status() {
		$this->reset_properties();
		$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
		$cc->requestId = time();
		DebMes($cc->DMP->getStatus());
		// дает ответ надо его распарсить

		// ответ от гуглохома "{\"type\":\"MEDIA_STATUS\",\"status\":[{\"mediaSessionId\":11,\"playbackRate\":1,\"playerState\":\"PLAYING\",\"currentTime\":1321.325804,\"supportedMediaCommands\":274447,\"volume\":{\"level\":1,\"muted\":false},\"activeTrackIds\":[],\"media\":{\"contentId\":\"http:\/\/ic7.101.ru:8000\/a175?userid=0&setst=spo4a4n0ggd4bu5v1qt989dvgl&tok=10750927MnRZbklCa2ZJbSs2MEQxT2UwT25wcVdFNStzMDZ0cGcxNnc3RUd1TjhaT09pQ254cktXby9odE53Z3B0bTFCUA%3D%3D3\",\"streamType\":\"BUFFERED\",\"contentType\":\"audio\/aacp\",\"duration\":null,\"tracks\":[{\"trackId\":1,\"type\":\"AUDIO\"}],\"breakClips\":[],\"breaks\":[]},\"currentItemId\":11,\"items\":[{\"itemId\":11,\"media\":{\"contentId\":\"http:\/\/ic7.101.ru:8000\/a175?userid=0&setst=spo4a4n0ggd4bu5v1qt989dvgl&tok=10750927MnRZbklCa2ZJbSs2MEQxT2UwT25wcVdFNStzMDZ0cGcxNnc3RUd1TjhaT09pQ254cktXby9odE53Z3B0bTFCUA%3D%3D3\",\"streamType\":\"BUFFERED\",\"contentType\":\"audio\/aacp\",\"duration\":null},\"autoplay\":1,\"orderId\":0}],\"repeatMode\":\"REPEAT_OFF\"}],\"requestId\":1554844363}"

		return $this->success;
	}

	
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			try {
				$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
				$cc->requestId = time();
				if(preg_match('/\.mp3/', $input)) {
					$content_type = 'audio/mp3';
				} elseif(preg_match('/mp4/', $input)) {
					$content_type = 'video/mp4';
				} elseif(preg_match('/m4a/', $input)) {
					$content_type = 'audio/mp4';
				} elseif(preg_match('/^http/', $input)) {
					$content_type = '';
					if($fp = fopen($input, 'r')) {
						$meta = stream_get_meta_data($fp);
						if(is_array($meta['wrapper_data'])) {
							$items = $meta['wrapper_data'];
							foreach($items as $line) {
								if(preg_match('/Content-Type:(.+)/is', $line,$m)) {
									$content_type = trim($m[1]);
								}
							}
						}
						fclose($fp);
					}
				}
				if(!$content_type) {
					$content_type = 'audio/mpeg';
				}
				$cc->DMP->play($input, 'BUFFERED', $content_type, true, 0);
				$this->success = TRUE;
				$this->message = 'OK';
			} catch (Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	
	// Pause
	function pause() {
		$this->reset_properties();
		try {
			$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
			$cc->requestId = time();
			$cc->DMP->pause();
			$this->success = TRUE;
			$this->message = 'OK';
		} catch (Exception $e) {
			$this->success = FALSE;
			$this->message = $e->getMessage();
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		try {
			$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
			$cc->requestId = time();
			$cc->DMP->stop();
			$this->success = TRUE;
			$this->message = 'OK';
		} catch (Exception $e) {
			$this->success = FALSE;
			$this->message = $e->getMessage();
		}
		return $this->success;
	}
	
	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(strlen($level)) {
			try {
				$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
				$cc->requestId = time();
				$level = round($level/100, 1);
				$cc->DMP->SetVolume($level);
				$this->success = TRUE;
				$this->message = 'OK';
			} catch (Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
}

?>
