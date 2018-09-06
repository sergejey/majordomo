<?php

/*
	Addon VLC GUI for app_player
*/

class vlc extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'VLC (VideoLAN)';
		$this->description = 'Управление VLC через GUI интерфейс. ';
		$this->description .= 'В настоящее время доступно только для Windows. ';
		$this->description .= 'Поддерживает ограниченный набор команд. ';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?80:$this->terminal['PLAYER_PORT']);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		if($this->terminal['PLAYER_USERNAME'] || $this->terminal['PLAYER_PASSWORD']) {
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
			curl_setopt($this->curl, CURLOPT_USERPWD, $this->terminal['PLAYER_USERNAME'].':'.$this->terminal['PLAYER_PASSWORD']);
		}
	}
	
	// Destructor
	function destroy() {
		curl_close($this->curl);
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(!empty($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			$input = preg_replace('/\/$/is', '', $input);
			if(!preg_match('/^http/', $input)) {
				$input = str_replace('/', "\\", $input);
			}
			$this->stop();
			$vlc_volume = round((int)getGlobal('ThisComputer.volumeMediaLevel') / 100, 2);
			// "--volume" not working (see https://trac.videolan.org/vlc/ticket/3913)
			// The following parameters require last version of VLC (3.0.4):
			$volume_params = '--no-volume-save --mmdevice-volume '.$vlc_volume.' --directx-volume '.$vlc_volume.' --waveout-volume '.$vlc_volume;
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_play&param='.urlencode($volume_params." '".$input."'"));
			if($result = curl_exec($this->curl)) {
				if($result == 'OK') {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = $result;
				}
			} else {
				$this->success = FALSE;
				$this->message = 'RC interface not available!';
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
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_pause');
		if($result = curl_exec($this->curl)) {
			if($result == 'OK') {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = $result;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'RC interface not available!';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_close');
		if($result = curl_exec($this->curl)) {
			if($result == 'OK') {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = $result;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'RC interface not available!';
		}
		return $this->success;
	}
	
	// Next
	function next() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_next');
		if($result = curl_exec($this->curl)) {
			if($result == 'OK') {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = $result;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'RC interface not available!';
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_prev');
		if($result = curl_exec($this->curl)) {
			if($result == 'OK') {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = $result;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'RC interface not available!';
		}
		return $this->success;
	}

	// Set fullscreen
	function fullscreen() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_fullscreen');
		if($result = curl_exec($this->curl)) {
			if($result == 'OK') {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = $result;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'RC interface not available!';
		}
		return $this->success;
	}
	
	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(!empty($level)) {
			$old_level = getGlobal('ThisComputer.volumeMediaLevelOld');
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command=vlc_volume&param='.urlencode((int)$old_level.':'.(int)$level));
			if($result = curl_exec($this->curl)) {
				if($result == 'OK') {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = $result;
				}
			} else {
				$this->success = FALSE;
				$this->message = 'RC interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}

	// Default command
	function command($command, $parameter) {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command='.urlencode($command).(empty($parameter)?'':'&param='.urlencode($parameter)));
		if($result = curl_exec($this->curl)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'RC interface not available!';
		}
		return $this->success;
	}
	
}

?>
