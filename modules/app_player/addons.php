<?php

/*
	General class for app_player addons
*/

class app_player_addon {
	
	// Properties
	public $title = NULL;
	public $description = NULL;
	
	public $terminal = NULL;
	public $success = FALSE;
	public $message = NULL;
	public $data = NULL;
	
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
		$this->__destruct();
	}

	// Reset properties
	final public function reset_properties() {
		$this->success = FALSE;
		$this->message = NULL;
		$this->data = NULL;
	}
	
	// Unknown method
	final public function __call($name, $parameters) {
		/*
		Do not use directly
		*/
		return $this->command($name, $parameters[0]);
	}
	
	// Command Not Supported
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
		$this->reset_properties();
		$this->success = FALSE;
		$this->message = 'Command "'.(string)$command.'" is not supported for this player type!';
		return $this->success;
	}

	// Get player status
	public function status() {
		/*
		Data format (array):
		
		'track_id'	 -	Number of currently playing track (in playlist).
						Integer. If unknown (playback stopped or playlist is empty) = -1.
		'length'	 -	Track length in seconds. Integer. If unknown = 0.
		'time'		 -	Current playback progress (in seconds). If unknown = 0.
		'state'		 -	Playback status. String: stopped/playing/paused/unknown
		'fullscreen' -	Full screen mode. Boolean.
		'volume'	 -	Volume level in percent. Integer. Some players may have values greater than 100.
		'random'	 -	Random mode. Boolean.
		'loop'		 -	Loop mode. Boolean.
		'repeat'	 -	Repeat mode. Boolean.
		*/
		return $this->not_supported();
	}
	
	// Deprecated (backward compatibility)
	final public function refresh($input) {
		// Please do not use this command! Instead, use play()
		return $this->play($input);
	}
	
	// Play
	public function play($input) {
		/*
		$input - The path to the file for playback. String.
		*/
		return $this->not_supported();
	}
	
	// Pause/Resume
	public function pause() {
		return $this->not_supported();
	}
	
	// Deprecated (backward compatibility)
	final public function close() {
		// Please do not use this command! Instead, use stop()
		return $this->stop();
	}
	
	// Stop
	public function stop() {
		return $this->not_supported();
	}
	
	// Next
	public function next() {
		return $this->not_supported();
	}
	
	// Prev
	public function prev() {
		return $this->not_supported();
	}
	
	// Seek
	public function seek($position) {
		/*
		$position - Position in seconds. Integer.
		*/
		return $this->not_supported();
	}
	
	// Fullscreen/Window mode
	public function fullscreen() {
		return $this->not_supported();
	}
	
	// Set volume level
	public function volume($level) {
		/*
		$level - Volume level in percent. Integer.
		*/
		return $this->not_supported();
	}
	
	// Playlist: Get
	public function pl_get() {
		return $this->not_supported();
	}

	// Playlist: Add
	public function pl_add($input) {
		/*
		$input - The path to the file for add to the playlist. String.
		*/
		return $this->not_supported();
	}
	
	// Playlist: Delete
	public function pl_delete($id) {
		/*
		$id - The track number to remove from the playlist. Integer. See status() function (track_id).
		*/
		return $this->not_supported();
	}
	
	// Playlist: Empty
	public function pl_empty() {
		return $this->not_supported();
	}
	
	// Playlist: Play
	public function pl_play($id) {
		/*
		$id - The position of the playback track. Integer. See status() function (track_id).
		*/
		return $this->not_supported();
	}
	
	// Playlist: Sort
	public function pl_sort($order) {
		/*
		$order - Sort playlist. String. Format: <sort_type>:<desc>
			sort_type = name/author/random/track
			desc = 1 (desc) or 0 (asc)
			Example: 'author:1' (without quotes)
		*/
		return $this->not_supported();
	}
	
	// Playlist: Random
	public function pl_random($enable) {
		/*
		$enable - Integer. 1 = enable, 0 = disable
		*/
		return $this->not_supported();
	}
	
	// Playlist: Loop
	public function pl_loop($enable) {
		/*
		$enable - Integer. 1 = enable, 0 = disable
		*/
		return $this->not_supported();
	}
	
	// Playlist: Repeat
	public function pl_repeat($enable) {
		/*
		$enable - Integer. 1 = enable, 0 = disable
		*/
		return $this->not_supported();
	}
	
	// Default command
	public function command($command, $parameter) {
		/*
		$command - Command name. String.
		$parameter - Command parameter. String.
		*/
		return $this->not_supported($command);
	}
	
}

?>
