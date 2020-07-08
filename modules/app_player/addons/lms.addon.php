<?php

/*
	Addon Logitech Media Server for app_player
	http://tutoriels.domotique-store.fr/content/54/95/fr/api-logitech-squeezebox-server-_-player-http.html
	http://localhost:9000/html/docs/cli-api.html
*/

class lms extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'Logitech Media Server';
		$this->description = 'Logitech Media Server - это потоковый аудиосервер,разработанный, в частности, для поддержки цифровых аудиоприемников Squeezebox.<br>';
		$this->description .= 'В поле <i>Имя пользователя</i> необходимо указать IP или MAC адрес плеера.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?9000:$this->terminal['PLAYER_PORT']);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
	}
	
	// Destructor
	function destroy() {
		curl_close($this->curl);
	}
	
	// Private: LMS JSON-RPC request
	private function lms_jsonrpc_request($data) {
		$jsonrpc = array(
			'id'		=> 1,
			'method'	=> 'slim.request',
			'params'	=> array(
				$this->terminal['PLAYER_USERNAME'],
				$data,
			)
		);
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_POST, TRUE);
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/jsonrpc.js');
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($jsonrpc));
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		if($result = curl_exec($this->curl)) {
			if($json = json_decode($result)) {
				$this->success = TRUE;
				$this->message = 'OK';
				$this->data = ($json->result?$json->result:NULL);
			} else {
				$this->success = FALSE;
				$this->message = 'JSON decode: '.json_last_error_msg().'!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'LMS JSON-RPC interface: '.curl_error($this->curl).'!';
		}
		return $this->success;
	}
	
	// Private: LMS get track position
	private function lms_get_track_position($id) {
		if($this->pl_get()) {
			$playlist = $this->data;
			$this->reset_properties(array('success'=>FALSE, 'message'=>"Track with ID = $id not found!"));
			foreach($playlist as $i=>$track) {
				if($track['id'] == $id) {
					$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
					$this->data = $i;
					break;
				}
			}
		}
		return $this->success;
	}
	
	// Private: LMS get track id
	private function lms_get_track_id($position=-1) {
		if($this->pl_get()) {
			$playlist = $this->data;
			$this->reset_properties(array('success'=>FALSE, 'message'=>"Track with position = $position not found!"));
			if($position == -1) {
				$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
				$this->data = $playlist[count($playlist)-1]['id'];
			} else {
				foreach($playlist as $i=>$track) {
					if($i == $position) {
						$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
						$this->data = $track['id'];
						break;
					}
				}
			}
		}
		return $this->success;
	}

	// Get player status
	function status() {
		if($this->lms_jsonrpc_request(array('status', '-', 1, 'tags:i'))) {
			$json = $this->data;
			// State
			switch($json->mode) {
				case 'play':	$state = 'playing'; break;
				case 'pause':	$state = 'paused'; break;
				case 'stop':	$state = 'stopped'; break;
				default:		$state = 'unknown';
			}
			// Results
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = array(
				'track_id'		=> (int)(($state == 'stopped' || $state == 'unknown')?-1:(($json->playlist_loop)?reset($json->playlist_loop)->id:-1)),
				'length'		=> (int)(($state == 'stopped' || $state == 'unknown')?0:round($json->duration)),
				'time'			=> (int)(($state == 'stopped' || $state == 'unknown')?0:round($json->time)),
				'state'			=> (string)$state,
				'volume'		=> (int)$json->{'mixer volume'},
				'random'		=> (boolean)(($json->{'playlist shuffle'} == 0)?FALSE:TRUE),
				'loop'			=> (boolean)(($json->{'playlist repeat'} == 2)?TRUE:FALSE),
				'repeat'		=> (boolean)(($json->{'playlist repeat'} == 1)?TRUE:FALSE),
			);
		}
		return $this->success;
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			if($this->lms_jsonrpc_request(array('playlist', 'play', $input))) {
				if($this->status()) {
					$track_id = $this->data['track_id'];
				} else {
					$track_id = -1;
				}
				$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
				$this->data = (int)$track_id;
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	
	// Pause
	function pause() {
		if($this->lms_jsonrpc_request(array('pause'))) {
			if($this->status()) {
				$paused = (($this->data['state'] == 'paused')?TRUE:FALSE);
			} else {
				$paused = FALSE;
			}
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (boolean)$paused;
		}
		return $this->success;
	}

	// Stop
	function stop() {
		if($this->lms_jsonrpc_request(array('stop'))) {
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
		}
		return $this->success;
	}
	
	// Next
	function next() {
		if($this->lms_jsonrpc_request(array('button', 'jump_fwd'))) {
			if($this->status()) {
				$track_id = $this->data['track_id'];
			} else {
				$track_id = -1;
			}
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (int)$track_id;
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		if($this->lms_jsonrpc_request(array('button', 'jump_rew'))) {
			if($this->status()) {
				$track_id = $this->data['track_id'];
			} else {
				$track_id = -1;
			}
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (int)$track_id;
		}
		return $this->success;
	}
	
	// Seek
	function seek($position) {
		$this->reset_properties();
		if(strlen($position)) {
			if($this->lms_jsonrpc_request(array('time', (int)$position))) {
				$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
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
			if($this->lms_jsonrpc_request(array('mixer', 'volume', (int)$level))) {
				$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Get
	function pl_get() {
		if($this->lms_jsonrpc_request(array('status', 0, PHP_INT_MAX, 'tags:u'))) {
			$playlist = $this->data->playlist_loop;
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK', 'data'=>array()));
			if($playlist) {
				foreach($playlist as $track) {
					$this->data[] = array(
						'id'	=> (int)$track->id,
						'name'	=> (string)(empty($track->title)?'Unknown':$track->title),
						'file'	=> (string)trim($track->url, 'file:///'),
					);
				}
			}
		}
		return $this->success;
	}

	// Playlist: Add
	function pl_add($input) {
		$this->reset_properties();
		if(strlen($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			if($this->lms_jsonrpc_request(array('playlist', 'add', $input))) {
				if($this->lms_get_track_id()) {
					$track_id = $this->data;
				} else {
					$track_id = -1;
				}
				$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
				$this->data = (int)$track_id;
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
			if($this->lms_jsonrpc_request(array('playlistcontrol', 'cmd:delete', 'track_id:'.(int)$id))) {
				$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Empty
	function pl_empty() {
		if($this->lms_jsonrpc_request(array('playlist', 'clear'))) {
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
		}
		return $this->success;
	}
	
	// Playlist: Play
	function pl_play($id) {
		$this->reset_properties();
		if(strlen($id)) {
			if($this->lms_get_track_position($id)) {
				$track_position = $this->data;
				if($this->lms_jsonrpc_request(array('playlist', 'jump', (int)$track_position))) {
					$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
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
		if($this->status()) {
			$random = ($this->data['random']?0:1);
		} else {
			$random = 0;
		}
		if($this->lms_jsonrpc_request(array('playlist', 'shuffle', (int)$random))) {
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (boolean)$random;
		}
		return $this->success;
	}
	
	// Playlist: Loop
	function pl_loop() {
		if($this->status()) {
			$loop = ($this->data['loop']?0:2);
		} else {
			$loop = 0;
		}
		if($this->lms_jsonrpc_request(array('playlist', 'repeat', (int)$loop))) {
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (boolean)$loop;
		}
		return $this->success;
	}
	
	// Playlist: Repeat
	function pl_repeat() {
		if($this->status()) {
			$repeat = ($this->data['repeat']?0:1);
		} else {
			$repeat = 0;
		}
		if($this->lms_jsonrpc_request(array('playlist', 'repeat', (int)$repeat))) {
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (boolean)$repeat;
		}
		return $this->success;
	}
	
	// Default command
	function command($command, $parameter) {
		$data = array($command, $parameter);
		return $this->lms_jsonrpc_request($data);
	}
	
}

?>
