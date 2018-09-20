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
	private function kodi_player_id($n=0) {
		if($this->kodi_request('Player.GetActivePlayers')) {
			if($this->data->result) {
				$player_id = $this->data->result[$n]->playerid;
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
				$this->data = $player_id;
			} else {
				$this->success = FALSE;
				$this->message = 'No active players!';
			}
		}
		return $this->success;
	}

	/*
	// Get player status
	function status() {
	}
	*/
	
	/*
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	*/
	
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
	
	/*
	// Next
	function next() {
	}
	*/
	
	/*
	// Previous
	function previous() {
	}
	*/
	
	// Seek
	function seek($position) {
		$this->reset_properties();
		if(strlen($position)) {
			if($this->kodi_player_id()) {
				$hours = round($position/60/60);
				$minutes = round(($position-$hours*60*60)/60);
				$seconds = round($position-$hours*60*60-$minutes*60);
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
	
	/*
	// Fullscreen
	function fullscreen() {
	}
	*/
	
	/*
	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(strlen($level)) {
			
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	*/
	
	/*
	// Playlist: Get
	function pl_get() {
	}
	*/

	/*
	// Playlist: Add
	function pl_add($input) {
		$this->reset_properties();
		if(strlen($input)) {
			
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	*/
	
	/*
	// Playlist: Delete
	function pl_delete($id) {
		$this->reset_properties();
		if(strlen($id)) {
			
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}
	*/
	
	/*
	// Playlist: Empty
	function pl_empty() {
	}
	*/
	
	/*
	// Playlist: Play
	function pl_play($id) {
		$this->reset_properties();
		if(strlen($id)) {
		
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}
	*/
	
	/*
	// Playlist: Sort
	function pl_sort($order) {
		$this->reset_properties();
		if(strlen($order)) {
			
		} else {
			$this->success = FALSE;
			$this->message = 'Order is missing!';
		}
		return $this->success;
	}
	*/
	
	/*
	// Playlist: Random
	function pl_random() {
	}
	*/
	
	/*
	// Playlist: Loop
	function pl_loop() {
	}
	*/
	
	/*
	// Playlist: Repeat
	function pl_repeat() {
	}
	*/
	
	// Default command
	function command($command, $parameter) {
		if($this->kodi_request($command, array($parameter))) { // FIXME: parameter
			$this->success = TRUE;
			$this->message = 'OK';
		}
		return $this->success;
	}
	
}
