<?php

class stt_addon {
    // Addon info
    private $title = NULL;
    public $terminal = NULL;

    function __construct($terminal) {
        $this->terminal = $terminal;
    }

    public function turnOn_stt() {
        return false;
    }
    
    public function turnOff_stt() {
        return false;
    }
    function ping_sttservice($host)
    {
        return ping($host);
    }
}
