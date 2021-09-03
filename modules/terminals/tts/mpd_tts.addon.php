<?php

/*
Addon MPD for tts
*/

class mpd_tts extends tts_addon
{
    
    // Constructor
    function __construct($terminal)
    {
        
        $this->title       = 'Music Player Daemon (MPD)';
        $this->description = '<b>Описание:</b>&nbsp; Для работы использует кроссплатформенный музыкальный сервер MPD</a>.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping ("пингование" проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 6600 (если по умолчанию, можно не указывать).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';    
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        
        $this->setting  = json_decode($this->terminal['TTS_SETING'], true);
        $this->port     = empty($this->setting['TTS_PORT']) ? 6600 : $this->setting['TTS_PORT'];
        $this->password = $this->setting['TTS_PASSWORD'];
        
        // MPD
        include_once(DIR_MODULES . 'app_player/libs/mpd/mpd.class.php');
        $this->mpd = new mpd_player($this->terminal['HOST'], $this->port, $this->password);
        $this->mpd->Disconnect();
    }
    
    // Say
    public function say_media_message($message, $terminal)
    {
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
	    $fileinfo = pathinfo($message['CACHED_FILENAME']);
            $filename = $fileinfo['dirname'] . '/' . $fileinfo['filename'].'sil'.'.mp3'; 
                        
            if (!defined('PATH_TO_FFMPEG')) {
                if (IsWindowsOS()) {
                    define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
                } else {
                    define("PATH_TO_FFMPEG", 'ffmpeg');
                }
            }
            // для Сергея - у него мпд немного глючит вставим паузу 1 секунду
            shell_exec(PATH_TO_FFMPEG . ' -i ' . $message['CACHED_FILENAME'] . ' -i '. SERVER_ROOT . '/cms/terminals/silent.wav -filter_complex concat=n=2:v=0:a=1 -vn -ar 44100 -ac 2 -ab 192 -f mp3 -y ' . $filename );

    	    // проверяем айпи
            $server_ip = getLocalIp();
            if (!$server_ip) {
                return false;
            }

            $file_link = 'http://' . $server_ip . '/' . str_ireplace(ROOT, "", $filename);
            $this->mpd->PLClear();
            $this->mpd->PLAddFile($file_link);
            if ($this->mpd->Play()) {
                sleep($message['MESSAGE_DURATION']);
                // контроль окончания воспроизведения медиа
                $count = 0;
                $this->success = TRUE;
                while ($result['state'] != 'stop') {
                    $result = $this->mpd->GetStatus();
                    $count = $count + 1;
                    sleep (1);
                    if ($count > 10 ) {
                        $this->success = FALSE;
                        break;
                    }
                }
                sleep (2);
                @unlink($filename);
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Set volume
    public function set_volume($level = 0)
    {
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->SetVolume($level)) {
                    $this->success = TRUE;
                    // контроль установки громкости
                    $count = 0;
                    while ($result['volume'] != $level) {
                        $result = $this->mpd->GetStatus();
                        usleep (100000);
			$count = $count + 1;
                        if ($count > 30 ) {
                            $this->success = FALSE;
                            break;
                        }
                   }
                } else {
                    $this->success = FALSE;
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // ping terminal
    public function ping_ttsservice($host)
    {
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            if ($this->mpd->Ping()) {
                $this->success = TRUE;
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Get terminal status
    public function terminal_status()
    {
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
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
        if ($this->mpd->connected) {
            $result = $this->mpd->GetStatus();
        }
        
        $out_data = array(
            'listening_keyphrase' => (string) strtolower($listening_keyphrase), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
            'volume_media' => (int) $result['volume'], // громкость медиа на терминале (-1 - не поддерживается терминалом)
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
    
    // Say
    public function play_media ($link)
    {
	// проверяем айпи
        $server_ip = getLocalIp();
        if (!$server_ip) {
            DebMes("Server IP not found", 'terminals');
            return false;
        }
	if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            // превращаем в адрес 
            $file_link = 'http://' . $server_ip . '/' . str_ireplace(ROOT, "", $link);
            $this->mpd->PLClear();
            $this->mpd->PLAddFile($file_link);
            if ($this->mpd->Play()) {
                sleep(2);
                // контроль окончания воспроизведения медиа
                $count = 0;
                $this->success = TRUE;
                while ($result['state'] != 'stop') {
                    $result = $this->mpd->GetStatus();
                    $count = $count + 1;
                    sleep (1);
                    if ($count > 10 ) {
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
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
}

?>
