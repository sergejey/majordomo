<?php
/* 
 * maintained by
 * - Sven Ginka (sven.ginka@gmail.com) 
 *
 * Version mpd.class.php-1.3a 01/2013
 * - added property db_playtime
 * 
 * Version mpd.class.php-1.3 03/2010
 * - take over from Hendrik Stoetter
 * - removed "split()" as this function is marked depracted
 * - added property "xfade" (used by IPodMp, phpMp+)
 * - added property "bitrate" (used by phpMp+)
 * - added define "MPD_SEARCH_FILENAME"
 * - included sorting algorithm "msort"
 * - added function validateFile() for guessing a title if no ID3 data is given 
 * 
 * Hendrik Stoetter 03/2008
 * - this a lightly modified version of mod.class Version 1.2.
 * - fixed some bugs and added some new functions
 * - Changes:
 * 		GetDir($url) -> GetDir(url,$sort)
 * 		var $stats
 * 
 *  Benjamin Carlisle 05/05/2004
 * 
 *  mpd.class.php - PHP Object Interface to the MPD Music Player Daemon
 *  Version 1.2, Released 05/05/2004
 *  Copyright (C) 2003-2004  Benjamin Carlisle (bcarlisle@24oz.com)
 *  http://mpd.24oz.com/ | http://www.musicpd.org/
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 *
 *
 */ 

/*
 * MPD command reference at http://www.musicpd.org/doc/protocol/index.html
 */

// Create common command definitions for MPD to use
define("MPD_CMD_STATUS",      "status");
define("MPD_CMD_STATISTICS",  "stats");
define("MPD_CMD_VOLUME",      "volume");
define("MPD_CMD_SETVOL",      "setvol");
define("MPD_CMD_PLAY",        "play");
define("MPD_CMD_PLAYID",      "playid");
define("MPD_CMD_STOP",        "stop");
define("MPD_CMD_PAUSE",       "pause");
define("MPD_CMD_NEXT",        "next");
define("MPD_CMD_PREV",        "previous");
define("MPD_CMD_PLLIST",      "playlistinfo");
define("MPD_CMD_PLADD",       "add");
define("MPD_CMD_PLREMOVE",    "delete");
define("MPD_CMD_PLREMOVEID",  "deleteid");
define("MPD_CMD_PLCLEAR",     "clear");
define("MPD_CMD_PLSHUFFLE",   "shuffle");
define("MPD_CMD_PLLOAD",      "load");
define("MPD_CMD_PLSAVE",      "save");
define("MPD_CMD_KILL",        "kill");
define("MPD_CMD_REFRESH",     "update");
define("MPD_CMD_SINGLE",      "single");
define("MPD_CMD_REPEAT",      "repeat");
define("MPD_CMD_LSDIR",       "lsinfo");
define("MPD_CMD_SEARCH",      "search");
define("MPD_CMD_START_BULK",  "command_list_begin");
define("MPD_CMD_END_BULK",    "command_list_end");
define("MPD_CMD_FIND",        "find");
define("MPD_CMD_RANDOM",      "random");
define("MPD_CMD_SEEK",        "seek");
define("MPD_CMD_PLSWAPTRACK", "swap");
define("MPD_CMD_PLMOVETRACK", "move");
define("MPD_CMD_PASSWORD",    "password");
define("MPD_CMD_TABLE",       "list");
define("MPD_CMD_PLMOVE",      "move" );

// Predefined MPD Response messages
define("MPD_RESPONSE_ERR", "ACK");
define("MPD_RESPONSE_OK",  "OK");

// MPD State Constants
define("MPD_STATE_PLAYING", "play");
define("MPD_STATE_STOPPED", "stop");
define("MPD_STATE_PAUSED",  "pause");

// MPD Searching Constants
define("MPD_SEARCH_ARTIST", "artist");
define("MPD_SEARCH_TITLE",  "title");
define("MPD_SEARCH_ALBUM",  "album");
define("MPD_SEARCH_ANY",  	"any");
define("MPD_SEARCH_FILENAME","filename"); 

// MPD Cache Tables
define("MPD_TBL_ARTIST","artist");
define("MPD_TBL_ALBUM","album");


$mpd_debug = 0;

function addLog($text){
	global $mpd_debug;
	$style="background-color:lightgrey;border:thin solid grey;margin:5px;padding:5px";
	if ($mpd_debug) echo '<div style="'.$style.'">log:>'.$text.'</div>';
}
function addErr($err){
	global $mpd_debug;
	if ($mpd_debug) echo 'error:>'.$err.'<br>';
}

class mpd_player {
	// TCP/Connection variables
	var $host;
	var $port;
    var $password;

	var $mpd_sock   = NULL;
	var $connected  = FALSE;

	// MPD Status variables
	var $mpd_version    = "(unknown)";

	var $state;
	var $current_track_position;
	var $current_track_length;
	var $current_track_id;
	var $volume;
	var $repeat;
	var $random;

	var $uptime;
	var $playtime;
	var $db_last_refreshed;
	var $db_playtime;
	var $num_songs_played;
	var $playlist_count;
	var $xfade;
	var $bitrate;
	
	var $num_artists;
	var $num_albums;
	var $num_songs;
	
	var $playlist		= array();
	
	var $stats;

	// Misc Other Vars	
	var $mpd_class_version = "1.3";

	var $debugging   = FALSE;    // Set to TRUE to turn extended debugging on.
	var $errStr      = "";       // Used for maintaining information about the last error message

	var $command_queue;          // The list of commands for bulk command sending

    // =================== BEGIN OBJECT METHODS ================

