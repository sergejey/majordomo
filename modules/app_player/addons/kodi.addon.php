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
		$uid = rand(1, 9999999);
		$json = array('jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => $uid);
		$request = json_encode($json);
		$this->reset_properties();
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
			$this->message = 'Kodi HTTP interface not available!';
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
				if($player_id >= 0) {
					$this->success = TRUE;
					$this->message = 'OK';
					$this->data = $player_id;
				} else {
					$this->success = FALSE;
					$this->message = 'No players with "'.$type.'" type!';
				}
			} else {
				$this->success = FALSE;
				$this->message = 'No active players!';
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
				if($playlist_id >= 0) {
					$this->success = TRUE;
					$this->message = 'OK';
					$this->data = $playlist_id;
				} else {
					$this->success = FALSE;
					$this->message = 'No playlists with "'.$type.'" type!';
				}
			} else {
				$this->success = FALSE;
				$this->message = 'No playlists!';
			}
		}
		return $this->success;
	}

	// Get player status
	function status() {
		// FIXME
	}
	
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if($this->pl_empty()) {
				if($this->pl_add($input)) {
					if($this->kodi_player_id()) {
						if($this->kodi_request('Player.GoTo', array('playerid'=>$this->data, 'to'=>0))) {
							$this->success = TRUE;
							$this->message = 'OK';
						}
					}
				}
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
			if($this->kodi_request('Player.PlayPause', array('playerid'=>$this->data))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}

	// Stop
	function stop() {
		if($this->kodi_player_id()) {
			if($this->kodi_request('Player.Stop', array('playerid'=>$this->data))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Next
	function next() {
		if($this->kodi_player_id()) {
			if($this->kodi_request('Player.GoTo', array('playerid'=>$this->data, 'to'=>'next'))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		if($this->kodi_player_id()) {
			if($this->kodi_request('Player.GoTo', array('playerid'=>$this->data, 'to'=>'previous'))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Seek
	function seek($position) {
		$this->reset_properties();
		if(strlen($position)) {
			if($this->kodi_player_id()) {
				$hours = round((int)$position/60/60);
				$minutes = round(((int)$position-$hours*60*60)/60);
				$seconds = round((int)$position-$hours*60*60-$minutes*60);
				if($this->kodi_request('Player.Seek', array('playerid'=>$this->data, 'value'=>array('hours'=>$hours,'minutes'=>$minutes,'seconds'=>$seconds)))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Position is missing!';
		}
		return $this->success;
	}
	
	// Fullscreen
	function fullscreen() {
		if($this->kodi_request('GUI.GetProperties', array('properties'=>array('fullscreen')))) {
			if($this->kodi_request('GUI.SetFullscreen', array('fullscreen'=>!$this->data->result->fullscreen))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
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
			if($this->kodi_request('Playlist.GetItems', array('playlistid'=>$this->data, 'properties'=>array('file')))) {
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
		}
		return $this->success;
	}

	// Playlist: Add
	function pl_add($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if($this->kodi_playlist_id()) {
				if($this->kodi_request('Playlist.Add', array('playlistid'=>$this->data, 'item'=>array('file'=>$input)))) {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Delete
	function pl_delete($id) { // FIXME: id
		$this->reset_properties();
		if(strlen($id)) {
			if($this->kodi_playlist_id()) {
				if($this->kodi_request('Playlist.Remove', array('playlistid'=>$this->data, 'position'=>$id))) {
					$this->success = TRUE;
					$this->message = 'OK';
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
			if($this->kodi_request('Playlist.Clear', array('playlistid'=>$this->data))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Playlist: Play
	function pl_play($id) { // FIXME: id
		$this->reset_properties();
		if(strlen($id)) {
			if($this->kodi_playlist_id()) {
				if($this->kodi_request('Player.GoTo', array('playlistid'=>$this->data, 'position'=>$id))) {
					$this->success = TRUE;
					$this->message = 'OK';
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
			if($this->kodi_request('Player.SetShuffle', array('playerid'=>$this->data, 'shuffle'=>'toggle'))) {
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Playlist: Loop
	function pl_loop() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
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
		}
		return $this->success;
	}
	
	// Playlist: Repeat
	function pl_repeat() {
		if($this->kodi_player_id()) {
			$player_id = $this->data;
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
		}
		return $this->success;
	}
	
	// Default command
	function command($command, $parameter) {
		if($this->kodi_request($command, array($parameter))) { // FIXME: parameter
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
}
