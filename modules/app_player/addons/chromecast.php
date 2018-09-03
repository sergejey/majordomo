<?php

$address = $terminal['HOST']; // ip

include_once(DIR_MODULES.'app_player/addons/castv2/Chromecast.php');

//Define('CHROMECAST_DEBUG', 1);
	
$json = array(
	'play_terminal'		=> $play_terminal,
	'session_terminal'	=> $play_terminal,
	'command'			=> $command,
	'success'			=> FALSE,
	'message'			=> '',
	'data'				=> NULL,
);

try {
	$cc = new Chromecast($address, 8009);
	$cc->requestId = time();

	switch($command) {

		// GENERAL

		case 'status': // FIXME
			// $cc->DMP->getStatus();
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
			// $cc->DMP->restart();
		case 'play':
			global $input;
			if(!isset($input)) {
				// deprecated (backward compatibility)
				$input = $out['PLAY'];
			}
			if(isset($input)) {
				if(preg_match('/\.mp3/', $input)) {
					$content_type = 'audio/mp3';
				} elseif(preg_match('/mp4/', $input)) {
					$content_type = 'video/mp4';
				} elseif(preg_match('/m4a/', $input)) {
					$content_type = 'audio/mp4';
				} elseif(preg_match('/^http/', $input)) {
					$content_type = '';
					if($fp = fopen($input, 'r')) {
						$meta = stream_get_meta_data($fp);
						if(is_array($meta['wrapper_data'])) {
							$items = $meta['wrapper_data'];
							foreach($items as $line) {
								if(preg_match('/Content-Type:(.+)/is', $line,$m)) {
									$content_type = trim($m[1]);
								}
							}
						}
						fclose($fp);
					}
				}
				if(!$content_type) {
					$content_type = 'audio/mpeg';
				}
				$cc->DMP->play($input, 'LIVE', $content_type, true, 0);
				$json['success'] = TRUE;
				$json['message'] = 'OK';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'input is missing';
			}
			break;
		case 'pause':
			$cc->DMP->pause();
			$json['success'] = TRUE;
			$json['message'] = 'OK';
			break;
		case 'close': // deprecated (backward compatibility)
		case 'stop':
			$cc->DMP->stop();
			$json['success'] = TRUE;
			$json['message'] = 'OK';
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
			global $volume;
			if(isset($volume)) {
				$volume = round($volume/100, 1);
				$cc->DMP->SetVolume($volume);
				$json['success'] = TRUE;
				$json['message'] = 'OK';
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
		default: // Execute chromecast command
			if(isset($command)) {
				// FIXME
				$json['success'] = FALSE;
				$json['message'] = 'command is not supported for this player type';
			} else {
				$json['success'] = FALSE;
				$json['message'] = 'command is missing';
			}
	}
} catch (Exception $e) {
	$json['success'] = FALSE;
	$json['message'] = $e->getMessage();
}


$res = json_encode($json);
$res = '';
