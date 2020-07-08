<?php

class tts_addon {
    // Addon info
    private $title = NULL;
    public $terminal = NULL;

    function __construct($terminal) {
        $this->terminal = $terminal;
    }

    public function say($phrase, $level=0) {
        return false;
    }

    public function ask($phrase, $level=0) {
        return $this->say($phrase);
    }

    public function sayCached($phrase, $level = 0, $cached_file = '') {
        return false;
    }

}