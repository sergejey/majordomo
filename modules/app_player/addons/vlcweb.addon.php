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
		$this->description .= 'и установить для него пароль (Основные интерфейсы -> Lua -> HTTP -> Пароль). ';
		
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

	// Get player status
	function status() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
					$this->data = array(
						'track_id'		=> (int)$xml->currentplid,
						'length'		=> (int)$xml->length,
						'time'			=> (int)$xml->time,
						'state'			=> (string)$xml->state,
						'fullscreen'	=> ($xml->fullscreen == 'true'?TRUE:FALSE),
						'volume'		=> (round((int)$xml->volume * 100 / 256)),
						'random'		=> ($xml->random == 'true'?TRUE:FALSE),
						'loop'			=> ($xml->loop == 'true'?TRUE:FALSE),
						'repeat'		=> ($xml->repeat == 'true'?TRUE:FALSE),
					);
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(!empty($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=in_play&input='.urlencode($input));
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
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
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_pause');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_stop');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Next
	function next() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_next');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_previous');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Seek
	function seek($position) {
		$this->reset_properties();
		if(!empty($position)) {
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=seek&val='.(int)$position);
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Position is missing!';
		}
		return $this->success;
	}
	
	// Fullscreen
	function fullscreen() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=fullscreen');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(!empty($level)) {
			$level = round((int)$level * 256 / 100);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=volume&val='.(int)$level);
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Get
	function pl_get() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/playlist.xml');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
					foreach($xml->node[0] as $item) {
						$this->data[] = array(
							'id'	=> (int)$item['id'],
							'name'	=> (string)$item['name'],
							'file'	=> (string)$item['uri'],
						);
					}
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}

	// Playlist: Add
	function pl_add($input) {
		$this->reset_properties();
		if(!empty($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=in_enqueue&input='.urlencode($input));
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
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
		if(!empty($id)) {
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_delete&id='.(int)$id);
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Id is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Empty
	function pl_empty() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_empty');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Playlist: Play
	function pl_play($id) {
		$this->reset_properties();
		if(!empty($id)) {
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_play&id='.(int)$id);
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
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
		if(!empty($order)) {
			$order = explode(':', $order);
			switch($order[0]) {
				case 'name': $order[0] = 1; break;
				case 'author': $order[0] = 3; break;
				case 'random': $order[0] = 5; break;
				case 'track': $order[0] = 7; break;
				default: $order[0] = 0; // id
			}
			$order[1] = (isset($order[1]) && $order[1] == 'desc'?1:0);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_sort&id='.(int)$order[1].'&val='.(int)$order[0]);
			if($result = curl_exec($this->curl)) {
				try {
					if($xml = @ new SimpleXMLElement($result)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = 'SimpleXMLElement error!';
					}
				} catch(Exception $e) {
					$this->success = FALSE;
					$this->message = $e->getMessage();
				}
			} else {
				$this->success = FALSE;
				$this->message = 'VLC HTTP interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Order is missing!';
		}
		return $this->success;
	}
	
	// Playlist: Random
	function pl_random() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_random');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Playlist: Loop
	function pl_loop() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_loop');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Playlist: Repeat
	function pl_repeat() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/status.xml?command=pl_repeat');
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					$this->success = TRUE;
					$this->message = 'OK';
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Default command
	function command($command, $parameter) {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/requests/vlm_cmd.xml?command='.urlencode($command.(empty($parameter)?'':' '.$parameter)));
		if($result = curl_exec($this->curl)) {
			try {
				if($xml = @ new SimpleXMLElement($result)) {
					if(empty((string)$xml->error)) {
						$this->success = TRUE;
						$this->message = 'OK';
					} else {
						$this->success = FALSE;
						$this->message = (string)$xml->error;
					}
				} else {
					$this->success = FALSE;
					$this->message = 'SimpleXMLElement error!';
				}
			} catch(Exception $e) {
				$this->success = FALSE;
				$this->message = $e->getMessage();
			}
		} else {
			$this->success = FALSE;
			$this->message = 'VLC HTTP interface not available!';
		}
		return $this->success;
	}
	
}

?>
