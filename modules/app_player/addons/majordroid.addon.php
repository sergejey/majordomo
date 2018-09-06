<?php

/*
	Addon MajorDroid for app_player
*/

class majordroid extends app_player_addon {

	// Constructor
	function __construct($terminal) {
		$this->title = 'MajorDroid';
		$this->description = 'Официальное приложение для Android.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Network
		$this->terminal['PLAYER_PORT'] = (empty($this->terminal['PLAYER_PORT'])?7999:$this->terminal['PLAYER_PORT']);
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(!empty($input)) {
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
				$packet = $command.(empty($parameter)?'':':'.$parameter);
				socket_write($socket, $packet, strlen($packet));
				$this->success = TRUE;
				$this->message = 'OK';
			}
			socket_close($socket);
		}
		return $this->success;
	}
	
}

?>
