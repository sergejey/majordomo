<?php
/*
Addon Kodi (XBMC) for app_player
*/
class alicevox extends tts_addon
{
    function __construct($terminal)
    {
        $this->title   = "Alicevox";
        $this->description = '<b>Описание:</b>&nbsp;  Работает на медиацентрах KODI не ниже версии 18 (Leia) с установленным плагином &nbsp;<a href="https://github.com/SergMicar/script.alicevox.master">Alicevox</a>.<br>Ссылка на &nbsp;<a href="https://mjdm.ru/forum/viewtopic.php?f=5&t=2893">тему форума</a>.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping ("пингование" проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Не забудьте активировать управление по HTTP в настройках KODI (Настройки -> Сервисные настройки -> Управление -> Разрешить удаленное управление по HTTP) и установить "порт", "имя пользователя" и "пароль".<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';

        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
	    
        // содержит в себе все настройки терминала кроме айпи адреса
        $this->setting = json_decode($this->terminal['TTS_SETING'], true);
	    
        $this->dingdong = $this->setting['TTS_DINGDONG_FILE'];
        $this->address = 'http://'.$this->setting['TTS_USERNAME'].':'.$this->setting['TTS_PASSWORD'].'@'.$this->terminal['HOST'].':'.(empty($this->setting['TTS_PORT'])?8080:$this->setting['TTS_PORT']);
    }
    
    
    // Say
    public function say_media_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
       if ($message['CACHED_FILENAME']) {
            $fileinfo = pathinfo($message['CACHED_FILENAME']);
            $filename = $fileinfo['dirname'] . '/' . rand(1000000000,9000000000).'.wav';
            if (!defined('PATH_TO_FFMPEG')) {
                if (IsWindowsOS()) {
                    define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
                } else {
                    define("PATH_TO_FFMPEG", 'ffmpeg');
                }
            }
            if ($this->dingdong) {
                shell_exec(PATH_TO_FFMPEG . ' -i ' . ROOT . "cms/sounds/" . $this->dingdong . ' -i ' . $message['CACHED_FILENAME'] . ' -filter_complex concat=n=2:v=0:a=1 -f WAV -acodec pcm_s16le -ar 44100  -vn -y ' . $filename);
            } else {
                shell_exec(PATH_TO_FFMPEG . " -i " . $message['CACHED_FILENAME'] . " -f WAV -acodec pcm_s16le -ar 44100  -vn -y " . $filename);
            }

            if (file_exists($filename)) {
                if (preg_match('/\/cms\/cached.+/', $filename, $m)) {
                    $LinkName = 'http://' . getLocalIp() . $m[0];
                    $command = "{\"jsonrpc\":\"2.0\",\"method\":\"Addons.ExecuteAddon\",\"params\":{\"addonid\":\"script.alicevox.master\",\"params\":[\"" . $LinkName . "\"]},\"id\":1}";
	            	$result = $this->send_command($command);
                    if ($result['result']=='OK') {
                        sleep($message['MESSAGE_DURATION']+3);
                        $this->success = TRUE;
                        @unlink($filename);
                    } else {
                        $this->success = FALSE;
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

    // ping terminal
    function ping_ttsservice($host)
    {
        // proverka na otvet
        $command = "{\"jsonrpc\":\"2.0\",\"method\":\"Addons.ExecuteAddon\",\"params\":{\"addonid\":\"script.alicevox.master\",\"params\":[\"ping\"]},\"id\":1}";
        $result = $this->send_command($command);
	if ($result['error']) {
            $this->success = FALSE;
        } else if (is_array($result)) {
            $this->success = TRUE;
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    private function send_command($data=null, $timeout=3) {
        $_curlHdl = curl_init();
        curl_setopt($_curlHdl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curlHdl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($_curlHdl, CURLOPT_CONNECTTIMEOUT, 7);
        curl_setopt($_curlHdl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($_curlHdl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($_curlHdl, CURLINFO_HEADER_OUT, true);
        curl_setopt($_curlHdl, CURLOPT_POST, true);
        curl_setopt($_curlHdl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($_curlHdl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data))
		);

        $url =  $this->address.'/jsonrpc';
        curl_setopt($_curlHdl, CURLOPT_URL, $url);

        $answer = curl_exec($_curlHdl);
        if(curl_errno($_curlHdl)) {
            return array('error'=>curl_error($_curlHdl));
        }

        if ($answer == false) {
            return array('error'=>"Couldn't reach Kodi device.");
        }

        $answer = json_decode($answer, true);
        if (isset($answer['error']) ) return array('result'=>null, 'error'=>$answer['error']);
        return array('result'=>$answer['result']);
    }
}
