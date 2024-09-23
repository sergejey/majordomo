<?php

/*
	Addon MPD for app_player
*/

class mpd extends app_player_addon {

	// Private properties
	private $mpd;

	// Constructor
	function __construct($terminal) {
		$this->title = 'Music Player Daemon (MPD)';
		$this->description = 'Кроссплатформенный музыкальный проигрыватель, имеющий клиент-серверную архитектуру.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Network
		$this->terminal['PLAYER_PORT'] = (empty($this->terminal['PLAYER_PORT'])?6600:$this->terminal['PLAYER_PORT']);
		$this->terminal['PLAYER_PASSWORD'] = (empty($this->terminal['PLAYER_PASSWORD'])?NULL:$this->terminal['PLAYER_PASSWORD']);
		
		// MPD
		include_once(DIR_MODULES.'app_player/libs/mpd/mpd.class.php');
	}

	// Private: MPD connect
	private function mpd_connect() {
		$this->reset_properties();
		$this->mpd = new mpd_player($this->terminal['HOST'], $this->terminal['PLAYER_PORT'], $this->terminal['PLAYER_PASSWORD']);
		if($this->mpd->connected) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			if(is_null($this->mpd->errStr)) {
				$this->message = 'Error connecting to MPD server!';
			} else {
				$this->message = $this->mpd->errStr;
			}
		}
		return $this->success;
	}
	
	// Get player status
	function status() {
		if($this->mpd_connect()) {
			$this->mpd->RefreshInfo();
			$this->reset_properties();
			if(!is_null($status = $this->mpd->GetStatus())) {
				$this->success = TRUE;
				$this->message = 'OK';
				switch($status['state']) {
					case 'stop': $status['state'] = 'stopped'; break;
					case 'play': $status['state'] = 'playing'; break;
					case 'pause': $status['state'] = 'paused'; break;
					default: $status['state'] = 'unknown';
				}
				$this->data = array(
					'track_id'		=> (int)$this->mpd->current_track_id,
					'length'		=> (int)$this->mpd->current_track_length,
					'time'			=> (int)$this->mpd->current_track_position,
					'state'			=> (string)$status['state'],
					'volume'		=> (int)$status['volume'],
					'random'		=> ($status['random'] == '1'?TRUE:FALSE),
					'loop'			=> ((($status['single'] == '0') && ($status['repeat'] == '1'))?TRUE:FALSE),
					'repeat'		=> ((($status['single'] == '1') && ($status['repeat'] == '1'))?TRUE:FALSE),
				);
			} else {
				$this->success = FALSE;
				if(is_null($this->mpd->errStr)) {
					$this->message = 'Error getting player status!';
				} else {
					$this->message = $this->mpd->errStr;
				}
			}
			$this->mpd->Disconnect();
		}
		if ($this->success) {
			return $this->data;
		} else {
			return $this->success;
		}

	}
	
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if($this->mpd_connect()) {
				$this->mpd->PLClear();
				//$this->mpd->DBRefresh();
				$this->mpd->PLAdd($input);
				$this->mpd->Play();
				$this->mpd->Disconnect();
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}

	// Pause
	function pause() {
		if($this->mpd_connect()) {
			$this->mpd->Pause();
			$this->mpd->Disconnect();
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		if($this->mpd_connect()) {
			$this->mpd->Stop();
			$this->mpd->Disconnect();
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
	// Next
	function next() {
		if($this->mpd_connect()) {
			$this->mpd->Next();
			$this->mpd->Disconnect();
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		if($this->mpd_connect()) {
			$this->mpd->Previous();
			$this->mpd->Disconnect();
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
	// Seek
	function seek($position) {
		$this->reset_properties();
		if(strlen($position)) {
			if($this->mpd_connect()) {
				$this->mpd->SeekTo((int)$position);
				$this->mpd->Disconnect();
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Position is missing!';
		}
		return $this->success;
	}

	// Set volume
	function set_volume($level) {
		if(strlen($level)) {
			if($this->mpd_connect()) {
				$this->mpd->SetVolume((int)$level);
				$this->mpd->Disconnect();
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
	// Get volume
	public function get_volume() {
		if($this->mpd_connect()) {
			$this->reset_properties();
			if(!is_null($volume = $this->mpd->GetVolume())) {
				$this->success = TRUE;
				$this->message = 'OK';
				if($volume == -1) {
					if(strtolower($this->terminal['HOST']) == 'localhost' || $this->terminal['HOST'] == '127.0.0.1') {
						$this->data = (int)getGlobal('ThisComputer.volumeMediaLevel');
					} else {
						$this->success = FALSE;
						$this->message = 'The volume level is unknown!';
					}
				} else {
					$this->data = (int)$volume;
				}
			} else {
				$this->success = FALSE;
				if(is_null($this->mpd->errStr)) {
					$this->message = 'Error getting volume level!';
				} else {
					$this->message = $this->mpd->errStr;
				}
			}
			$this->mpd->Disconnect();
		}
		return $this->success;
	}
	
	// Playlist: Get
	function pl_get() {
		if($this->mpd_connect()) {
			$this->reset_properties();
			if(!is_null($playlist = $this->mpd->GetPlaylist())) {
				$this->success = TRUE;
				$this->message = 'OK';
				foreach($playlist['files'] as $file) {
					$this->data[] = array(
						'id'	=> (int)$file['Id'],
						'name'	=> (string)$file['Title'],
						'file'	=> (string)$file['file'],
					);
				}
			} else {
				$this->success = FALSE;
				if(is_null($this->mpd->errStr)) {
					$this->message = 'Error getting playlist!';
				} else {
					$this->message = $this->mpd->errStr;
				}
			}
			$this->mpd->Disconnect();
		}
		return $this->success;
	}

	// Playlist: Add
	function pl_add($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if($this->mpd_connect()) {
				$this->mpd->PLAdd($input);
				$this->mpd->Disconnect();
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Delete
	function pl_delete($id) {
		$this->reset_properties();
		if(strlen($id)) {
			if($this->mpd_connect()) {
				$this->mpd->PLRemoveId($id);
				$this->mpd->Disconnect();
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Empty
	function pl_empty() {
		if($this->mpd_connect()) {
			$this->mpd->PLClear();
			//$this->mpd->DBRefresh();
			$this->mpd->Disconnect();
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
	// Playlist: Play
	function pl_play($id) {
		$this->reset_properties();
		if(strlen($id)) {
			if($this->mpd_connect()) {
				$this->mpd->PlayId((int)$id);
				$this->mpd->Disconnect();
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}

	// Playlist: Random on/off
	function pl_random() {
		if($this->mpd_connect()) {
			$this->reset_properties();
			if(!is_null($status = $this->mpd->GetStatus())) {
				$this->mpd->SetRandom((int)!$status['random']);
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				if(is_null($this->mpd->errStr)) {
					$this->message = 'Error getting player status!';
				} else {
					$this->message = $this->mpd->errStr;
				}
			}
			$this->mpd->Disconnect();
		}
		return $this->success;
	}

	// Playlist: Loop on/off
	function pl_loop() {
		if($this->mpd_connect()) {
			$this->reset_properties();
			if(!is_null($status = $this->mpd->GetStatus())) {
				$loop = (($status['single'] == '0') && ($status['repeat'] == '1'));
				$this->mpd->SetSingle(0);
				$this->mpd->SetRepeat((int)!$loop);
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				if(is_null($this->mpd->errStr)) {
					$this->message = 'Error getting player status!';
				} else {
					$this->message = $this->mpd->errStr;
				}
			}
			$this->mpd->Disconnect();
		}
		return $this->success;
	}

	// Playlist: Repeat on/off
	function pl_repeat() {
		if($this->mpd_connect()) {
			$this->reset_properties();
			if(!is_null($status = $this->mpd->GetStatus())) {
				$repeat = (($status['single'] == '1') && ($status['repeat'] == '1'));
				$this->mpd->SetSingle((int)!$repeat);
				$this->mpd->SetRepeat((int)!$repeat);
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				if(is_null($this->mpd->errStr)) {
					$this->message = 'Error getting player status!';
				} else {
					$this->message = $this->mpd->errStr;
				}
			}
			$this->mpd->Disconnect();
		}
		return $this->success;
	}

	// Default command
	function command($command, $parameter) {
		if($this->mpd_connect()) {
			$result = $this->mpd->SendCommand($command, $parameter);
			$this->mpd->Disconnect();
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
			if(!is_null($result)) {
				$this->data = $result;
			}
		}
		return $this->success;
	}

}

?>
