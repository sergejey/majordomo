<?php

$address = $terminal['HOST'];
$port = $terminal['PLAYER_PORT'];

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
			'volume'		=> 100,
			'random'		=> FALSE,
			'loop'			=> FALSE,
			'repeat'		=> FALSE,
		);
		break;
	case 'refresh': // deprecated (backward compatibility)
	case 'play':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'pause':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
		break;
	case 'close': // deprecated (backward compatibility)
	case 'stop':
		// FIXME
		$json['success'] = FALSE;
		$json['message'] = 'command is not supported for this player type';
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
			// FIXME
			$json['success'] = FALSE;
			$json['message'] = 'command is not supported for this player type';
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command is missing';
		}
}

$res = json_encode($json);
$res = '';
