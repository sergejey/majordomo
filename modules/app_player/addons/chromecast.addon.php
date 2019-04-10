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
		// Defaults
        $track_id      = -1;
        $length        = 0;
        $time          = 0;
        $state         = 'unknown';
        $volume        = 0;
        $random        = FALSE;
        $loop          = FALSE;
        $repeat        = FALSE;
		
		$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
		$cc->requestId = time();
		$result = $cc->DMP->getStatus();

        if ($result) {
			$this->reset_properties();
            $this->success = TRUE;
            $this->message = 'OK';
            $this->data = array(
            'track_id'        => (int)$result['status'][0]['media']['tracks'][0]['trackId'], //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'length'          => (int)$result['status'][0]['media']['duration'], //Track length in seconds. Integer. If unknown = 0. 
            'time'            => (int)$result['status'][0]['currentTime'], //Current playback progress (in seconds). If unknown = 0. 
            'state'           => (string)strtolower($result['status'][0]['playerState']), //Playback status. String: stopped/playing/paused/unknown 
            'volume'          => (int)$volume, // Volume level in percent. Integer. Some players may have values greater than 100.
            'random'          => (boolean)$random, // Random mode. Boolean. 
            'loop'            => (boolean)$loop, // Loop mode. Boolean.
            'repeat'          => (boolean)$repeat, //Repeat mode. Boolean.
            );
		}
        return $this->success;   
	}

	    // Playlist: Get
    function pl_get() {
        $this->success = FALSE;
        $this->message = 'Command execution error!';    
        $track_id      = -1;
        $name          = 'unknow';
        $curren_url    = '';

		$cc = new GChromecast($this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
		$cc->requestId = time();
		$result = $cc->DMP->getStatus();
		// дает ответ надо его распарсить
		DebMes ($result['status'][0]['media']['duration']);
		
        if ($result) {
            // Results
            $this->reset_properties();
            $this->success = TRUE;
            $this->message = 'OK';
            $this->data = array(
                'id'        => (int)$result['status'][0]['media']['tracks'][0]['trackId'], //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
                'name'      => (string)$name, //Current speed for playing media. float.
                'file'      => (string)$result['status'][0]['media']['contentId'], //Current link for media in device. String.
                );
        }
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