	/* mpd_player() : Constructor
	 * 
	 * Builds the MPD object, connects to the server, and refreshes all local object properties.
	 */
	function mpd_player($srv,$port,$pwd = NULL, $debug = FALSE ) {
		$this->host = $srv;
		$this->port = $port;
        $this->password = $pwd;
        $this->debugging = $debug;
        
        //$file = '/var/www/html/debug.log';
        //$line = "\n\n... mpd_player (HOST: $srv, PLAYER_PORT: $port, PLAYER_PASSWORD: $pwd)";
        //file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

        global $mpd_debug;
        $mpd_debug = $debug;

		$resp = $this->Connect();
		if ( is_null($resp) ) {
            addErr( "Could not connect" );
            
            file_put_contents($file, "\n... Could not connect", FILE_APPEND | LOCK_EX);
			
            return;
		} else {
			addLog( "connected");

            //file_put_contents($file, "\n... connected: $resp", FILE_APPEND | LOCK_EX);

			list ( $this->mpd_version ) = sscanf($resp, MPD_RESPONSE_OK . " MPD %s\n");
            if ( ! is_null($pwd) ) {
                if ( is_null($this->SendCommand(MPD_CMD_PASSWORD,$pwd)) ) {
                    $this->connected = FALSE;
                    addErr("bad password");
                    return;  // bad password or command
                }
    			if ( is_null($this->RefreshInfo()) ) { // no read access -- might as well be disconnected!
                    $this->connected = FALSE;
                    addErr("Password supplied does not have read access");
                    return;
                }
            } else {
    			if ( is_null($this->RefreshInfo()) ) { // no read access -- might as well be disconnected!
                    $this->connected = FALSE;
                    addErr("Password required to access server");
                    return; 
                }
            }
		}
        $this->connected = TRUE;
	}
	
	
	/* Connect()
	 * 
	 * Connects to the MPD server. 
     * 
	 * NOTE: This is called automatically upon object instantiation; you should not need to call this directly.
	 */
	function Connect() {
		addLog( "mpd->Connect() / host: ".$this->host.", port: ".$this->port."\n" );
		$this->mpd_sock = @fsockopen($this->host,$this->port,$errNo,$errStr,10);
		if (!$this->mpd_sock) {
			addErr("Socket Error: $errStr ($errNo)");
			return NULL;
		} else {
			$counter=0;
			while(!feof($this->mpd_sock)) {
				$counter++;
				if ($counter > 10){
					addErr("no file end");
					return NULL;
				}
				$response =  fgets($this->mpd_sock,1024);
				addLog( $response );
								
				if (strncmp(MPD_RESPONSE_OK,$response,strlen(MPD_RESPONSE_OK)) == 0) {
					$this->connected = TRUE;
					return $response;
				}
				if (strncmp(MPD_RESPONSE_ERR,$response,strlen(MPD_RESPONSE_ERR)) == 0) {
					// close socket
					fclose($this->mpd_sock);
					addErr("Server responded with: $response");
					return NULL;
				}

			}
			// close socket
			fclose($this->mpd_sock);
			// Generic response
			addErr("Connection not available");
			return NULL;
		}
	}

	/* SendCommand()
	 * 
	 * Sends a generic command to the MPD server. Several command constants are pre-defined for 
	 * use (see MPD_CMD_* constant definitions above). 
	 */
	function SendCommand($cmdStr,$arg1 = "",$arg2 = "") {
		addLog("mpd->SendCommand() / cmd: ".$cmdStr.", args: ".$arg1." ".$arg2 );

		// Clear out the error String
		$this->errStr = NULL;
		$respStr = "";

		if ( ! $this->connected ) {
			addErr( "mpd->SendCommand() / Error: Not connected");
		} else {

			// Check the command compatibility:
			if ( ! $this->_checkCompatibility($cmdStr) ) {
				return NULL;
			}

			if (strlen($arg1) > 0) $cmdStr .= " \"$arg1\"";
			if (strlen($arg2) > 0) $cmdStr .= " \"$arg2\"";
			fputs($this->mpd_sock,"$cmdStr\n");
			while(!feof($this->mpd_sock)) {
				$response = fgets($this->mpd_sock,1024);
				//addLog($response);
				
				// An OK signals the end of transmission -- we'll ignore it
				if (strncmp(MPD_RESPONSE_OK,$response,strlen(MPD_RESPONSE_OK)) == 0) {
					break;
				}

				// An ERR signals the end of transmission with an error! Let's grab the single-line message.
				if (strncmp(MPD_RESPONSE_ERR,$response,strlen(MPD_RESPONSE_ERR)) == 0) {
					list ( $junk, $errTmp ) = strtok(MPD_RESPONSE_ERR . " ",$response );
					addErr( strtok($errTmp,"\n") );
					return NULL;
				}

				// Build the response string
				$respStr .= $response;
			}
			addLog("mpd->SendCommand() / response: '".$respStr."'\n");
		}
		return $respStr;
	}

	/* QueueCommand() 
	 *
	 * Queues a generic command for later sending to the MPD server. The CommandQueue can hold 
	 * as many commands as needed, and are sent all at once, in the order they are queued, using 
	 * the SendCommandQueue() method. The syntax for queueing commands is identical to SendCommand(). 
     */
	function QueueCommand($cmdStr,$arg1 = "",$arg2 = "") {
		if ( $this->debugging ) echo "mpd->QueueCommand() / cmd: ".$cmdStr.", args: ".$arg1." ".$arg2."\n";
		if ( ! $this->connected ) {
			echo "mpd->QueueCommand() / Error: Not connected\n";
			return NULL;
		} else {
			if ( strlen($this->command_queue) == 0 ) {
				$this->command_queue = MPD_CMD_START_BULK . "\n";
			}
			if (strlen($arg1) > 0) $cmdStr .= " \"$arg1\"";
			if (strlen($arg2) > 0) $cmdStr .= " \"$arg2\"";

			$this->command_queue .= $cmdStr ."\n";

			if ( $this->debugging ) echo "mpd->QueueCommand() / return\n";
		}
		return TRUE;
	}

