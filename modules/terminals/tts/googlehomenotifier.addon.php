<?php

class googlehomenotifier extends tts_addon {

    function __construct($terminal) {
        $this->title="GoogleHomeNotifier API";
        parent::__construct($terminal);
    }

    function say($phrase, $level = 0)
    {
        $port = $this->terminal['PLAYER_PORT'];
        $language = SETTINGS_SITE_LANGUAGE;
        if (!$port) {
            $port = '8091';
        }
        $host = $this->terminal['HOST'];
        $url = 'http://' . $host . ':' . $port . '/google-home-notifier?language=' . $language . '&text=' . urlencode($phrase);
        getURL($url, 0);
    }

}