<?php

/*
	Addon MajorDroid for app_player
*/

class majordroid extends app_player_addon {

	// Constructor
	function __construct($terminal) {
		$this->title = 'MajorDroid';
		$this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука на устройствах которые поддерживаают MajorDroid API.<br>';
		$this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; ??? нужно уточнять ??? (если ТТС такого же типа, что и плеер).<br>';
		$this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
		$this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7999 (если по умолчанию, можно не указывать).';
		
		$this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
		$this->reset_properties();
		
		// Network
		$this->terminal['PLAYER_PORT'] = (empty($this->terminal['PLAYER_PORT'])?7999:$this->terminal['PLAYER_PORT']);
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if($socket === false) {
				$this->success = FALSE;
				$this->message = socket_strerror(socket_last_error());
				$this->message = iconv('CP1251', 'UTF-8', $this->message);
			} else {
				$result = @socket_connect($socket, $this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
				if($result === false) {
					$this->success = FALSE;
					$this->message = socket_strerror(socket_last_error($socket));
					$this->message = iconv('CP1251', 'UTF-8', $this->message);
				} else {
					$packet = 'play:'.$input;
					socket_write($socket, $packet, strlen($packet));
					$this->success = TRUE;
					$this->message = 'OK';
				}
				socket_close($socket);
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}

	// Volume
	function set_volume($volume) {
		$this->reset_properties();
		if(strlen($volume)) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if($socket === false) {
				$this->success = FALSE;
				$this->message = socket_strerror(socket_last_error());
				$this->message = iconv('CP1251', 'UTF-8', $this->message);
			} else {
				$result = @socket_connect($socket, $this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
				if($result === false) {
					$this->success = FALSE;
					$this->message = socket_strerror(socket_last_error($socket));
					$this->message = iconv('CP1251', 'UTF-8', $this->message);
				} else {
					$packet = 'volume:'.$volume;
					socket_write($socket, $packet, strlen($packet));
					$this->success = TRUE;
					$this->message = 'OK';
				}
				socket_close($socket);
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
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($socket === false) {
			$this->success = FALSE;
			$this->message = socket_strerror(socket_last_error());
			$this->message = iconv('CP1251', 'UTF-8', $this->message);
		} else {
			$result = @socket_connect($socket, $this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
			if($result === false) {
				$this->success = FALSE;
				$this->message = socket_strerror(socket_last_error($socket));
				$this->message = iconv('CP1251', 'UTF-8', $this->message);
			} else {
				$packet = 'pause';
				socket_write($socket, $packet, strlen($packet));
				$this->success = TRUE;
				$this->message = 'OK';
			}
			socket_close($socket);
		}
		return $this->success;
	}

	// Stop
	function stop() {
		return $this->pause();
	}

	// Default command
	function command($command, $parameter) {
		$this->reset_properties();
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($socket === false) {
			$this->success = FALSE;
			$this->message = socket_strerror(socket_last_error());
			$this->message = iconv('CP1251', 'UTF-8', $this->message);
		} else {
			$result = @socket_connect($socket, $this->terminal['HOST'], $this->terminal['PLAYER_PORT']);
			if($result === false) {
				$this->success = FALSE;
				$this->message = socket_strerror(socket_last_error($socket));
				$this->message = iconv('CP1251', 'UTF-8', $this->message);
			} else {
				$packet = $command.(strlen($parameter)?':'.$parameter:'');
				socket_write($socket, $packet, strlen($packet));
				$this->success = TRUE;
				$this->message = 'OK';
			}
			socket_close($socket);
		}
		return $this->success;
	}
	
    // Get player status
    function status()
    {
        $this->reset_properties();
        // Defaults
		$playlist_id = -1;
		$playlist_content = array();
        $track_id = -1;
		$name     = -1;
		$file     = -1;
        $length   = -1;
        $time     = -1;
        $state    = -1;
        $volume   = -1;
		$muted    = -1;
        $random   = -1;
        $loop     = -1;
        $repeat   = -1;
        $crossfade= -1;
		$speed = -1;
		
        $this->data = array(
                'playlist_id' => (int)$playlist_id, // номер или имя плейлиста 
                'playlist_content' => $playlist_content, // содержимое плейлиста должен быть ВСЕГДА МАССИВ 
                                                         // обязательно $playlist_content[$i]['pos'] - номер трека
                                                         // обязательно $playlist_content[$i]['file'] - адрес трека
                                                         // возможно $playlist_content[$i]['Artist'] - артист
                                                         // возможно $playlist_content[$i]['Title'] - название трека
				'track_id' => (int) track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
			    'name' => (string) $name, //Current speed for playing media. float.
				'file' => (string) $file, //Current link for media in device. String.
                'length' => (int) $length, //Track length in seconds. Integer. If unknown = 0. 
                'time' => (int) $time, //Current playback progress (in seconds). If unknown = 0. 
                'state' => (string) strtolower($state), //Playback status. String: stopped/playing/paused/unknown 
                'volume' => (int)$volume, // Volume level in percent. Integer. Some players may have values greater than 100.
                'muted' => (int) $random, // Volume level in percent. Integer. Some players may have values greater than 100.
                'random' => (int) $random, // Random mode. Boolean. 
                'loop' => (int) $loop, // Loop mode. Boolean.
                'repeat' => (int) $repeat, //Repeat mode. Boolean.
                'crossfade' => (int) $crossfade, // crossfade
                'speed' => (int) $speed, // crossfade
            );
		// удаляем из массива пустые данные
		foreach ($this->data as $key => $value) {
			if ($value == '-1' or !$value) unset($this->data[$key]);
		}
				        
        $this->success = TRUE;
        $this->message = 'OK';
        return $this->success;
    }
}

?>
