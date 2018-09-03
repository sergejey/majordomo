<?php

/**
 * Title
 *
 * Description
 *
 * @access public
 */

$address = $terminal['HOST']; // ip
$service_port = '7999';

$packet = NULL;

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
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($socket === false) {
			$json['success'] = FALSE;
			$json['message'] = 'socket_create() failed: '.socket_strerror(socket_last_error());
		} else {
			$result = @socket_connect($socket, $address, $service_port);
			if($result === false) {
				$json['success'] = FALSE;
				$json['message'] = 'socket_connect() failed: '.socket_strerror(socket_last_error($socket));
			} else {
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
			}
			 socket_close($socket);
		}
		break;
	case 'refresh': // deprecated (backward compatibility)
	case 'play':
		global $input;
		if(!isset($input)) {
			// deprecated (backward compatibility)
			$input = $out['PLAY'];
		}
		if(isset($input)) {
			//$input = preg_replace('/\\\\$/is', '', $input);
			//$input = preg_replace('/\/$/is', '', $input);
			//if(!preg_match('/^http/', $input)) {
			//	$input = str_replace('/', "\\", $input);
			//}
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if($socket === false) {
				$json['success'] = FALSE;
				$json['message'] = 'socket_create() failed: '.socket_strerror(socket_last_error());
			} else {
				$result = @socket_connect($socket, $address, $service_port);
				if($result === false) {
					$json['success'] = FALSE;
					$json['message'] = 'socket_connect() failed: '.socket_strerror(socket_last_error($socket));
				} else {
					$packet = 'play:'.$input;
					socket_write($socket, $packet, strlen($packet));
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				}
				 socket_close($socket);
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'input is missing';
		}
		break;
	case 'close': // deprecated (backward compatibility)
	case 'stop': // FIXME
	case 'pause':
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($socket === false) {
			$json['success'] = FALSE;
			$json['message'] = 'socket_create() failed: '.socket_strerror(socket_last_error());
		} else {
			$result = @socket_connect($socket, $address, $service_port);
			if($result === false) {
				$json['success'] = FALSE;
				$json['message'] = 'socket_connect() failed: '.socket_strerror(socket_last_error($socket));
			} else {
				$packet = 'pause';
				socket_write($socket, $packet, strlen($packet));
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			}
			 socket_close($socket);
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
	default: // Execute majordroid command
		if(isset($command)) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if($socket === false) {
				$json['success'] = FALSE;
				$json['message'] = 'socket_create() failed: '.socket_strerror(socket_last_error());
			} else {
				$result = @socket_connect($socket, $address, $service_port);
				if($result === false) {
					$json['success'] = FALSE;
					$json['message'] = 'socket_connect() failed: '.socket_strerror(socket_last_error($socket));
				} else {
					$packet = $command;
					socket_write($socket, $packet, strlen($packet));
					$json['success'] = TRUE;
					$json['message'] = 'OK';
				}
				 socket_close($socket);
			}
		} else {
			$json['success'] = FALSE;
			$json['message'] = 'command is missing';
		}
}

$res = json_encode($json);
$res = '';
