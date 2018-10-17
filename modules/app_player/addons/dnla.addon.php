<?php

/*
    Addon dnla for app_player
*/

class dnla extends app_player_addon {
    
    // Private properties
    private $curl;
    private $address;
    
    // Constructor
    function __construct($terminal) {
        $this->title = 'DNLA media player';
        $this->description = 'Проигрывание видео - аудио ';
        $this->description .= 'на всех устройства поддерживающих такой протокол. ';
        $this->terminal = $terminal;
        $this->reset_properties();
        // MediaRenderer
        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/MediaRenderer.php');
        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/MediaRendererVolume.php');
        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/Core.php');
        }
    

    // Play
    function play($input) {
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRES']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->play($input);
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Play files';
	    } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    // Stop
    function stop() {
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRES']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->stop();
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Stop play';
	    } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    // Pause
    function pause() {
        $this->reset_properties();
        $core= new Core();
        $result = $core->search($this->terminal['HOST']);
        foreach($result as $device){
             $current_dev = $device['location'];
            }
        //echo ($current_dev);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->pause();
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Pause enabled';
	    } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }
    
    // Next
    function next() {
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRES']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->next();
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Next file changed';
	    } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }
    
    // Previous
    function previous() {
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRES']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->previous();
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Previous file changed';
	    } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    
    // Set volume
    function set_volume($level) {
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRES']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remotevolume = new MediaRendererVolume($current_dev);
        //DebMes($level);
        $answer = $remotevolume->SetVolume($level);
        //DebMes($this->success);
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Volume changed';
	    } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }  

      
}

?>
