<?php

/*
	Addon VLC HTTP for app_player
*/

class vlcweb extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'VLC через HTTP';
		$this->description = 'Управление VLC через веб интерфейс. ';
		$this->description .= 'Данный тип плеера имеет наиболее полную совместимость со всеми командами. ';
		$this->description .= 'Не забудьте активировать HTTP интерфейс в настройках VLC ';
		$this->description .= '(Инструменты -> Настройки -> Все -> Основные интерфейсы -> Дополнительные модули интерфейса -> Web) ';
		$this->description .= 'и установить для него пароль (Основные интерфейсы -> Lua -> HTTP -> Пароль).';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?8080:$this->terminal['PLAYER_PORT']);
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
	
	// Private: VLC-WEB request
	private function vlcweb_request($path, $data=array()) {
		$params = array();
		foreach($data as $key=>$value) {
			if(is_string($key)) {
				$params[] = $key.'='.urlencode($value);
			} else {
				$params[] = $value;
			}
		}
		$params = implode('&', $params);
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/'.$path.(strlen($params)?'?'.$params:''));
		if($result = curl_exec($this->curl)) {
			$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
			switch($code) {
				case 200:
					$this->success = TRUE;
					$this->message = 'OK';
					$this->data = $result;
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
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Private: VLC-WEB parse XML
	private function vlcweb_parse_xml($data) {
		$this->reset_properties();
		try {
			if($xml = @ new SimpleXMLElement($data)) {
				$this->success = TRUE;
				$this->message = 'OK';
				$this->data = $xml;
			} else {
				$this->success = FALSE;
				$this->message = 'SimpleXMLElement error!';
			}
		} catch(Exception $e) {
			$this->success = FALSE;
			$this->message = $e->getMessage();
		}
		return $this->success;
	}

	// Get player status
	function status() {
		if($this->vlcweb_request('status.xml')) {
			if($this->vlcweb_parse_xml($this->data)) {
				$xml = $this->data;
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
				$this->data = array(
					'track_id'		=> (int)$xml->currentplid,
					'length'		=> (int)$xml->length,
					'time'			=> (int)$xml->time,
					'state'			=> (string)$xml->state,
					'volume'		=> (round((int)$xml->volume * 100 / 256)),
					'random'		=> ($xml->random == 'true'?TRUE:FALSE),
					'loop'			=> ($xml->loop == 'true'?TRUE:FALSE),
					'repeat'		=> ($xml->repeat == 'true'?TRUE:FALSE),
				);
			}
		}
		return $this->success;
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			if($this->vlcweb_request('status.xml', array('command'=>'in_play', 'input'=>$input))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
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
	
	// Pause
	function pause() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_pause'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}

	// Stop
	function stop() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_stop'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Next
	function next() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_next'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_previous'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
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
			if($this->vlcweb_request('status.xml', array('command'=>'seek', 'val'=>(int)$position))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
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

	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(strlen($level)) {
			$level = round((int)$level * 256 / 100);
			if($this->vlcweb_request('status.xml', array('command'=>'volume', 'val'=>(int)$level))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
					$this->success = TRUE;
					$this->message = 'OK';
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Get
	function pl_get() {
		if($this->vlcweb_request('playlist.xml')) {
			if($this->vlcweb_parse_xml($this->data)) {
				$xml = $this->data;
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
				foreach($xml->node[0] as $item) {
					$this->data[] = array(
						'id'	=> (int)$item['id'],
						'name'	=> (string)$item['name'],
						'file'	=> (string)$item['uri'],
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
			if($this->vlcweb_request('status.xml', array('command'=>'in_enqueue', 'input'=>$input))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
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
	function pl_delete($id) {
		$this->reset_properties();
		if(strlen($id)) {
			if($this->vlcweb_request('status.xml', array('command'=>'pl_delete', 'id'=>(int)$id))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
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
		if($this->vlcweb_request('status.xml', array('command'=>'pl_empty'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Playlist: Play
	function pl_play($id) {
		$this->reset_properties();
		if(strlen($id)) {
			if($this->vlcweb_request('status.xml', array('command'=>'pl_play', 'id'=>(int)$id))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
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
	
	// Playlist: Sort
	function pl_sort($order) {
		$this->reset_properties();
		if(strlen($order)) {
			$order = explode(':', $order);
			switch($order[0]) {
				case 'name': $order[0] = 1; break;
				case 'author': $order[0] = 3; break;
				case 'random': $order[0] = 5; break;
				case 'track': $order[0] = 7; break;
				default: $order[0] = 0; // id
			}
			$order[1] = (isset($order[1]) && $order[1] == 'desc'?1:0);
			if($this->vlcweb_request('status.xml', array('command'=>'pl_sort', 'id'=>(int)$order[1], 'val'=>(int)$order[0]))) {
				if($this->vlcweb_parse_xml($this->data)) {
					$this->reset_properties();
					$this->success = TRUE;
					$this->message = 'OK';
				}
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Order is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Random
	function pl_random() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_random'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Playlist: Loop
	function pl_loop() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_loop'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Playlist: Repeat
	function pl_repeat() {
		if($this->vlcweb_request('status.xml', array('command'=>'pl_repeat'))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$this->reset_properties();
				$this->success = TRUE;
				$this->message = 'OK';
			}
		}
		return $this->success;
	}
	
	// Default command
	function command($command, $parameter) {
		if($this->vlcweb_request('vlm_cmd.xml', array('command'=>$command.(strlen($parameter)?' '.$parameter:'')))) {
			if($this->vlcweb_parse_xml($this->data)) {
				$xml = $this->data;
				$this->reset_properties();
				if(strlen((string)$xml->error)) {
					$this->success = FALSE;
					$this->message = (string)$xml->error;
				} else {
					$this->success = TRUE;
					$this->message = 'OK';
				}
			}
		}
		return $this->success;
	}
	
}

?>
