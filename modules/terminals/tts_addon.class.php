<?php

class tts_addon {
    // Addon info
    private $title = NULL;
    public $terminal = NULL;

    function __construct($terminal) {
        $this->terminal = $terminal;
    }

    public function ask($phrase, $level) {
        return $this->say($phrase);
    }
    
    public function set_volume_media($volume) {
        return false;
    }

    public function set_volume_notification($volume) {
        return false;
    }

    public function set_volume_alarm($volume) {
        return false;
    }
	
    public function set_volume_ring($volume) {
        return false;
    }

    public function set_volume($volume) {
        return false;
    }

    function ping_ttsservice($host)
    {
        return ping($host);
    }

    function say_media_message($message, $terminal)
    {
        return false;
    }

    function say_message($message, $terminal)
    {
        return false;
    }

    function set_brightness_display($brightness)
    {
        return false;
    }

    function turn_on_display($time)
    {
        return false;
    }

    function turn_off_display($time)
    {
        return false;
    }

    function terminal_status()
    {
        return false;
    } 

    function play_media($link)
    {
        return false;
    }
}
