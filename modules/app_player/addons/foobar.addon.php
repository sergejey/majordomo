<?php

/*
	Addon Foobar2000 for app_player
*/

class foobar extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'Foobar2000';
		$this->description = 'Мощный медиаплеер, созданный одним из разработчиков WinAmp.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?8888:$this->terminal['PLAYER_PORT']);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
	}
	
	// Destructor
	function destroy() {
		curl_close($this->curl);
	}

	// Play
	function play($input) {
		$this->reset_properties();
		$input = preg_replace('/\\\\$/is', '', $input);
		$input = preg_replace('/\/$/is', '', $input);
		if(!preg_match('/^http/', $input)) {
			$input = str_replace('/', "\\", $input);
		}
		if(!empty($input)) {
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=EmptyPlaylist&param3=NoResponse');
			curl_exec($this->curl);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=Browse&param1='.rawurlencode($input).'&param2=EnqueueDirSubdirs&param3=NoResponse');
			curl_exec($this->curl);
		}
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=Start&param1=0&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Pause
	function pause() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=PlayOrPause&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=Stop&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Next
	function next() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=StartNext&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Prev
	function prev() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=StartPrevious&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}

	// Default command
	function command($command, $parameter) {
		$this->reset_properties();
		if(!empty($command)) {
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd='.rawurlencode($command).(empty($parameter)?'':'&param1='.rawurlencode($parameter)).'&param3=NoResponse');
			if($result = curl_exec($this->curl)) {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = 'HTTP interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Command is missing!';
		}
		return $this->success;
	}
	
}

?>
