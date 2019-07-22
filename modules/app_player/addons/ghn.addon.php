<?php

/*
	Addon Google Home Notifier for app_player
*/

class ghn extends app_player_addon {

	// Private properties
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'Google Home Notifier';
		$this->description = 'Умная колонка от Google.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Network
		$this->terminal['PLAYER_PORT'] = (empty($this->terminal['PLAYER_PORT'])?8091:$this->terminal['PLAYER_PORT']);
		$this->address = 'http://'.$this->terminal['HOST'].':'.$this->terminal['PLAYER_PORT'];
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			if(getURL($this->address.'/google-home-notifier?text='.urlencode($input), 0)) {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = 'Command execution error!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		if(getURL($this->address.'/google-home-notifier?text='.urlencode('http://somefakeurl.stream/'), 0)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'Command execution error!';
		}
		return $this->success;
	}

}

?>
