<?php

$terminal['PLAYER_PORT']='80';

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
		
	case 'status': // FIXME
		$json['success'] = TRUE;
		$json['message'] = 'OK';
		$json['data'] = array(
			'track_id'		=> -1,
			'length'		=> 0,
			'time'			=> 0,
			'state'			=> 'unknown',
			'fullscreen'	=> FALSE,
			'volume'		=> (int)getGlobal('ThisComputer.VLCvolumeLevel'),
			'random'		=> FALSE,
			'loop'			=> FALSE,
			'repeat'		=> FALSE,
		);
		break;
	case 'refresh': // deprecated (backward compatibility)
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_close');
		$result = curl_exec($ch);
	case 'play':
		global $input;
		if(!isset($input)) {
			// deprecated (backward compatibility)
			$input = $out['PLAY'];
		}
		$input = preg_replace('/\\\\$/is', '', $input);
		$input = preg_replace('/\/$/is', '', $input);
		if(!preg_match('/^http/', $input)) {
			$input = str_replace('/', "\\", $input);
		}
		$vlc_volume = round(intval(getGlobal('ThisComputer.VLCvolumeLevel')) / 100, 2);
		$volume_params = '--no-volume-save --mmdevice-volume '.$vlc_volume.' --directx-volume '.$vlc_volume.' --waveout-volume '.$vlc_volume; // "--volume" not working (see https://trac.videolan.org/vlc/ticket/3913)
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_play&param='.urlencode($volume_params).(isset($input)?urlencode(" '".$input."'"):''));
		if($result = curl_exec($ch)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'RC interface not available';
		}
		break;
	case 'pause':
		global $id;
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_pause'.(isset($id)?'&param='.intval($id):''));
		if($result = curl_exec($ch)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'RC interface not available';
		}
		break;
	case 'close': // deprecated (backward compatibility)
	case 'stop':
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_close');
		if($result = curl_exec($ch)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'RC interface not available';
		}
		break;
	case 'next':
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_next');
		if($result = curl_exec($ch)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'RC interface not available';
		}
		break;
	case 'prev':
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_prev');
		if($result = curl_exec($ch)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'RC interface not available';
		}
		break;
	case 'fullscreen':
		curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_fullscreen');
		if($result = curl_exec($ch)) {
			if($result == 'OK') {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = $result;
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'RC interface not available';
		}
		break;
	case 'volume':
		global $volume;
		if(isset($volume)) {
			curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command=vlc_volume&param='.intval(getGlobal('ThisComputer.VLCvolumeLevel')).':'.intval($volume));
			if($result = curl_exec($ch)) {
				if($result == 'OK') {
					setGlobal('ThisComputer.VLCvolumeLevel', $volume);
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = $result;
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'RC interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'volume is missing';
		}
		break;

	// PLAYLIST
		
	case 'pl_get':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_add':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_delete':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_empty':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_play':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_sort':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_random':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_loop':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pl_repeat':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	default: // Execute VLM command
		if(isset($command)) {
			curl_setopt($ch, CURLOPT_URL, 'http://'.$terminal['HOST'].':'.$terminal['PLAYER_PORT'].'/rc/?command='.$command);
			if($result = curl_exec($ch)) {
				if($result == 'OK') {
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				} else {
					$json['success'] = FALSE;
					$json['message'] = $result;
				}
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'RC interface not available';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command is missing';
		}
}

//$res = json_encode($json);
//$res = '';
