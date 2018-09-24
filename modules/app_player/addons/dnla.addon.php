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
        $core= new Core();
        //Debmes ("IP terminal - ".$this->terminal['HOST']);
        //Debmes ("URL - ".$input);
        $result = $core->search($this->terminal['HOST']);
        foreach($result as $device){
              $current_dev = $device['location'];
              }
        $current_dev = str_ireplace("Location:", "", $current_dev);
        //DebMes ("XML adress - ".$current_dev);
        $remote = new MediaRenderer($current_dev);
        $this->success = $remote->play($input);
        return $this->success;
    }

    // Stop
    function stop() {
        $this->reset_properties();
        $core= new Core();
        $result = $core->search($this->terminal['HOST']);
        foreach($result as $device){
             $current_dev = $device['location'];
            }
        //echo ($current_dev);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $this->success = $remote->stop();
        return $this->success;
    }
    
    // Next
    function next() {
        $this->reset_properties();
        $core= new Core();
        $result = $core->search($this->terminal['HOST']);
        foreach($result as $device){
             $current_dev = $device['location'];
            }
        //echo ($current_dev);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $this->success = $remote->next();
        return $this->success;
    }
    
    // Previous
    function previous() {
        $this->reset_properties();
        $core= new Core();
        $result = $core->search($this->terminal['HOST']);
        foreach($result as $device){
             $current_dev = $device['location'];
            }
        //echo ($current_dev);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $this->success = $remote->previous();
        return $this->success;
    }

    
    // Set volume
    function set_volume($level) {
        $this->reset_properties();
        $core= new Core();
        $result = $core->search($this->terminal['HOST']);
        foreach($result as $device){
             $current_dev = $device['location'];
            }
        //DebMes($current_dev);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remotevolume = new MediaRendererVolume($current_dev);
        DebMes($level);
        $this->success = $remotevolume->SetVolume($level);
        DebMes($this->success);
        return $this->success;
    }  

      
}

?>
