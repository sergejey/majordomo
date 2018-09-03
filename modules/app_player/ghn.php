<?php

$port = (!empty($terminal['PLAYER_PORT'])?$terminal['PLAYER_PORT']:8091);
$host = $terminal['HOST'];

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
		$url = 'http://'.$host.':'.$port.'/google-home-notifier';
		if(getURL($url, 0)) {
			$json['success'] = TRUE;
			$json['message'] = 'OK';
			$json['data'] = array(
				'track_id'		=> -1,
				'length'		=> 0,
				'time'			=> 0,
				'state'			=> 'unknown',
				'fullscreen'	=> FALSE,
				'volume'		=> 100,
				'random'		=> FALSE,
				'loop'			=> FALSE,
				'repeat'		=> FALSE,
			);
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command execution error';
		}
		break;
	case 'refresh': // deprecated (backward compatibility)
	case 'play':
		global $input;
		if(!isset($input)) {
			// deprecated (backward compatibility)
			$input = $out['PLAY'];
		}
		//$input = preg_replace('/\\\\$/is', '', $input);
		//$input = preg_replace('/\/$/is', '', $input);
		//if(!preg_match('/^http/', $input)) {
		//	$input = str_replace('/', "\\", $input);
		//}
		if(isset($input)) {
			$url = 'http://'.$host.':'.$port.'/google-home-notifier?text='.urlencode($input);
			if(getURL($url, 0)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'command execution error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'input is missing';
		}
		break;
	case 'pause': // FIXME
	case 'close': // deprecated (backward compatibility)
	case 'stop':
		$input = 'http://somefakeurl.stream/';
		$url = 'http://'.$host.':'.$port.'/google-home-notifier?text='.urlencode($input);
		if(getURL($url, 0)) {
			$json['success'] = TRUE;
			$json['message'] = 'OK';
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command execution error';
		}
		break;
	case 'next':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'prev':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'fullscreen':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'volume':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
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
	default: // Execute google-chrome-notifier command
		if(isset($command)) {
			$url = 'http://'.$host.':'.$port.'/google-home-notifier?text='.urlencode($command);
			if(getURL($url, 0)) {
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'command execution error';
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command is missing';
		}
}

$res = json_encode($json);
$res = '';
