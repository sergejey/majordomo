<?php

/*
 * MPD command reference at http://www.musicpd.org/doc/protocol/index.html
 */

class mpd_player
{
    
    function __construct($srv, $port, $pwd = NULL, $debug = FALSE)
    {
        $this->host      = $srv;
        $this->port      = $port;
        $this->password  = $pwd;
        $this->debugging = $debug;
        
        if (!$this->host) {
            $this->connected = FALSE;
        }
        
        $this->mpd_sock = fsockopen($this->host, $this->port, $errNo, $errStr, 10);
        if (!$this->mpd_sock) {
            $this->connected = FALSE;
        } else {
            $counter = 0;
            while (!feof($this->mpd_sock)) {
                $counter++;
                if ($counter > 10) {
                    $this->connected = FALSE;
                    break;
                }
                $response = fgets($this->mpd_sock, 1024);
                if (strncmp("OK", $response, strlen("OK")) == 0) {
                    $this->connected = TRUE;
                    break;
                }
                if (strncmp("ACK", $response, strlen("ACK")) == 0) {
                    $this->connected = FALSE;
                    break;
                }
            }
        }
        if (!$this->connected) {
            // close socket
            fclose($this->mpd_sock);
        }
        if ($this->password) {
            fputs($this->mpd_sock, 'password "' . $this->password . '"' . "\n");
            while (!feof($this->mpd_sock)) {
                $response = fgets($this->mpd_sock, 1024);
                if (strncmp("OK", $response, strlen("OK")) == 0) {
                    $this->connected = TRUE;
                    break;
                }
                if (strncmp("ACK", $response, strlen("ACK")) == 0) {
                    $this->connected = FALSE;
                    // close socket
                    fclose($this->mpd_sock);
                    break;
                }
            }
        }
    }
    
    function Connect()
    {
        $this->mpd_sock = @fsockopen($this->host, $this->port, $errNo, $errStr, 10);
        if (!$this->mpd_sock) {
            return false;
        } else {
            $counter = 0;
            while (!feof($this->mpd_sock)) {
                $counter++;
                if ($counter > 10) {
                    return false;
                }
                $response = fgets($this->mpd_sock, 1024);
                if (strncmp("OK", $response, strlen("OK")) == 0) {
                    $this->connected = TRUE;
                    return $response;
                }
                if (strncmp("ACK", $response, strlen("ACK")) == 0) {
                    list($junk, $errTmp) = strtok("ACK" . " ", $response);
                    fclose($this->mpd_sock);
                    $this->connected = FALSE;
                    return $response;
                }
                
            }
            // close socket
            fclose($this->mpd_sock);
            return false;
        }
    }
    
    /* Disconnect() 
     * 
     * Closes the connection to the MPD server.
     */
    function Disconnect()
    {
        fclose($this->mpd_sock);
        $this->connected = FALSE;
        unset($this->mpd_sock);
        return 'Disconnected';
    }
    
    /* SendCommand()
     * 
     * Sends a generic command to the MPD server. Several command constants are pre-defined for 
     * use (see MPD_CMD_* constant definitions above). 
     */
    function SendCommand($cmdStr, $arg1 = "", $arg2 = "")
    {
        $respStr = "";
        if ($this->connected) {
            if (strlen($arg1) > 0)
                $cmdStr .= ' "' . $arg1 . '"';
            if (strlen($arg2) > 0)
                $cmdStr .= ' "' . $arg2 . '"';
            fputs($this->mpd_sock, $cmdStr . "\n");
            while (!feof($this->mpd_sock)) {
                $response = fgets($this->mpd_sock, 1024);
                // Build the response string
                $respStr .= $response;
                // An OK signals the end of transmission -- we'll ignore it
                if (strncmp("OK", $response, strlen("OK")) == 0) {
                    break;
                }
                // An ERR signals the end of transmission with an error! Let's grab the single-line message.
                if (strncmp("ACK", $response, strlen("ACK")) == 0) {
                    list($junk, $errTmp) = strtok("ACK" . " ", $response);
                    return $response;
                }
            }
        }
        return $respStr;
    }
    