	/* SendCommandQueue() 
	 *
	 * Sends all commands in the Command Queue to the MPD server. See also QueueCommand().
     */
	function SendCommandQueue() {
		if ( $this->debugging ) echo "mpd->SendCommandQueue()\n";
		if ( ! $this->connected ) {
			echo "mpd->SendCommandQueue() / Error: Not connected\n";
			return NULL;
		} else {
			$this->command_queue .= MPD_CMD_END_BULK . "\n";
			if ( is_null($respStr = $this->SendCommand($this->command_queue)) ) {
				return NULL;
			} else {
				$this->command_queue = NULL;
				if ( $this->debugging ) echo "mpd->SendCommandQueue() / response: '".$respStr."'\n";
			}
		}
		return $respStr;
	}

	/* AdjustVolume() 
	 *
	 * Adjusts the mixer volume on the MPD by <modifier>, which can be a positive (volume increase),
	 * or negative (volume decrease) value. 
     */
	function AdjustVolume($modifier) {
		if ( $this->debugging ) echo "mpd->AdjustVolume()\n";
		if ( ! is_numeric($modifier) ) {
			$this->errStr = "AdjustVolume() : argument 1 must be a numeric value";
			return NULL;
		}

        $this->RefreshInfo();
        $newVol = $this->volume + $modifier;
        $ret = $this->SetVolume($newVol);

		if ( $this->debugging ) echo "mpd->AdjustVolume() / return\n";
		return $ret;
	}

	/* SetVolume() 
	 *
	 * Sets the mixer volume to <newVol>, which should be between 1 - 100.
     */
	function SetVolume($newVol) {
		if ( $this->debugging ) echo "mpd->SetVolume()\n";
		if ( ! is_numeric($newVol) ) {
			$this->errStr = "SetVolume() : argument 1 must be a numeric value";
			return NULL;
		}

        // Forcibly prevent out of range errors
		if ( $newVol < 0 )   $newVol = 0;
		if ( $newVol > 100 ) $newVol = 100;

        // If we're not compatible with SETVOL, we'll try adjusting using VOLUME
        if ( $this->_checkCompatibility(MPD_CMD_SETVOL) ) {
            if ( ! is_null($ret = $this->SendCommand(MPD_CMD_SETVOL,$newVol))) $this->volume = $newVol;
        } else {
    		$this->RefreshInfo();     // Get the latest volume
    		if ( is_null($this->volume) ) {
    			return NULL;
    		} else {
    			$modifier = ( $newVol - $this->volume );
                if ( ! is_null($ret = $this->SendCommand(MPD_CMD_VOLUME,$modifier))) $this->volume = $newVol;
    		}
        }

		if ( $this->debugging ) echo "mpd->SetVolume() / return\n";
		return $ret;
	}
	

	
	/* GetDir() 
	 * 
     * Retrieves a database directory listing of the <dir> directory and places the results into
	 * a multidimensional array. If no directory is specified, the directory listing is at the 
	 * base of the MPD music path. 
	 */
	function GetDir($dir = "",$sort = "") {

		addLog( "mpd->GetDir()" );
		$resp = $this->SendCommand(MPD_CMD_LSDIR,$dir);
		$listArray = $this->_parseFileListResponse($resp);

		if ($listArray==null){
			return null;
		}
		
		// we have 3 differnt items: directory, playlist and file
		// we have to sort them individually and separate
		// playlist and directory by name
		// file by $sort

		// 1st: subarrays
		$array_directory 	= $listArray['directories'];		
		$array_playlist 	= $listArray['playlists'];		
		$array_file 		= $listArray['files'];
		
		// 2nd: sort them
		natcasesort($array_directory);
		natcasesort($array_playlist);
		usort($array_file,"msort"); 
		// 3rd: rebuild
		$array_return= array( 
						"directories"=> $array_directory,
						"playlists"=> $array_playlist,
						"files"=> $array_file
								);
/*
									
		foreach ($array_directory as $value) {
			$array_return[]["directory"] = $value; 
		}
		foreach ($array_playlist as $value) {
			$array_return[]["playlist"] = $value; 
		}
		$array_return = array_merge($array_return,$array_file);
*/		
		addLog( "mpd->GetDir() / return ".print_r($array_return,true));
		return $array_return;
	}

	/* GetDirTest() (Unoffical add) -- Returns readable dir contents
	 *
     * Retrieves a database directory listing of the <dir> directory and places the results into
	 * a multidimensional array. If no directory is specified, the directory listing is at the
	 * base of the MPD music path.
	 */
	function GetDirTest($dir = "") {
		if ( $this->debugging ) echo "mpd->GetDir()\n";
		$resp = $this->SendCommand(MPD_CMD_LSDIR,$dir);

         
		#$dirlist = $this->_parseFileListResponse($resp);
        $dirlist = $this->_parseFileListResponseHumanReadable($resp);

		if ( $this->debugging ) echo "mpd->GetDir() / return ".print_r($dirlist)."\n";
		return $dirlist;
	}
	
