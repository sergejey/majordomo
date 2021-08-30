<?php
/*
Addon iobroker.paw http for app_player
*/
class iobroker_tts extends tts_addon
{
    
    function __construct($terminal)
    {
        $this->title       = "ioBroker.paw";
        $this->description = '<b>Описание:</b>&nbsp; Для работы использует андроид с установленным приложением &nbsp;<a href="https://play.google.com/store/apps/details?id=ru.codedevice.iobrokerpawii">ioBroker.paw</a>.<br>';
		$this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
		$this->description .= '<b>Настройка:</b>&nbsp; Не забудьте активировать HTTP интерфейс в настройках ioBroker.paw и включть работу сервиса кнопкой: Connection.<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>say(),sayTo()<br>';
                
        $this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
        
        $this->setting = json_decode($this->terminal['TTS_SETING'], true);
        $this->port    = empty($this->setting['TTS_PORT']) ? 8080 : $this->setting['TTS_PORT'];
        $this->curl    = curl_init();
        $this->address = 'http://' . $this->terminal['HOST'] . ':' . $this->port;
    }
	
    // ping terminal tts service
    public function ping_ttsservice($host)
    {
        $connection = @fsockopen($this->terminal['HOST'],$this->setting['TTS_PORT'],$errno,$errstr,1);
        if (is_resource($connection)) {
            $this->success = TRUE;
            fclose($connection);
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
	
    // Say
    function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        getURL($this->address . "/api/set.json?play=true", 0);
        sleep(1);
        getURL($this->address . "/api/set.json?ringtone=false", 0);
        usleep(500000);
        $url = $this->address . "/api/set.json?tts=" . urlencode($message['MESSAGE']);
        getURL($url, 0);
        sleep($message['MESSAGE_DURATION']);
        return true;
    }
    
    function turn_on_display($time = 0)
    {
        // включаем дисплей
        $url = $this->address . "/api/set.json?toWake=true";
        if ($time > 0) {
            setTimeout($this->terminal['NAME'] . '_on_display', "getURL('$url', 0);", $time);
        } else {
            getURL($url, 0);
        }
        return true;
    }
    function turn_off_display($time = 0)
    {
        // выключаем дисплей
        $url = $this->address . "/api/set.json?toWake=false";
        if ($time > 0) {
            setTimeout($this->terminal['NAME'] . '_off_display', "getURL('$url', 0);", $time);
        } else {
            getURL($url, 0);
        }
        return true;
        
    }
    
    function set_brightness_display($brightness, $time = 0)
    {
        // установим яркость дисплея
        $url = $this->address . "/api/set.json?brightness=" . $brightness;
        if ($time > 0) {
            setTimeout($this->terminal['NAME'] . '_set_brightness', "getURL('$url', 0);", $time);
        } else {
            getURL($url, 0);
        }
        return true;
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
        
        $result = json_decode(getURL($this->address . "/api/get.json", 0), true);
        if ($result) {
            if ($result['display']['state']) {
                $display_state = 'On';
            } else if (!$result['display']['state']) {
                $display_state = 'Off';
            }
            $volume_media        = intval($result['volume']['music'] * 100 / $result['volume']['music-max']);
            $volume_ring         = intval($result['volume']['ring'] * 100 / $result['volume']['ring-max']);
            $volume_notification = intval($result['volume']['notification'] * 100 / $result['volume']['notification-max']);
            $volume_alarm        = intval($result['volume']['alarm'] * 100 / $result['volume']['alarm-max']);
            
            $brightness = intval($result['display']['brightness']);
        }
        
        $out_data = array(
            'listening_keyphrase' => (string) strtolower($listening_keyphrase), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
            'volume_media' => (int) $volume_media, // громкость медиа на терминале (-1 - не поддерживается терминалом)
            'volume_ring' => (int) $volume_ring, // громкость звонка к пользователям на терминале (-1 - не поддерживается терминалом)
            'volume_alarm' => (int) $volume_alarm, // громкость аварийных сообщений на терминале (-1 - не поддерживается терминалом)
            'volume_notification' => (int) $volume_notification, // громкость простых сообщений на терминале (-1 - не поддерживается терминалом)
            'brightness_auto' => (int) $brightness_auto, // автояркость включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'recognition' => (int) $recognition, // распознавание на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'fullscreen' => (int) $fullscreen, // полноекранный режим на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'brightness' => (int) $brightness, // яркость екрана (-1 - не поддерживается терминалом)
            'battery' => (int) $battery, // заряд акумулятора терминала в процентах (-1 - не поддерживается терминалом)
            'display_state' => (int) $result['display']['state'] // 1, 0  - состояние дисплея (-1 - не поддерживается терминалом)
        );
        
        // удаляем из массива пустые данные
        foreach ($out_data as $key => $value) {
            if ($value == '-1')
                unset($out_data[$key]);
            ;
        }
        return $out_data;
    }
}
?>
