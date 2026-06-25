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

	// Get product version (for exe files)
	private function get_product_version($file) {
		if($data = file_get_contents($file)) {
			$key = "V\x00S\x00_\x00V\x00E\x00R\x00S\x00I\x00O\x00N\x00_\x00I\x00N\x00F\x00O\x00\x00\x00";
			$key_pos = strpos($data, $key);
			if($key_pos === FALSE) {
				return '';
			}
			$data = substr($data, $key_pos);
			$key = "P\x00r\x00o\x00d\x00u\x00c\x00t\x00V\x00e\x00r\x00s\x00i\x00o\x00n\x00\x00\x00";
			$key_pos = strpos($data, $key);
			if($key_pos === FALSE) {
				return '';
			}
			$version = '';
			$key_pos = $key_pos + strlen($key);
			for($i=$key_pos; $data[$i]!="\x00"; $i+=2) {
				$version .= $data[$i];
			}
			return trim($version);
		} else {
			return NULL;
		}
	}
	
	// Compare programs versions
	private function compare_programs_versions($first, $second) {
		$fvc = substr_count($first, '.');
		$svc = substr_count($second, '.');
		if($fvc > $svc) {
			$dvc = $fvc;
		} else {
			$dvc = $svc;
		}
		$fvf= explode('.', $first);
		$svf = explode('.', $second);
		for($i=0;$i<=$dvc;$i++) {
			if(intval($svf[$i]) > intval($fvf[$i])) {
				return TRUE;
			} elseif(intval($svf[$i]) < intval($fvf[$i])) {
				return FALSE;
			}
		}
		return FALSE;
	}
	
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			$input = preg_replace('/\/$/is', '', $input);
			if(!preg_match('/^http/', $input)) {
				$input = str_replace('/', "\\", $input);
			}
			$this->stop();
			$vlc_volume = round((int)getGlobal('ThisComputer.volumeMediaLevel') / 100, 2);
			$volume_params = '';
			if($vlc_version = $this->get_product_version(SERVER_ROOT.'/apps/vlc/vlc.exe')) {
				$vlc_version = str_replace(',', '.', $vlc_version);
				if(!$this->compare_programs_versions($vlc_version, '3.0.4.0')) {
					// "--volume" not working (see https://trac.videolan.org/vlc/ticket/3913)
					// The following parameters require last version of VLC (3.0.4):
					$volume_params = '--no-volume-save --mmdevice-volume '.$vlc_volume.' --directx-volume '.$vlc_volume.' --waveout-volume '.$vlc_volume;
				}
			}
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

	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(strlen($level)) {
			$old_level = getGlobal('ThisComputer.volumeMediaLevel');
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
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/rc/?command='.urlencode($command).(strlen($parameter)?'&param='.urlencode($parameter):''));
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
