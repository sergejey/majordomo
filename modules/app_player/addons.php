<?php

/*
	General class for app_player addons
*/

class app_player_addon {
	
	/*
		Properties
	*/
	
	// Addon info
	public $title = NULL;
	public $description = NULL;
	
	// Informations
	public $terminal = NULL;
	public $success = FALSE;
	public $message = NULL;
	public $data = NULL;
	
	/*
		Service methods
	*/
	
	// Constructor
	function __construct($terminal) {
		/*
			$terminal - Array. See `terminals` table (in MySQL).
		*/
		$this->terminal = $terminal;
		$this->reset_properties();
	}
	
	// Destructor
	public function destroy() {
		/*
			Some code...
		*/
	}
	
	/*
		Playback methods
	*/
	
	// Get player status
	public function status() {
		/*
			$this->data format (array):
			
			'track_id'	 -	ID of currently playing track (in playlist).
							Integer. If unknown (playback stopped or playlist is empty) = -1.
			'length'	 -	Track length in seconds. Integer. If unknown = 0.
			'time'		 -	Current playback progress (in seconds). If unknown = 0.
			'state'		 -	Playback status. String: stopped/playing/paused/unknown
			'volume'	 -	Volume level in percent. Integer. Some players may have values greater than 100.
			'random'	 -	Random mode. Boolean.
			'loop'		 -	Loop mode. Boolean.
			'repeat'	 -	Repeat mode. Boolean.
		*/
		return $this->not_supported();
	}

	// Play the specified file
	public function play($input) {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}
	
	// Pause/Resume
	public function pause() {
		/*
			$this->data format (boolean): pause status (paused = true, else = false).
		*/
		return $this->not_supported();
	}

	// Stop playback
	public function stop() {
		/*
			$this->data format: NULL.
		*/
		return $this->not_supported();
	}
	
	// Next track
	public function next() {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}
	
	// Previous track
	public function previous() {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}
	
	// Set playback position
	public function seek($position) {
		/*
			$position - Position in seconds. Integer.
			
			$this->data format: NULL.
		*/
		return $this->not_supported();
	}

	// Set media volume level
	public function set_volume($level) {
		/*
			$level - Volume level in percent. Integer.
			
			$this->data format: NULL.
		*/
		return $this->not_supported();
	}
	
	// Get media volume level
	public function get_volume() {
		/*
			$this->data format (integer): Current volume level in percent.
		*/
		if($this->status()) {
			$volume = $this->data['volume'];
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = $volume;
		} elseif(strtolower($this->terminal['HOST']) == 'localhost' || $this->terminal['HOST'] == '127.0.0.1' || $this->terminal['NAME'] == 'MAIN') {
			$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
			$this->data = (int)getGlobal('ThisComputer.volumeMediaLevel');
		} else {
			$this->not_supported();
		}
		return $this->success;
	}
	
	// Playlist: Get
	public function pl_get() {
		/*
			$this->data format (array):
			
			'id'	- track ID;
			'name'	- title;
			'file'	- file path.
		*/
		return $this->not_supported();
	}

	// Playlist: Add
	public function pl_add($input) {
		/*
			$input - The path to the file for add to the playlist. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}
	
	// Playlist: Delete
	public function pl_delete($id) {
		/*
			$id - The track number to remove from the playlist. Integer. See status() function (track_id).
			
			$this->data format: NULL.
		*/
		return $this->not_supported();
	}
	
	// Playlist: Empty
	public function pl_empty() {
		/*
			$this->data format: NULL.
		*/
		return $this->not_supported();
	}
	
	// Playlist: Play
	public function pl_play($id) {
		/*
			$id - The position of the playback track. Integer. See status() function (track_id).
			
			$this->data format: NULL.
		*/
		return $this->not_supported();
	}

	// Playlist: Random on/off
	public function pl_random() {
		/*
			$this->data format (boolean): random status (on = true, off = false).
		*/
		return $this->not_supported();
	}

	// Playlist: Loop on/off
	public function pl_loop() {
		/*
			$this->data format (boolean): loop status (on = true, off = false).
		*/
		return $this->not_supported();
	}

	// Playlist: Repeat on/off
	public function pl_repeat() {
		/*
			$this->data format (boolean): repeat status (on = true, off = false).
		*/
		return $this->not_supported();
	}

	// Default command
	public function command($command, $parameter) {
		/*
			$command - Command name. String.
			$parameter - Command parameter. String.
			
			$this->data format (string): result of the command.
		*/
		return $this->not_supported($command);
	}
	
	/*
		Final methods
	*/
	
	// Reset properties
	final public function reset_properties($defaults=array()) {
		/*
			$defaults - Associative array with default property values.
			
			Example:
				array('message'=>'Hello')
		*/
		$this->success = FALSE;
		$this->message = NULL;
		$this->data = NULL;
		foreach($defaults as $key=>$value) {
			if(property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
		return $this;
	}
	
	// Unknown method
	final public function __call($name, $parameters) {
		/*
			Do not use directly
		*/
		return $this->command($name, $parameters[0]);
	}
	
	// Command not supported
	final public function not_supported($command=NULL) {
		/*
			$command - Command name. String.
		*/
		if($command === NULL) {
			$backtrace = debug_backtrace();
			if(isset($backtrace[1])) {
				$command = $backtrace[1]['function'];
			} else {
				$command = 'unknown';
			}
		}
		$this->reset_properties(array('success'=>FALSE, 'message'=>'Command "'.(string)$command.'" is not supported for this player type!'));
		return $this->success;
	}
	
	// Set system volume level
	final public function set_system_volume($level) {
		/*
			$level - Volume level in percent. Integer.
			
			$this->data format: NULL.
		*/
		$this->reset_properties();
		if(strlen($level)) {
			setGlobal('ThisComputer.volumeLevel', (int)$level);
			callMethod('ThisComputer.VolumeLevelChanged', array('VALUE' => (int)$level, 'HOST' => $this->terminal['HOST']));
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'Level is missing!';
		}
		return $this->success;
	}
	
	// Get system volume level
	final public function get_system_volume() {
		/*
			$this->data format (integer): Current volume level in percent.
		*/
		$this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
		$this->data = (int)getGlobal('ThisComputer.volumeLevel');
		return $this->success;
	}
	
	// Play the specified file without breaking the current playlist
	final public function safe_play($input) {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (boolean): Playback result (TRUE = successful, FALSE = unable to safely play)
		*/
		return $this->play($input); // FIXME
	}
	
}

?>
