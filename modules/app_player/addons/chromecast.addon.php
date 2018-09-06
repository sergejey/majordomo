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

	/*
	// Get player status
	function status() {
		$this->reset_properties();
		try {
			$cc = new GChromecast($this->terminal['HOST'], this->terminal['PLAYER_PORT']);
			$cc->requestId = time();
			
			var_dump(
				$cc->DMP->getStatus() // TODO
			);
			
			//$this->success = TRUE;
			//$this->message = 'OK';
			//$this->data = array(
			//	'track_id'		=> -1,
			//	'length'		=> 0,
			//	'time'			=> 0,
			//	'state'			=> 'unknown',
			//	'fullscreen'	=> FALSE,
			//	'volume'		=> 100,
			//	'random'		=> FALSE,
			//	'loop'			=> FALSE,
			//	'repeat'		=> FALSE,
			//);
			
		} catch (Exception $e) {
			$this->success = FALSE;
			$this->message = $e->getMessage();
		}
		return $this->success;
	}
	*/
	
	// Play
	function play($input) {
		$this->reset_properties();
		if(!empty($input)) {
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
				$cc->DMP->play($input, 'LIVE', $content_type, true, 0);
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
		if(!empty($level)) {
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
