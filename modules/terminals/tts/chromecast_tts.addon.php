<?php

/*
Addon Chromecast for app_player
*/

class chromecast_tts extends tts_addon
{
    
    // Constructor
    function __construct($terminal)
    {
        $this->title = 'Google Chromecast';
        $this->description = '<b>Описание:</b>&nbsp; Работает на устройствах поддерживающих протокол Chromecast (CASTv2) от компании Google.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping (пингование проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 8009 (если по умолчанию, можно не указывать).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
        
        $this->setting = json_decode($this->terminal['TTS_SETING'], true);
        $this->port = empty($this->setting['TTS_PORT']) ? 8009 : $this->setting['TTS_PORT'];
        // Chromecast
        include_once(DIR_MODULES . 'app_player/libs/castv2/Chromecast.php');
    }
    
    // Say
    public function say_media_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        if (preg_match('/\/cms\/cached.+/', $message['CACHED_FILENAME'], $m)) {
            $server_ip = getLocalIp();
            if (!$server_ip) {
                DebMes("Server IP not found", 'terminals');
                return false;
            } else {
                $message_link = 'http://' . $server_ip . $m[0];
            }
        }
        $cc = new GChromecast($this->terminal['HOST'], $this->port);
        $cc->requestId = time();
        while (!$cc->load($message_link, 0)) {
            $count = $count + 1;
            if ($count > 30) {
                break;
            }
            usleep(100000);
            $cc->requestId = time();
        }
        $cc->requestId = time();
        $response = $cc->play();
        if ($response) {
            //set_time_limit(2+$message['MESSAGE_DURATION']);
            sleep($message['MESSAGE_DURATION']);
            $this->success = TRUE;
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    // Set volume
    public function set_volume($level)
    {
        if (strlen($level)) {
            try {
                $cc = new GChromecast($this->terminal['HOST'], $this->port);
                $cc->requestId = time();
                $level = $level / 100;
                $cc->SetVolume($level);
                $this->success = TRUE;
            }
            catch (Exception $e) {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    // ping terminal
    public function ping_ttsservice($host)
    {
        if (ping($host)) {
            $cc = new GChromecast($this->terminal['HOST'], $this->port);
            $cc->requestId = time();
            $status = $cc->getStatus();
            if (is_array($status)) {
                $this->success = TRUE;
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
        $volume_media = -1;
        $volume_ring = -1;
        $volume_alarm = -1;
        $volume_notification = -1;
        $brightness_auto = -1;
        $recognition = -1;
        $fullscreen = -1;
        $brightness = -1;
        $display_state = -1;
        $battery = -1;
        
        $cc = new GChromecast($this->terminal['HOST'], $this->port);
        $cc->requestId = time();
        $status = $cc->getStatus();
        
        $out_data = array(
            'listening_keyphrase' => (string) strtolower($listening_keyphrase), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
            'volume_media' => (int) ($status['status']['volume']['level'] * 100), // громкость медиа на терминале (-1 - не поддерживается терминалом)
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
    
    public function play_media($link) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        // проверяем айпи
        $server_ip = getLocalIp();
        if (!$server_ip) {
            DebMes("Server IP not found", 'terminals');
            return false;
        }
        
        // проверяем файл на нужный формат
        $fileinfo = pathinfo($link);
        // если не он то конвертируем в кеш
        if ($fileinfo['extension'] != 'mp3') {
            $linknold = $link;
            $link = ROOT . "cms/cached/voice/" . $fileinfo['filename'] . '.mp3';
            if (!file_exists($link)) {
                DebMes('convert');
                if (!defined('PATH_TO_FFMPEG')) {
                    if (IsWindowsOS()) {
                        define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
                    } else {
                        define("PATH_TO_FFMPEG", 'ffmpeg');
                    }
                }
                shell_exec(PATH_TO_FFMPEG . " -i " . $linknold . " -vn -ar 44100 -ac 2 -b:a 192k -y " . $link);
            }
        }
        
        // превращаем в адрес 
        $file_link = 'http://' . $server_ip . '/' . str_ireplace(ROOT, "", $link);
        
        $cc = new GChromecast($this->terminal['HOST'], $this->port);
        $cc->requestId = time();
        while (!$cc->load($file_link, 0)) {
            $count = $count + 1;
            if ($count > 30) {
                break;
            }
            usleep(100000);
            $cc->requestId = time();
        }
        $cc->requestId = time();
        $response = $cc->play();
        if ($response) {
            sleep(get_media_info($file_link)['duration']);
            $this->success = TRUE;
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
}

?>
