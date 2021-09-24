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
	
	// Ping mediaplayer service
    public function ping_mediaservice($host) {
        return ping($host);
    }
	
	// Get player status
	public function status() {
		/*
          $this->data = array(
            'playlist_id' => (int) $result['playlist'], // номер или имя плейлиста 
            'playlist_content' => json_encode($playlist_content), // содержимое плейлиста должен быть ВСЕГДА МАССИВ 
            							  // обязательно $playlist_content[$i]['pos'] - номер трека
           							  // обязательно $playlist_content[$i]['file'] - адрес трека
							          // возможно $playlist_content[$i]['Artist'] - артист
            							  // возможно $playlist_content[$i]['Title'] - название трека
            'track_id' => (int) $result['song'], //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'name' => (string) $name, //Current speed for playing media. float.
            'file' => (string) $file, //Current link for media in device. String.
            'length' => (int) $result['duration'], //Track length in seconds. Integer. If unknown = 0. 
            'time' => (int) $result['time'], //Current playback progress (in seconds). If unknown = 0. 
            'state' => (string) strtolower($result['state']), //Playback status. String: stopped/playing/paused/unknown 
            'volume' => (int) $result['volume'], // Volume level in percent. Integer. Some players may have values greater than 100.
            'muted' => (int) $result['muted'], // Volume level in percent. Integer. Some players may have values greater than 100.
            'random' => (int) $result['random'], // Random mode. Boolean. 
            'loop' => (int) $result['loop'], // Loop mode. Boolean.
            'repeat' => (int) $result['repeat'], //Repeat mode. Boolean.
            'crossfade' => (int) $result['xfade'], // crossfade
            'speed' => (int) $speed // crossfade
        );
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

	// Playlist(file): Repeat on/off
	public function set_repeat($repeat=0) {
		/*
			$this->data format (boolean): repeat status (on = true, off = false).
		*/
		return $this->not_supported();
	}
	
	// Playlist(file): Random on/off
	public function set_random($random=0) {
		/*
			$this->data format (boolean): repeat status (on = true, off = false).
		*/
		return $this->not_supported();
	}
	
    // Crossfade player
	public function set_crossfade($crossfade=0) {
		/*
			$this->data format (boolean): repeat status (on = true, off = false).
		*/
		return $this->not_supported();
	}
	
    // Muted player
	public function set_muted($muted=0) {
		/*
			$this->data format (boolean): repeat status (on = true, off = false).
		*/
		return $this->not_supported();
	}
	
    // Playlist (file): Loop on/off
	public function set_loop($loop=0) {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}
	
    // Play the specified file
	public function set_speed($speed=1) {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}

    // Restore playing the specified file
	public function restore_media($input, $position=0) {
		/*
			$input - The path to the file for playback. String.
			
			$this->data format (integer): ID of currently playing track (in playlist). If unknown = -1.
		*/
		return $this->not_supported();
	}
	
    // Restore the specified playlist
	public function restore_playlist($playlist_id, $playlist_content=array(), $track_id = -1, $time = 0, $state = 'stopped') {
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
		return $this->not_supported();
	}
	
	// Playlist: Get
	public function pl_get() {
		/*
			$this->data format (array):
			See status() function
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
