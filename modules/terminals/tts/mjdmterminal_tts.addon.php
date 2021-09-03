<?php

class mjdmterminal_tts extends tts_addon
{
    
    function __construct($terminal)
    {
        $this->title       = "MjDM Terminal";

        $this->description = '<b>Описание:</b>&nbsp; Предназначен для Android устройств с ПО &nbsp;<a href="https://mjdm.ru/forum/viewtopic.php?f=5&t=6737">мой MjDM</a>. Фактически представляет собой тип терминала MajorDroid с расширенным API.<br>';
	$this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';    
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7999 (если по умолчанию, можно не указывать).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;change_tts_volume(), say(), sayTo(), sayReply(), ask().';
 
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        $this->setting     = json_decode($this->terminal['TTS_SETING'], true);	    
        $this->port = empty($this->setting['TTS_PORT']) ? 7999 : $this->setting['TTS_PORT'];
    }
    
    function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        return $this->sendMjdmCommand('tts:' . $message['MESSAGE']);
    }

    function ask($phrase, $level = 0)
    {
        return $this->sendMjdmCommand('ask:' . $phrase);
    }
    
    function set_volume($volume = 0)
    {
        return $this->sendMjdmCommand('volume:' . $volume);
    }
	
    public function set_volume_media($volume) {
        return $this->sendMjdmCommand('mvolume:' . $volume);
    }

    public function set_volume_notification($volume) {
        return $this->sendMjdmCommand('nvolume:' . $volume);
    }

    public function set_volume_alarm($volume) {
        return $this->sendMjdmCommand('avolume:' . $volume);
    }
	
    public function set_volume_ring($volume) {
        return $this->sendMjdmCommand('rvolume:' . $volume);
    }

    function set_brightness_display($brightness, $time=0)
    {
        // установим яркость дисплея
        return $this->sendMjdmCommand('brightness:' . $brightness);
    }
	
    function turn_on_display($time = 0)
    {
        return $this->sendMjdmCommand('screen:on');
    }
    function turn_off_display($time = 0)
    {
        return $this->sendMjdmCommand('screen:off');
    }
	
    // Get terminal status
    function terminal_status()
    {
        // Defaults
        $listening_keyphrase = -1;
        $volume_media        = -1;
        $volume_ring         = -1;
        $volume_alarm        = -1;
        $volume_notification = -1;
        $brightness_auto     = -1;
        $recognition         = -1;
        $fullscreen          = -1;
        $brightness          = -1;
        $display_state       = -1;
        $battery             = -1;
	    
        $result              = json_decode($this->sendMjdmCommand('status'));
	
        $out_data = array(
                'listening_keyphrase' =>(string) strtolower(($result['application']['settings']['listening_keyphrase'])), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
                'volume_media' => (int)rtrim($result['device']['volume_media'],'%'), // громкость медиа на терминале (-1 - не поддерживается терминалом)
                'volume_ring' => (int)rtrim($result['device']['volume_ring'],'%'), // громкость звонка к пользователям на терминале (-1 - не поддерживается терминалом)
                'volume_alarm' => (int)rtrim($result['device']['volume_alarm'],'%'), // громкость аварийных сообщений на терминале (-1 - не поддерживается терминалом)
                'volume_notification' => (int)rtrim($result['device']['volume_notification'],'%'), // громкость простых сообщений на терминале (-1 - не поддерживается терминалом)
                'brightness_auto' => (boolean) $result['device']['brightness_auto'], // автояркость включена или выключена true или false (-1 - не поддерживается терминалом)
                'recognition' => (boolean) $result['application']['settings']['recognition'], // распознавание на терминале включена или выключена true или false (-1 - не поддерживается терминалом)
                'fullscreen' => (boolean) $result['application']['settings']['recognition'], // полноекранный режим на терминале включена или выключена true или false (-1 - не поддерживается терминалом)
                'brightness' => (int)rtrim($result['device']['brightness'],'%'), // яркость екрана (-1 - не поддерживается терминалом)
                'battery' => (int) rtrim($result['device']['battery'],'%'), // заряд акумулятора терминала в процентах (-1 - не поддерживается терминалом)
                'display_state'=> (boolean) $result['application']['settings']['screenon'], // 1, 0  - состояние дисплея (-1 - не поддерживается терминалом)
            );
		
		// удаляем из массива пустые данные
		foreach ($out_data as $key => $value) {
			if ($value == '-1') unset($out_data[$key]); ;
		}
        return $out_data;
    }
	
    private function sendMjdmCommand($cmd)
    {
        if ($this->terminal['HOST']) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                return 0;
            }
            $result = socket_connect($socket, $this->terminal['HOST'], $this->port);
            if ($result === false) {
                return 0;
            }
            $result = socket_write($socket, $cmd, strlen($cmd));
            usleep(500000);
            socket_close($socket);
            return $result;
        }
    }
}
