<?php

class chromegate_tts extends tts_addon {

    function __construct($terminal) {
	$this->title="ChromeGate addon for Google Chrome";
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply(), ask().';
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
    }

    // Say
    function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        if ($terminal['TITLE']) {
            if ($message['MESSAGE']) {
	        postToWebSocket($message['EVENT'], array('level' => $message['IMPORTANCE'], 'message' => $message['MESSAGE'], 'destination' => $terminal['TITLE'] ), 'PostEvent');
                usleep(100000);
	        $this->success = TRUE;
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }

    function ask($phrase, $level=0) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        if ($phrase) {
            postToWebSocket('ASK', array('level' => $level, 'prompt' => $phrase, 'target' => ''), 'PostEvent');
            sleep(1);
	    $this->success = TRUE;
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
}

?>