	/* PLAdd() 
	 * 
     * Adds each track listed in a single-dimensional <trackArray>, which contains filenames 
	 * of tracks to add, to the end of the playlist. This is used to add many, many tracks to 
	 * the playlist in one swoop.
	 */
	function PLAddBulk($trackArray) {
		if ( $this->debugging ) echo "mpd->PLAddBulk()\n";
		$num_files = count($trackArray);
		for ( $i = 0; $i < $num_files; $i++ ) {
			$this->QueueCommand(MPD_CMD_PLADD,$trackArray[$i]);
		}
		$resp = $this->SendCommandQueue();
		$this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLAddBulk() / return\n";
		return $resp;
	}

	/* PLAdd() 
	 * 
	 * Adds the file <file> to the end of the playlist. <file> must be a track in the MPD database. 
	 */
	function PLAdd($fileName) {
		if ( $this->debugging ) echo "mpd->PLAdd()\n";
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLADD,$fileName))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLAdd() / return\n";
		return $resp;
	}

	/* PLMoveTrack() 
	 * 
	 * Moves track number <origPos> to position <newPos> in the playlist. This is used to reorder 
	 * the songs in the playlist.
	 */
	function PLMoveTrack($origPos, $newPos) {
		if ( $this->debugging ) echo "mpd->PLMoveTrack()\n";
		if ( ! is_numeric($origPos) ) {
			$this->errStr = "PLMoveTrack(): argument 1 must be numeric";
			return NULL;
		} 
		if ( $origPos < 0 or $origPos > $this->playlist_count ) {
			$this->errStr = "PLMoveTrack(): argument 1 out of range";
			return NULL;
		}
		if ( $newPos < 0 ) $newPos = 0;
		if ( $newPos > $this->playlist_count ) $newPos = $this->playlist_count;
		
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLMOVETRACK,$origPos,$newPos))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLMoveTrack() / return\n";
		return $resp;
	}

	/* PLShuffle() 
	 * 
	 * Randomly reorders the songs in the playlist.
	 */
	function PLShuffle() {
		if ( $this->debugging ) echo "mpd->PLShuffle()\n";
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLSHUFFLE))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLShuffle() / return\n";
		return $resp;
	}

	/* PLLoad() 
	 * 
	 * Retrieves the playlist from <file>.m3u and loads it into the current playlist. 
	 */
	function PLLoad($file) {
		if ( $this->debugging ) echo "mpd->PLLoad()\n";
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLLOAD,$file))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLLoad() / return\n";
		return $resp;
	}

	/* PLSave() 
	 * 
	 * Saves the playlist to <file>.m3u for later retrieval. The file is saved in the MPD playlist
	 * directory.
	 */
	function PLSave($file) {
		if ( $this->debugging ) echo "mpd->PLSave()\n";
		$resp = $this->SendCommand(MPD_CMD_PLSAVE,$file);
		if ( $this->debugging ) echo "mpd->PLSave() / return\n";
		return $resp;
	}

	/* PLClear() 
	 * 
	 * Empties the playlist.
	 */
	function PLClear() {
		if ( $this->debugging ) echo "mpd->PLClear()\n";
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLCLEAR))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLClear() / return\n";
		return $resp;
	}

	/* PLRemove() 
	 * 
	 * Removes track <id> from the playlist.
	 */
	function PLRemove($id) {
		if ( $this->debugging ) echo "mpd->PLRemove()\n";
		if ( ! is_numeric($id) ) {
			$this->errStr = "PLRemove() : argument 1 must be a numeric value";
			return NULL;
		}
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLREMOVE,$id))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLRemove() / return\n";
		return $resp;
	}
	
	/* PLRemoveId() 
	 * 
	 * Removes track <id> from the playlist.
	 */
	function PLRemoveId($id) {
		if ( $this->debugging ) echo "mpd->PLRemoveId()\n";
		if ( ! is_numeric($id) ) {
			$this->errStr = "PLRemoveId() : argument 1 must be a numeric value";
			return NULL;
		}
		if ( ! is_null($resp = $this->SendCommand(MPD_CMD_PLREMOVEID,$id))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PLRemoveId() / return\n";
		return $resp;
	}

	/* SetSingle() 
	 * 
	 * Enables 'single' mode -- tells MPD to play only the current track. The <singVal> parameter 
	 * is either 1 (on) or 0 (off).
	 */
	function SetSingle($singVal) {
		if ( $this->debugging ) echo "mpd->SetSingle()\n";
		$sing = $this->SendCommand(MPD_CMD_SINGLE,$singVal);
		$this->single = $singVal;
		if ( $this->debugging ) echo "mpd->SetSingle() / return\n";
		return $sing;
	}
	
	/* SetRepeat() 
	 * 
	 * Enables 'loop' mode -- tells MPD continually loop the playlist. The <repVal> parameter 
	 * is either 1 (on) or 0 (off).
	 */
	function SetRepeat($repVal) {
		if ( $this->debugging ) echo "mpd->SetRepeat()\n";
		$rpt = $this->SendCommand(MPD_CMD_REPEAT,$repVal);
		$this->repeat = $repVal;
		if ( $this->debugging ) echo "mpd->SetRepeat() / return\n";
		return $rpt;
	}

	/* SetRandom() 
	 * 
	 * Enables 'randomize' mode -- tells MPD to play songs in the playlist in random order. The
	 * <rndVal> parameter is either 1 (on) or 0 (off).
	 */
	function SetRandom($rndVal) {
		if ( $this->debugging ) echo "mpd->SetRandom()\n";
		$resp = $this->SendCommand(MPD_CMD_RANDOM,$rndVal);
		$this->random = $rndVal;
		if ( $this->debugging ) echo "mpd->SetRandom() / return\n";
		return $resp;
	}

	/* Shutdown() 
	 * 
	 * Shuts down the MPD server (aka sends the KILL command). This closes the current connection, 
	 * and prevents future communication with the server. 
	 */
	function Shutdown() {
		if ( $this->debugging ) echo "mpd->Shutdown()\n";
		$resp = $this->SendCommand(MPD_CMD_SHUTDOWN);

		$this->connected = FALSE;
		unset($this->mpd_version);
		unset($this->errStr);
		unset($this->mpd_sock);

		if ( $this->debugging ) echo "mpd->Shutdown() / return\n";
		return $resp;
	}

	/* DBRefresh() 
	 * 
	 * Tells MPD to rescan the music directory for new tracks, and to refresh the Database. Tracks 
	 * cannot be played unless they are in the MPD database.
	 */
	function DBRefresh() {
		if ( $this->debugging ) echo "mpd->DBRefresh()\n";
		$resp = $this->SendCommand(MPD_CMD_REFRESH);
		
		// Update local variables
		$this->RefreshInfo();

		if ( $this->debugging ) echo "mpd->DBRefresh() / return\n";
		return $resp;
	}

	/* Play() 
	 * 
	 * Begins playing the songs in the MPD playlist. 
	 */
	function Play() {
		if ( $this->debugging ) echo "mpd->Play()\n";
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_PLAY) )) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->Play() / return\n";
		return $rpt;
	}
	
	/* PlayId() 
	 * 
	 * Begins playing the song by id in the MPD playlist. 
	 */
	function PlayId($id) {
		if ( $this->debugging ) echo "mpd->PlayId()\n";
		if ( ! is_numeric($id) ) {
			$this->errStr = "PlayId() : argument 1 must be a numeric value";
			return NULL;
		}
		if ( ! is_null($pld = $this->SendCommand(MPD_CMD_PLAYID,$id))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->PlayId() / return\n";
		return $pld;
	}

	/* Stop() 
	 * 
	 * Stops playing the MPD. 
	 */
	function Stop() {
		if ( $this->debugging ) echo "mpd->Stop()\n";
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_STOP) )) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->Stop() / return\n";
		return $rpt;
	}

	/* Pause() 
	 * 
	 * Toggles pausing on the MPD. Calling it once will pause the player, calling it again
	 * will unpause. 
	 */
	function Pause() {
		if ( $this->debugging ) echo "mpd->Pause()\n";
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_PAUSE) )) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->Pause() / return\n";
		return $rpt;
	}
	
	/* SeekTo() 
	 * 
	 * Skips directly to the <idx> song in the MPD playlist. 
	 */
	function SkipTo($idx) { 
		if ( $this->debugging ) echo "mpd->SkipTo()\n";
		if ( ! is_numeric($idx) ) {
			$this->errStr = "SkipTo() : argument 1 must be a numeric value";
			return NULL;
		}
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_PLAY,$idx))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->SkipTo() / return\n";
		return $idx;
	}

	/* SeekTo() 
	 * 
	 * Skips directly to a given position within a track in the MPD playlist. The <pos> argument,
	 * given in seconds, is the track position to locate. The <track> argument, if supplied is
	 * the track number in the playlist. If <track> is not specified, the current track is assumed.
	 */
	function SeekTo($pos, $track = -1) { 
		if ( $this->debugging ) echo "mpd->SeekTo()\n";
		if ( ! is_numeric($pos) ) {
			$this->errStr = "SeekTo() : argument 1 must be a numeric value";
			return NULL;
		}
		if ( ! is_numeric($track) ) {
			$this->errStr = "SeekTo() : argument 2 must be a numeric value";
			return NULL;
		}
		if ( $track == -1 ) { 
			$track = $this->current_track_id;
		} 
		
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_SEEK,$track,$pos))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->SeekTo() / return\n";
		return $pos;
	}

	/* Next() 
	 * 
	 * Skips to the next song in the MPD playlist. If not playing, returns an error. 
	 */
	function Next() {
		if ( $this->debugging ) echo "mpd->Next()\n";
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_NEXT))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->Next() / return\n";
		return $rpt;
	}

	/* Previous() 
	 * 
	 * Skips to the previous song in the MPD playlist. If not playing, returns an error. 
	 */
	function Previous() {
		if ( $this->debugging ) echo "mpd->Previous()\n";
		if ( ! is_null($rpt = $this->SendCommand(MPD_CMD_PREV))) $this->RefreshInfo();
		if ( $this->debugging ) echo "mpd->Previous() / return\n";
		return $rpt;
	}
	
	/* Search() 
	 * 
     * Searches the MPD database. The search <type> should be one of the following: 
     *        MPD_SEARCH_ARTIST, MPD_SEARCH_TITLE, MPD_SEARCH_ALBUM
     * The search <string> is a case-insensitive locator string. Anything that contains 
	 * <string> will be returned in the results. 
	 */
	function Search($type,$string) {
		addLog("mpd->Search()");
		if ( $type != MPD_SEARCH_ARTIST and
	         $type != MPD_SEARCH_ALBUM and
	         $type != MPD_SEARCH_ANY and
			 $type != MPD_SEARCH_TITLE ) {
			addErr( "mpd->Search(): invalid search type" );
			return NULL;
		} else {
			if ( is_null($resp = $this->SendCommand(MPD_CMD_SEARCH,$type,$string)))	return NULL;
			$searchlist = $this->_parseFileListResponse($resp);
		}
		addLog( "mpd->Search() / return ".print_r($searchlist,true) );
		return $searchlist;
	}

	/* Find() 
	 * 
	 * Find() looks for exact matches in the MPD database. The find <type> should be one of 
	 * the following: 
     *         MPD_SEARCH_ARTIST, MPD_SEARCH_TITLE, MPD_SEARCH_ALBUM
     * The find <string> is a case-insensitive locator string. Anything that exactly matches 
	 * <string> will be returned in the results. 
	 */
	function Find($type,$string) {
		if ( $this->debugging ) echo "mpd->Find()\n";
		if ( $type != MPD_SEARCH_ARTIST and
	         $type != MPD_SEARCH_ALBUM and
			 $type != MPD_SEARCH_TITLE ) {
			$this->errStr = "mpd->Find(): invalid find type";
			return NULL;
		} else {
			if ( is_null($resp = $this->SendCommand(MPD_CMD_FIND,$type,$string)))	return NULL;
			$searchlist = $this->_parseFileListResponse($resp);
		}
		if ( $this->debugging ) echo "mpd->Find() / return ".print_r($searchlist)."\n";
		return $searchlist;
	}

	/* Disconnect() 
	 * 
	 * Closes the connection to the MPD server.
	 */
	function Disconnect() {
		if ( $this->debugging ) echo "mpd->Disconnect()\n";
		fclose($this->mpd_sock);

		$this->connected = FALSE;
		unset($this->mpd_version);
		unset($this->errStr);
		unset($this->mpd_sock);
	}

	/* GetArtists() 
	 * 
	 * Returns the list of artists in the database in an associative array.
	*/
	function GetArtists() {
		if ( $this->debugging ) echo "mpd->GetArtists()\n";
		if ( is_null($resp = $this->SendCommand(MPD_CMD_TABLE, MPD_TBL_ARTIST)))	return NULL;
        $arArray = array();
        
        $arLine = strtok($resp,"\n");
        $arName = "";
        $arCounter = -1;
        while ( $arLine ) {
            list ( $element, $value ) = explode(": ",$arLine);
            if ( $element == "Artist" ) {
            	$arCounter++;
            	$arName = $value;
            	$arArray[$arCounter] = $arName;
            }

            $arLine = strtok("\n");
        }
		if ( $this->debugging ) echo "mpd->GetArtists()\n";
        return $arArray;
    }

    /* GetAlbums() 
	 * 
	 * Returns the list of albums in the database in an associative array. Optional parameter
     * is an artist Name which will list all albums by a particular artist.
	*/
	function GetAlbums( $ar = NULL) {
		if ( $this->debugging ) echo "mpd->GetAlbums()\n";
		if ( is_null($resp = $this->SendCommand(MPD_CMD_TABLE, MPD_TBL_ALBUM, $ar )))	return NULL;
        $alArray = array();

        $alLine = strtok($resp,"\n");
        $alName = "";
        $alCounter = -1;
        while ( $alLine ) {
            list ( $element, $value ) = explode(": ",$alLine);
            if ( $element == "Album" ) {
            	$alCounter++;
            	$alName = $value;
            	$alArray[$alCounter] = $alName;
            }

            $alLine = strtok("\n");
        }
		if ( $this->debugging ) echo "mpd->GetAlbums()\n";
        return $alArray;
    }

	//*******************************************************************************//
	//***************************** INTERNAL FUNCTIONS ******************************//
	//*******************************************************************************//

    /* _computeVersionValue()
     *
     * Computes a compatibility value from a version string
     *
     */
    private function _computeVersionValue($verStr) {
		list ($ver_maj, $ver_min, $ver_rel ) = explode(".",$verStr);
		return ( 100 * $ver_maj ) + ( 10 * $ver_min ) + ( $ver_rel );
    }

	/* _checkCompatibility() 
	 * 
	 * Check MPD command compatibility against our internal table. If there is no version 
	 * listed in the table, allow it by default.
	*/
	private function _checkCompatibility($cmd) {
        return TRUE;
        
        // Check minimum compatibility
		if (isset($this->COMPATIBILITY_MIN_TBL[$cmd])){
			$req_ver_low = $this->COMPATIBILITY_MIN_TBL[$cmd];
		} else {
			$req_ver_low = "0.9.1";
		}
		// check max compatibility
		if (isset($this->COMPATIBILITY_MAX_TBL[$cmd])){
			$req_ver_hi = $this->COMPATIBILITY_MAX_TBL[$cmd];	
		} else {
			$req_ver_hi = "0.20.0";
		}

		$mpd_ver = $this->_computeVersionValue($this->mpd_version);

		if ( $req_ver_low ) {
			$req_ver = $this->_computeVersionValue($req_ver_low);

			if ( $mpd_ver < $req_ver ) {
				addErr("Command '$cmd' is not compatible with this version of MPD, version ".$req_ver_low." required");
				return FALSE;
			}
		}

        // Check maximum compatibility -- this will check for deprecations
		if ( $req_ver_hi ) {
            $req_ver = $this->_computeVersionValue($req_ver_hi);

			if ( $mpd_ver > $req_ver ) {
				addErr("Command '$cmd' has been deprecated in this version of MPD.");
				return FALSE;
			}
		}

		return TRUE;
	}

	/*
	 * checks the file entry and complete it if necesarry
	 * checked fields are 'Artist', 'Genre' and 'Title' 
	 *
	 */
	private function _validateFile( $fileItem ){

		$filename = $fileItem['file'];

		if (!isset($fileItem['Artist'])){ $fileItem['Artist']=null; }
		if (!isset($fileItem['Genre'])){ $fileItem['Genre']=null; }
		
		// special conversion for streams 				
		if (stripos($filename, 'http' )!==false){
			if (!isset($fileItem['Title'])) $title = ''; else $title=$fileItem['Title'];
			if (!isset($fileItem['Name'])) $name = ''; else $name=$fileItem['Name'];
			if (!isset($fileItem['Artist'])) $artist = ''; else $artist=$fileItem['Artist'];
			
			if (strlen($title.$name.$artist)==0){
				$fileItem['Title'] = $filename;
			} else {
				$fileItem['Title'] = 'stream://'.$title.' '.$name.' '.$artist;	
			}
			
		}
				 				
		if (!isset($fileItem['Title'])){ 
			$file_parts = explode('/', $filename);
			$fileItem['Title'] = $filename;
		 }
				
		return $fileItem;		
	}
	
	/*
	 * take the response of mpd and split it up into
	 * items of types 'file', 'directory' and 'playlist' 
	 * 
	 */
	private function _extractItems( $resp ){
	
		if ( $resp == null ) {
			addLog('empty file list');
			return NULL;
		} 
		
		// strip unwanted chars
		$resp = trim($resp);
		// split up into lines 
		$lineList = explode("\n", $resp );
		
		$array = array();
		
		$item=null;
		foreach ($lineList as $line ){
			list ( $element, $value ) = explode(": ",$line);

			
			// if one of the key words come up, store the item
			if (($element == "directory") or ($element=="playlist") or ($element=="file")){
				if ($item){
					$array[] = $item;
				}
				$item = array();
			}
			$item[$element] = $value;								
		}
		// check if there is a last item to store
		if (sizeof($item)>0){
			$array[] = $item;
		}
		
		return $array;			
	}
	
	
	/* _parseFileListResponse() 
	 * 
	 * Builds a multidimensional array with MPD response lists.
     *
	 * NOTE: This function is used internally within the class. It should not be used.
	 */
	private function _parseFileListResponse($resp) {
		
		$valuesArray = $this->_extractItems( $resp );
		
		if ($valuesArray == null ){
			return null;
		}
		
		
		//1. create empty arrays
		$directoriesArray = array();
		$filesArray = array();
		$playlistsArray = array();
		

		//2. sort the items 		
		foreach ( $valuesArray as $item ) {
			
			if (isset($item['file'])){
				$filesArray[] = $this->_validateFile($item);
			} else if (isset($item['directory'])){
				$directoriesArray[] = $item['directory'];
			} else if (isset($item['playlist'])){
				$playlistsArray[] = $item['playlist'];	
			} else {
				addErr('should not enter this');
			}
		} 
		
		//3. create a combined list of items		
		$returnArray = array(
							"directories"=>$directoriesArray,
							"playlists"=>$playlistsArray,
							"files"=>$filesArray
						);
		
		addLog( print_r($valuesArray,true) );
				
		return $returnArray;

	}
	
  

	
	/* RefreshInfo() 
	 * 
	 * Updates all class properties with the values from the MPD server.
     *
	 * NOTE: This function is automatically called upon Connect() as of v1.1.
	 */
	function RefreshInfo() {
        // Get the Server Statistics
		$statStr = $this->SendCommand(MPD_CMD_STATISTICS);
		if ( !$statStr ) {
			return NULL;
		} else {
			$stats = array();

			$statStr=trim($statStr);
			$statLine = explode( "\n", $statStr );
			foreach ( $statLine as $line ) {
				list ( $element, $value ) = explode(": ",$line);
				$stats[$element] = $value;
			} 
		}

        // Get the Server Status
		$statusStr = $this->SendCommand(MPD_CMD_STATUS);
		if ( ! $statusStr ) {
			return NULL;
		} else {
			$status = array();
			$statusStr=trim($statusStr);
			$statusLine = explode("\n", $statusStr );
			foreach ( $statusLine as $line ) {
				list ( $element, $value ) = explode(": ",$line);
				$status[$element] = $value;
			}
		}

        // Get the Playlist
		$plStr = $this->SendCommand(MPD_CMD_PLLIST);
   		$array = $this->_parseFileListResponse($plStr);
   		$playlist = $array['files'];
		if (is_array($playlist)) {
			$this->playlist_count = count($playlist);
			$this->playlist = array();
			if (sizeof($playlist)>0){
				foreach ($playlist as $item ){
					$this->playlist[$item['Pos']]=$item;
				}
			}
		} else {
			$this->playlist_count = 0;
			$this->playlist = array();
		}


        // Set Misc Other Variables
		$this->state = $status['state'];
		if ( ($this->state == MPD_STATE_PLAYING) || ($this->state == MPD_STATE_PAUSED) ) {
			$this->current_track_id = $status['song'];
			list ($this->current_track_position, $this->current_track_length ) = explode(":",$status['time']);
		} else {
			$this->current_track_id = -1;
			$this->current_track_position = -1;
			$this->current_track_length = -1;
		}

		$this->repeat = $status['repeat'];
		$this->random = $status['random'];

		$this->db_last_refreshed = $stats['db_update'];

		$this->volume = $status['volume'];
		$this->uptime = $stats['uptime'];
		$this->db_playtime = $stats['db_playtime'];
		$this->playtime = $stats['playtime'];
		$this->num_songs_played = $stats['songs'];
		$this->num_artists = $stats['artists'];
		$this->num_songs = $stats['songs'];
		$this->num_albums = $stats['albums'];
		if(isset($status['xfade'])) $this->xfade = $status['xfade'];
        else $this->xfade = FALSE;
		if(isset($status['bitrate'])) $this->bitrate = $status['bitrate'];
        else $this->bitrate = FALSE;
        		
		return TRUE;
	}

    /* ------------------ DEPRECATED METHODS -------------------*/
	/* GetStatistics() 
	 * 
	 * Retrieves the 'statistics' variables from the server and tosses them into an array.
     *
	 * NOTE: This function really should not be used. Instead, use $this->[variable]. The function
	 *   will most likely be deprecated in future releases.
	 */
	function GetStatistics() {
		if ( $this->debugging ) echo "mpd->GetStatistics()\n";
		$stats = $this->SendCommand(MPD_CMD_STATISTICS);
		if ( !$stats ) {
			return NULL;
		} else {
			$statsArray = array();
			$statsLine = strtok($stats,"\n");
			while ( $statsLine ) {
				list ( $element, $value ) = explode(": ",$statsLine);
				$statsArray[$element] = $value;
				$statsLine = strtok("\n");
			} 
		}
		if ( $this->debugging ) echo "mpd->GetStatistics() / return: " . print_r($statsArray) ."\n";
		return $statsArray;
	}

	/* GetStatus() 
	 * 
	 * Retrieves the 'status' variables from the server and tosses them into an array.
     *
	 * NOTE: This function really should not be used. Instead, use $this->[variable]. The function
	 *   will most likely be deprecated in future releases.
	 */
	function GetStatus() {
		if ( $this->debugging ) echo "mpd->GetStatus()\n";
		$status = $this->SendCommand(MPD_CMD_STATUS);
		if ( ! $status ) {
			return NULL;
		} else {
			$statusArray = array();
			$statusLine = strtok($status,"\n");
			while ( $statusLine ) {
				list ( $element, $value ) = explode(": ",$statusLine);
				$statusArray[$element] = $value;
				$statusLine = strtok("\n");
			}
		}
		if ( $this->debugging ) echo "mpd->GetStatus() / return: " . print_r($statusArray) ."\n";
		return $statusArray;
	}

	/* GetVolume() 
	 * 
	 * Retrieves the mixer volume from the server.
     *
	 * NOTE: This function really should not be used. Instead, use $this->volume. The function
	 *   will most likely be deprecated in future releases.
	 */
	function GetVolume() {
		if ( $this->debugging ) echo "mpd->GetVolume()\n";
		$volLine = $this->SendCommand(MPD_CMD_STATUS);
		if ( ! $volLine ) {
			return NULL;
		} else {
			list ($vol) = sscanf($volLine,"volume: %d");
		}
		if ( $this->debugging ) echo "mpd->GetVolume() / return: $vol\n";
		return $vol;
	}

	/* GetPlaylist() 
	 * 
	 * Retrieves the playlist from the server and tosses it into a multidimensional array.
     *
	 * NOTE: This function really should not be used. Instead, use $this->playlist. The function
	 *   will most likely be deprecated in future releases.
	 */
	function GetPlaylist() {
		if ( $this->debugging ) echo "mpd->GetPlaylist()\n";
		$resp = $this->SendCommand(MPD_CMD_PLLIST);
		$playlist = $this->_parseFileListResponse($resp);
		if ( $this->debugging ) echo "mpd->GetPlaylist() / return ".print_r($playlist)."\n";
		return $playlist;
	}

    /* ----------------- Command compatibility tables --------------------- */
	var $COMPATIBILITY_MIN_TBL = array(
		MPD_CMD_SEEK 		=> "0.9.1"	,
		MPD_CMD_PLMOVE  	=> "0.9.1"	,
		MPD_CMD_RANDOM  	=> "0.9.1"	,
		MPD_CMD_PLSWAPTRACK	=> "0.9.1"	,
		MPD_CMD_PLMOVETRACK	=> "0.9.1"  ,
		MPD_CMD_PASSWORD	=> "0.10.0" ,
        MPD_CMD_SETVOL      => "0.10.0"	
        
	);

    var $COMPATIBILITY_MAX_TBL = array(
        MPD_CMD_VOLUME      => "0.10.0"
    );

}   // ---------------------------- end of class ------------------------------

