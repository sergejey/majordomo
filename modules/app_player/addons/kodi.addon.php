<?php
/*
	Addon Kodi (XBMC) for app_player
*/
class kodi extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'Kodi (XBMC)';
		$this->description = 'Бесплатный кроссплатформенный медиаплеер и программное обеспечение для организации HTPC с открытым исходным кодом.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?8080:$this->terminal['PLAYER_PORT']);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, 5);
		if($this->terminal['PLAYER_USERNAME'] || $this->terminal['PLAYER_PASSWORD']) {
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->curl, CURLOPT_USERPWD, $this->terminal['PLAYER_USERNAME'].':'.$this->terminal['PLAYER_PASSWORD']);
		}
	}

	// Destructor
	function destroy() {
		curl_close($this->curl);
	}
	
	// Private: Kodi request
	private function kodi_request($method, $params=array()) {
		$this->reset_properties();
		$json = array('jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => (int)$this->terminal['ID']);
		$request = json_encode($json);
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/jsonrpc?request='.urlencode($request));
		if($result = curl_exec($this->curl)) {
			$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
			switch($code) {
				case 200:
					if($json = json_decode($result)) {
						if($json->error) {
							$this->success = FALSE;
							$this->message = $json->error->message;
						} else {
							$this->success = TRUE;
							$this->message = 'OK';
							$this->data = $json;
						}
					} else {
						$this->success = FALSE;
						$this->message = 'JSON decoding error!';
					}
					break;
				case 401:
					$this->success = FALSE;
					$this->message = 'Authorization failed!';
					break;
				default:
					$this->success = FALSE;
					$this->message = 'Unknown error (code '.$code.')!';
			}
		} else {
			$this->success = FALSE;
			$this->message = curl_error($this->curl);
		}
		return $this->success;
	}
	
	// Private: Kodi get player id
	private function kodi_player_id($type='audio') {
		if($this->kodi_request('Player.GetActivePlayers')) {
			if($this->data->result) {
				$player_id = -1;
				foreach($this->data->result as $player) {
					if($player->type == $type) {
						$player_id = $player->playerid;
						break;
					}
				}
				$this->reset_properties();
				$this->success = TRUE;
				if($player_id >= 0) {
					$this->message = 'OK';
				} else {
					$this->message = 'No players with "'.$type.'" type!';
				}
				$this->data = $player_id;
			} else {
				$this->success = TRUE;
				$this->message = 'No active players!';
				$this->data = -1;
			}
		}
		return $this->success;
	}
	
	// Private: Kodi get playlist id
	private function kodi_playlist_id($type='audio') {
		if($this->kodi_request('Playlist.GetPlaylists')) {
			if($this->data->result) {
				$playlist_id = -1;
				foreach($this->data->result as $playlist) {
					if($playlist->type == $type) {
						$playlist_id = $playlist->playlistid;
						break;
					}
				}
				$this->reset_properties();
				$this->success = TRUE;
				if($playlist_id >= 0) {
					$this->message = 'OK';
				} else {
					$this->message = 'No playlists with "'.$type.'" type!';
				}
				$this->data = $playlist_id;
			} else {
				$this->success = TRUE;
				$this->message = 'No playlists!';
				$this->data = -1;
			}
		}
		return $this->success;
	}
	
	// Private: Kodi get track position
	private function kodi_track_position($track_id) {
		if($this->pl_get()) {
			$track_position = -1;
			foreach($this->data as $key=>$value) {
				if($value['id'] == $track_id) {
					$track_position = $key;
					break;
				}
			}
			$this->reset_properties();
			if($track_position >= 0) {
				$this->success = TRUE;
				$this->message = 'OK';
				$this->data = $track_position;
			} else {
				$this->success = FALSE;
				$this->message = 'No track with ID "'.$track_id.'"!';
			}
		}
		return $this->success;
	}

	// Get player status
	function status() {
		// Kodi: Player ID
		if($this->kodi_player_id()) {
			// Defaults
			$track_id	= -1;
			$length		= 0;
			$time		= 0;
			$state		= 'unknown';
			$volume		= 0;
			$random		= FALSE;
			$loop		= FALSE;
			$repeat		= FALSE;
			// Player ID
			$player_id = $this->data;
			if($player_id == -1) {
				$state = 'stopped';
			}
			// Track ID, Length
			if($this->kodi_request('Player.GetItem', array('playerid'=>$player_id, 'properties'=>array('duration')))) {
				if(!is_null($this->data->result->item->id)) {
					$track_id = $this->data->result->item->id;
				}
				$length = $this->data->result->item->duration;
			}
			// Volume
			if($this->kodi_request('Application.GetProperties', array('properties'=>array('volume')))) {
				$volume = $this->data->result->volume;
			}
			// State: playing/paused, Time, Random, Loop, Repeat
			if($this->kodi_request('Player.GetProperties', array('playerid'=>$player_id, 'properties'=>array('speed', 'time', 'shuffled', 'repeat')))) {
				$state = ($this->data->result->speed?'playing':'paused');
				$time = ($this->data->result->time->hours*60*60) + ($this->data->result->time->minutes*60) + ($this->data->result->time->seconds) + round($this->data->result->time->milliseconds/1000);
				$random = $this->data->result->shuffled;
				$loop = ($this->data->result->repeat == 'all'?TRUE:FALSE);
				$repeat = ($this->data->result->repeat == 'one'?TRUE:FALSE);
			}
			// Results
			$this->reset_properties();
			$this->success = TRUE;
			$this->message = 'OK';
			$this->data = array(
				'track_id'		=> (int)$track_id,
				'length'		=> (int)$length,
				'time'			=> (int)$time,
				'state'			=> (string)$state,
				'volume'		=> (int)$volume,
				'random'		=> (boolean)$random,
				'loop'			=> (boolean)$loop,
				'repeat'		=> (boolean)$repeat,
			);
		}
		return $this->success;
	}
	
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if($this->kodi_request('Player.Open', array('item'=>array('file'=>$input)))) {
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
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.PlayPause', array('playerid'=>$player_id))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}

	// Stop
	function stop() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.Stop', array('playerid'=>$player_id))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Next
	function next() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.GoTo', array('playerid'=>$player_id, 'to'=>'next'))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.GoTo', array('playerid'=>$player_id, 'to'=>'previous'))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Seek
	function seek($position) {
		$this->reset_properties();
		if(strlen($position)) {
			if($this->kodi_player_id()) {
				$player_id = $this->data;
				if($player_id != -1) {
					$hours = round((int)$position/60/60);
					$minutes = round(((int)$position-$hours*60*60)/60);
					$seconds = round((int)$position-$hours*60*60-$minutes*60);
					if($this->kodi_request('Player.Seek', array('playerid'=>$player_id, 'value'=>array('hours'=>$hours,'minutes'=>$minutes,'seconds'=>$seconds)))) {
						$this->success = TRUE;
						$this->message = 'OK';
					}
				} else {
					$this->success = FALSE;
					$this->data = NULL;
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Position is missing!';
		}
		return $this->success;
	}

	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(strlen($level)) {
			if($this->kodi_request('Application.SetVolume', array('volume'=>(int)$level))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Get
	function pl_get() {
		if($this->kodi_playlist_id()) {
			$playlist_id = $this->data;
			if($playlist_id != -1) {
				if($this->kodi_request('Playlist.GetItems', array('playlistid'=>$playlist_id, 'properties'=>array('file')))) {
					$items = $this->data->result->items;
					$this->reset_properties();
					$this->success = TRUE;
					$this->message = 'OK';
					foreach($items as $item) {
						$this->data[] = array(
							'id'	=> (int)$item->id,
							'name'	=> (string)$item->label,
							'file'	=> (string)$item->file,
						);
					}
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}

	// Playlist: Add
	function pl_add($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if($this->kodi_playlist_id()) {
				$playlist_id = $this->data;
				if($playlist_id != -1) {
					if($this->kodi_request('Playlist.Add', array('playlistid'=>$playlist_id, 'item'=>array('file'=>$input)))) {
						$this->success = TRUE;
						$this->message = 'OK';
					}
				} else {
					$this->success = FALSE;
					$this->data = NULL;
				}
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
			if($this->kodi_track_position($id)) {
				$track_position = $this->data;
				if($this->kodi_playlist_id()) {
					$playlist_id = $this->data;
					if($playlist_id != -1) {
						if($this->kodi_request('Playlist.Remove', array('playlistid'=>$playlist_id, 'position'=>$track_position))) {
							$this->success = TRUE;
							$this->message = 'OK';
						}
					} else {
						$this->success = FALSE;
						$this->data = NULL;
					}
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Empty
	function pl_empty() {
		if($this->kodi_playlist_id()) {
			$playlist_id = $this->data;
			if($playlist_id != -1) {
				if($this->kodi_request('Playlist.Clear', array('playlistid'=>$playlist_id))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Playlist: Play
	function pl_play($id) {
		$this->reset_properties();
		if(strlen($id)) {
			if($this->kodi_track_position($id)) {
				$track_position = $this->data;
				if($this->kodi_player_id()) {
					$player_id = $this->data;
					if($player_id != -1) {
						if($this->kodi_request('Player.GoTo', array('playerid'=>$player_id, 'to'=>$track_position))) {
							$this->success = TRUE;
							$this->message = 'OK';
						}
					} else {
						$this->success = FALSE;
						$this->data = NULL;
					}
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}

	// Playlist: Random
	function pl_random() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.SetShuffle', array('playerid'=>$player_id, 'shuffle'=>'toggle'))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Playlist: Loop
	function pl_loop() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.GetProperties', array('playerid'=>$player_id, 'properties'=>array('repeat')))) {
					if($this->data->result->repeat != 'all') {
						$repeat = 'all';
					} else {
						$repeat = 'off';
					}
					if($this->kodi_request('Player.SetRepeat', array('playerid'=>$player_id, 'repeat'=>$repeat))) {
						$this->success = TRUE;
						$this->message = 'OK';
					}
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Playlist: Repeat
	function pl_repeat() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
			if($player_id != -1) {
				if($this->kodi_request('Player.GetProperties', array('playerid'=>$player_id, 'properties'=>array('repeat')))) {
					if($this->data->result->repeat != 'one') {
						$repeat = 'one';
					} else {
						$repeat = 'off';
					}
					if($this->kodi_request('Player.SetRepeat', array('playerid'=>$player_id, 'repeat'=>$repeat))) {
						$this->success = TRUE;
						$this->message = 'OK';
					}
				}
			} else {
				$this->success = FALSE;
				$this->data = NULL;
			}
		}
		return $this->success;
	}
	
	// Default command
	function command($command, $parameter) {
		if(!$json = json_decode($parameter)) {
			$json = array();
		}
		if($this->kodi_request($command, $json)) {
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
}
