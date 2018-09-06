<?php

/*
	Addon MPD for app_player
*/

class mpd extends app_player_addon {

	// Constructor
	function __construct($terminal) {
		$this->title = 'Music Player Daemon (MPD)';
		$this->description = 'Музыкальный проигрыватель, имеющий клиент-серверную архитектуру.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Network
		$this->terminal['PLAYER_PORT'] = (empty($this->terminal['PLAYER_PORT'])?6600:$this->terminal['PLAYER_PORT']);
		
		// MPD
		include_once(DIR_MODULES.'app_player/libs/mpd/mpd.class.php');
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(!empty($input)) {
			$mpd = new mpd_player($this->terminal['HOST'], $this->terminal['PLAYER_PORT'], $terminal['PLAYER_PASSWORD']);
			if($mpd->connected) {
				$mpd->PLClear();
				$mpd->DBRefresh();
				$mpd->PLAdd($input);
				$mpd->Play();
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = 'Error connecting to MPD server!';
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
		$mpd = new mpd_player($this->terminal['HOST'], $this->terminal['PLAYER_PORT'], $terminal['PLAYER_PASSWORD']);
		if($mpd->connected) {
			$mpd->Pause();
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'Error connecting to MPD server!';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		$mpd = new mpd_player($this->terminal['HOST'], $this->terminal['PLAYER_PORT'], $terminal['PLAYER_PASSWORD']);
		if($mpd->connected) {
			$mpd->Stop();
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'Error connecting to MPD server!';
		}
		return $this->success;
	}
	
	// Set volume
	function set_volume($level) {
		$this->reset_properties();
		if(!empty($level)) {
			$mpd = new mpd_player($this->terminal['HOST'], $this->terminal['PLAYER_PORT'], $terminal['PLAYER_PASSWORD']);
			if($mpd->connected) {
				$mpd->SetVolume($level);
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = 'Error connecting to MPD server!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}

}

?>
