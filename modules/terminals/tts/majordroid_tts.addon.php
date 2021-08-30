<?php

class majordroid_tts extends tts_addon
{
    
    function __construct($terminal)
    {
        $this->title       = "MajorDroid";
        $this->description = '<b>Описание:</b>&nbsp; Используется на устройствах которые поддерживаают MajorDroid API.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7999 (если по умолчанию, можно не указывать).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;change_tts_volume(), say(), sayTo(), sayReply(), ask().';

        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        $this->setting     = json_decode($this->terminal['TTS_SETING'], true);
        $this->port = empty($this->setting['TTS_PORT']) ? 7999 : $this->setting['TTS_PORT'];
    }
    
    public function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        $this->sendMajorDroidCommand('tts:' . $message['MESSAGE']);
		return true;
    }
    
    public function ask($phrase, $level = 0)
    {
        $this->sendMajorDroidCommand('ask:' . $phrase);
		return true;
    }

    public function set_volume_media($volume = 0)
    {
        $this->sendMajorDroidCommand('mvolume:' . $volume);
		return true;
    }

    public function set_volume_notification($volume = 0)
    {
        $this->sendMajorDroidCommand('nvolume:' . $volume);
		return true;
    }
    
    public function set_volume($volume = 0)
    {
        $this->sendMajorDroidCommand('volume:' . $volume);
		return true;
    }
    
    private function sendMajorDroidCommand($cmd)
    {
        if ($this->terminal['HOST']) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                return false;
            }
            $result = socket_connect($socket, $this->terminal['HOST'], $this->port);
            if ($result === false) {
                return false;
            }
            $result = socket_write($socket, $cmd, strlen($cmd));
            usleep(500000);
            socket_close($socket);
            return $result;
        }
    }
}