    /* GetStatus() 
     * 
     * Retrieves the 'status' variables from the server and tosses them into an array.
     *
     * NOTE: This function really should not be used. Instead, use $this->[variable]. The function
     *   will most likely be deprecated in future releases.
     */
    function GetStatus()
    {
        $status = $this->SendCommand("status");
        if (!$status) {
            return NULL;
        } else {
            $statusArray = array();
            $statusLine  = strtok($status, "\n");
            while ($statusLine) {
                list($element, $value) = explode(": ", $statusLine);
                $statusArray[$element] = $value;
                $statusLine            = strtok("\n");
            }
        }
        return $statusArray;
    }
    
    function GetCommand()
    {
        $status = $this->SendCommand("commands");
        if (!$status) {
            return NULL;
        } else {
            $statusArray = array();
            $statusLine  = strtok($status, "\n");
            $i           = 0;
            while ($statusLine) {
                list($element, $value) = explode(": ", $statusLine);
                $i++;
                $statusArray[$i] = $value;
                $statusLine      = strtok("\n");
            }
        }
        return $statusArray;
    }
    
    
    
    function SetVolume($newVol)
    {
        // Forcibly prevent out of range errors
        if ($newVol < 0)
            $newVol = 0;
        if ($newVol > 100)
            $newVol = 100;
        $ret = $this->SendCommand("setvol", $newVol);
        return $ret;
    }
    
    
    function Play()
    {
        $rpt = $this->SendCommand("play");
        return $rpt;
    }
    
    function Stop()
    {
        $rpt = $this->SendCommand("stop");
        return $rpt;
    }
    
    function Previous()
    {
        $rpt = $this->SendCommand("previous");
        return $rpt;
    }
    
    function Next()
    {
        $rpt = $this->SendCommand("next");
        return $rpt;
    }
    
    // dopisat proverku na pausu tekushuyu 
    function Pause()
    {
        $rpt = $this->SendCommand("pause");
        return $rpt;
    }
    
    function PLClear()
    {
        $rpt = $this->SendCommand("clear");
        return $rpt;
    }
    
    function PLAddFile($url)
    {
        $rpt = $this->SendCommand("add", $url);
        return $rpt;
    }
    function PLAddFileWithPosition($url, $position = 0)
    {
        $rpt = $this->SendCommand("addid", $url, $position);
        return $rpt;
    }
    function PLSeek($position = 0, $time = 0)
    {
        $rpt = $this->SendCommand("seek", $position, $time);
        return $rpt;
    }
    function Ping()
    {
        $rpt = $this->SendCommand("ping");
        return $rpt;
    }
    
    function SetRepeat($in)
    {
        $rpt = $this->SendCommand("repeat", $in);
        return $rpt;
    }
    
    function SetRandom($in)
    {
        $rpt = $this->SendCommand("random", $in);
        return $rpt;
    }
    function SetCrossfade($in)
    {
        $rpt = $this->SendCommand("crossfade", $in);
        return $rpt;
    }
    function GetPlaylistinfo()
    {
        $rpt = $this->SendCommand("playlistinfo");
        if (!$rpt) {
            return NULL;
        } else {
            $statusArray = array();
            $statusLine  = strtok($rpt, "\n");
            $i           = 0;
            while ($statusLine) {
                list($element, $value) = explode(": ", $statusLine);
                if ($element == 'OK') {
                    break;
                }
                if ($element == 'file') {
                    $i++;
    				$out = parse_url($value);
    				$scheme   = isset($out['scheme']) ? $out['scheme'] . '://' : '';
                    $host     = isset($out['host']) ? $out['host'] : '';
                    $port     = isset($out['port']) ? ':' . $out['port'] : '';
                    $path     = isset($out['path']) ? $out['path'] : '';
                    $value = $scheme.$host.$port.$path;
                }
                $statusArray[$i][$element] = $value;
                $statusLine                = strtok("\n");
            }
        }
        return $statusArray;
    }
}

?>
