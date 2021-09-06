<?php

/*
Addon VLC HTTP for app_player
*/

class vlcweb_tts extends tts_addon
{
    
    // Constructor
    function __construct($terminal)
    {
        $this->title       = 'VLC через HTTP';
        $this->description = '<b>Описание:</b>&nbsp; Работает с VideoLAN Client (VLC). Управление VLC производится по протоколу HTTP.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Не забудьте активировать HTTP (web) интерфейс в настройках VLC<br>';
        $this->description .= '(Инструменты -> Настройки -> Все -> Основные интерфейсы -> Дополнительные модули интерфейса -> Web)<br>';
        $this->description .= 'и установить для него пароль (Основные интерфейсы -> Lua -> HTTP -> Пароль).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
        $this->setting = json_decode($this->terminal['TTS_SETING'], true);
        
        $this->address = 'http://' . $this->terminal['HOST'] . ':' . (empty($this->setting['TTS_PORT']) ? 8080 : $this->setting['TTS_PORT']);
        
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
    
    // Private: VLC-WEB request
    private function vlcweb_request($path, $data = array())
    {
        $params = array();
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $params[] = $key . '=' . urlencode($value);
            } else {
                $params[] = $value;
            }
        }
        $params = implode('&', $params);
        
        // init curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($this->setting['TTS_USERNAME'] OR $this->setting['TTS_PASSWORD']) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->setting['TTS_USERNAME'] . ':' . $this->setting['TTS_PASSWORD']);
        }
        curl_setopt($curl, CURLOPT_URL, $this->address . '/requests/' . $path . (strlen($params) ? '?' . $params : ''));
        
        if ($result = curl_exec($curl)) {
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($code) {
                case 200:
                    $this->success = TRUE;
                    $this->message = 'OK';
                    $this->data    = $result;
                    break;
                case 401:
                    $this->success = FALSE;
                    $this->message = 'Authorization failed!';
                    break;
                default:
                    $this->success = FALSE;
                    $this->message = 'Unknown error (code ' . $code . ')!';
            }
        } else {
            $this->success = FALSE;
        }
        curl_close($curl);
        return $this->success;
    }
    
    // Private: VLC-WEB parse XML
    private function vlcweb_parse_xml($data)
    {
        
        try {
            if ($xml = @new SimpleXMLElement($data)) {
                $this->success = TRUE;
                $this->message = 'OK';
                $this->data    = $xml;
            } else {
                $this->success = FALSE;
                $this->message = 'SimpleXMLElement error!';
            }
        }
        catch (Exception $e) {
            $this->success = FALSE;
            $this->message = $e->getMessage();
        }
        return $this->success;
    }
    
    public function say_media_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        $outlink = $message['CACHED_FILENAME'];
        // берем ссылку http
        if (preg_match('/\/cms\/cached.+/', $outlink, $m)) {
            $server_ip = getLocalIp();
            if (!$server_ip) {
                DebMes("Server IP not found", 'terminals');
                return false;
            } else {
                $message_link = 'http://' . $server_ip . $m[0];
            }
        }
        // cleare playlist
        $this->vlcweb_request('status.xml', array('command' => 'pl_empty'));
        // play message
        if (file_exists($outlink)) {
            $message_link = preg_replace('/\\\\$/is', '', $message_link);
            if ($this->vlcweb_request('status.xml', array('command' => 'in_play', 'input' => $message_link ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->success = TRUE;
                    sleep($message['MESSAGE_DURATION']);
                    // контроль окончания воспроизведения медиа
                    $count = 0;
                    while ($xml->state != 'stopped') {
                        $this->vlcweb_request('status.xml');
                        if ($this->vlcweb_parse_xml($this->data)) {
                            $xml = $this->data;
                        }
                        sleep(1);
                        $count = $count + 1;
                        if ($count > 30) {
                            $this->success = FALSE;
                            break;
                        }
                    }
                } else {
                    $this->success = FALSE;
                }
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    // Set volume
    public function set_volume($level)
    {
        if (strlen($level)) {
            $level = round((int) $level * 256 / 100);
            // проверка установки уровня звука с контролем на 3 секунды
            $count = 0;
            while ($xml->volume != $level) {
                if ($this->vlcweb_request('status.xml', array('command' => 'volume','val' => (int) $level))) {
                    $this->success = TRUE;
                    $this->vlcweb_request('status.xml');
                    if ($this->vlcweb_parse_xml($this->data)) {
                        $xml = $this->data;
                    }
                    usleep(100000);
                    $count = $count + 1;
                    if ($count > 30) {
                        $this->success = FALSE;
                        break;
                    }
                }
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    public function play_media($link)
    {
        // берем ссылку http
        if (preg_match('/\/cms\/cached.+/', $link, $m)) {
            $server_ip = getLocalIp();
            if (!$server_ip) {
                DebMes("Server IP not found", 'terminals');
                return false;
            } else {
                $message_link = 'http://' . $server_ip . $m[0];
            }
        }
        // cleare playlist
        $this->vlcweb_request('status.xml', array(
            'command' => 'pl_empty'
        ));
        // play audio
        if (file_exists($link)) {
            $message_link = preg_replace('/\\\\$/is', '', $message_link);
            if ($this->vlcweb_request('status.xml', array('command' => 'in_play','input' => $message_link))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->success = TRUE;
                    sleep(2);
                    // контроль окончания воспроизведения медиа
                    $count = 0;
                    while ($xml->state != 'stopped') {
                        $this->vlcweb_request('status.xml');
                        if ($this->vlcweb_parse_xml($this->data)) {
                            $xml = $this->data;
                        }
                        sleep(1);
                        $count = $count + 1;
                        if ($count > 30) {
                            $this->success = FALSE;
                            break;
                        }
                    }
                } else {
                    $this->success = FALSE;
                }
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
	// Get terminal status
    public function terminal_status()
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
        
        // get status
        if ($this->vlcweb_request('status.xml')) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $xml = $this->data;
            }
        }
        
        $out_data = array(
            'listening_keyphrase' => (string) strtolower($listening_keyphrase), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
            'volume_media' => (int) round((int) $xml->volume * 100 / 256), // громкость медиа на терминале (-1 - не поддерживается терминалом)
            'volume_ring' => (int) $volume_ring, // громкость звонка к пользователям на терминале (-1 - не поддерживается терминалом)
            'volume_alarm' => (int) $volume_alarm, // громкость аварийных сообщений на терминале (-1 - не поддерживается терминалом)
            'volume_notification' => (int) $volume_notification, // громкость простых сообщений на терминале (-1 - не поддерживается терминалом)
            'brightness_auto' => (int) $brightness_auto, // автояркость включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'recognition' => (int) $recognition, // распознавание на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'fullscreen' => (int) $recognition, // полноекранный режим на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'brightness' => (int) $brightness, // яркость екрана (-1 - не поддерживается терминалом)
            'battery' => (int) $battery, // заряд акумулятора терминала в процентах (-1 - не поддерживается терминалом)
            'display_state' => (int) $display_state // 1, 0  - состояние дисплея (-1 - не поддерживается терминалом)
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
