<?php
/**
 * VLC over HTTP
 * @access public
 */

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$playerAddr = 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'];

$json = array(
	'play_terminal'		=> $play_terminal,
	'session_terminal'	=> $play_terminal,
	'command'			=> $command,
	'success'			=> FALSE,
	'message'			=> '',
	'data'				=> NULL,
);

switch($command) {
		
	// GENERAL
		
	case 'status':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
				$json['data'] = array(
					'track_id'		=> (int)$xml->currentplid,
					'length'		=> (int)$xml->length,
					'time'			=> (int)$xml->time,
					'state'			=> (string)$xml->state,
					'fullscreen'	=> ($xml->fullscreen == 'true'?TRUE:FALSE),
					'volume'		=> round((int)$xml->volume * 100 / 256),
					'random'		=> ($xml->random == 'true'?TRUE:FALSE),
					'loop'			=> ($xml->loop == 'true'?TRUE:FALSE),
					'repeat'		=> ($xml->repeat == 'true'?TRUE:FALSE),
				);
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'refresh': // deprecated (backward compatibility)
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_empty');
		$result = curl_exec($ch);
	case 'play':
		global $input;
		if(!isset($input)) {
			// deprecated (backward compatibility)
			$input = $out['PLAY'];
		}
		$input = preg_replace('/\\\\$/is', '', $input);
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=in_play'.(isset($input)?'&input='.rawurlencode($input):''));
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'pause':
		global $id;
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_pause'.(isset($id)?'&id='.intval($id):''));
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'close': // deprecated (backward compatibility)
	case 'stop':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_stop');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'next':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_next');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'prev':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_previous');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'fullscreen':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=fullscreen');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'volume':
		global $value;
		if(!isset($value)) {
			// deprecated (backward compatibility)
			$value = $volume * 3;
		}
		if(isset($value)) {
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=volume&val='.intval($value));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'val is missing';
		}
		break;
	/*
	case 'browse':
		global $dir;
		if(isset($dir)) {
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/browse.xml?dir='.rawurlencode($dir));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
					foreach($xml as $item) {
						$json['data'][] = (string)$item['name'];
					}
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'dir is missing';
		}
		break;
	*/
		
	// PLAYLIST
		
	case 'pl_get':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/playlist.xml');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
				foreach($xml->node[0] as $item) {
					$json['data'][] = array(
						'id'	=> (int)$item['id'],
						'name'	=> (string)$item['name'],
						'file'	=> (string)$item['uri'],
					);
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'pl_add':
		global $input;
		if(isset($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=in_enqueue&input='.rawurlencode($input));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'input is missing';
		}
		break;
	case 'pl_delete':
		global $id;
		if(isset($id)) {
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_delete&id='.intval($id));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'id is missing';
		}
		break;
	case 'pl_empty':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_empty');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'pl_play':
		global $id;
		if(isset($id)) {
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_play&id='.intval($id));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'id is missing';
		}
		break;
	case 'pl_sort':
		global $order, $value;
		switch($value) {
			case 'name': $value = 1; break;
			case 'author': $value = 3; break;
			case 'random': $value = 5; break;
			case 'track': $value = 7; break;
			default: $value = 0; // id
		}
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_sort&id='.($order=='desc'?1:0).'&val='.$value);
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'pl_random':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_random');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'pl_loop':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_loop');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	case 'pl_repeat':
		curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_repeat');
		if($result = curl_exec($ch)) {
			if($xml = new SimpleXMLElement($result)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'XML parsing error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'VLC HTTP interface not available';
		}
		break;
	/*
	case 'pl_sd':
		global $value;
		if(isset($value)) {
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/status.xml?command=pl_sd&val='.rawurlencode($value));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'value is missing';
		}
		break;
	*/
	default: // Execute VLM command
		if(isset($command)) {
			curl_setopt($ch, CURLOPT_URL, $playerAddr.'/requests/vlm_cmd.xml?command='.rawurlencode($command));
			if($result = curl_exec($ch)) {
				if($xml = new SimpleXMLElement($result)) {
					if(empty((string)$xml->error)) {
						$json['success'] = TRUE;
						$json['message'] = 'OK';
						$json['data'] = $result;
					} else {
						$json['success'] = FALSE;
						$json['message'] = (string)$xml->error;
					}
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'XML parsing error';
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'VLC HTTP interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command is missing';
		}
}

$res = json_encode($json);
$res = '';