function msort($a,$b) {
	global $sort_array,$filenames_only;
	$i=0;
	$ret = 0;
	while($filenames_only!="yes" && $i<4 && $ret==0) {
		if(!isset($a[$sort_array[$i]])) {
			if(isset($b[$sort_array[$i]])) {
				$ret = -1;
			}
		}
		else if(!isset($b[$sort_array[$i]])) {
			$ret = 1;
		}
		else if(strcmp($sort_array[$i],"Track")==0) {
			$ret = strnatcmp($a[$sort_array[$i]],$b[$sort_array[$i]]);
		}
		else {
			$ret = strcasecmp($a[$sort_array[$i]],$b[$sort_array[$i]]);
		}
		$i++;
	}
	if($ret==0)
		$ret = strcasecmp($a["file"],$b["file"]);
	return $ret;
}

function picksort($pick) {
	global $sort_array;
	if(0==strcmp($pick,$sort_array[0])) {
		return "$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[3]";
	}
	else if(0==strcmp($pick,$sort_array[1])) {
		return "$pick,$sort_array[0],$sort_array[2],$sort_array[3]";
	}
	else if(0==strcmp($pick,$sort_array[2])) {
		return "$pick,$sort_array[0],$sort_array[1],$sort_array[3]";
	}
	else if(0==strcmp($pick,$sort_array[3])) {
		return "$pick,$sort_array[0],$sort_array[1],$sort_array[2]";
	}
}

?>
