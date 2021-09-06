<?php

class mainterminal extends tts_addon
{
    function __construct($terminal)
    {
        $this->title       = "Основной терминал системы";
        $this->description = '<b>Описание:</b>&nbsp; Использует системный звуковой плеер, работает на встроенной звуковой карте сервера, без каких либо настроек.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp; ip_ping.<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Пользователи OS Linux могут указать предпочитаемый плеер, см. /path/to/majordomo/config.php, опция Define(\'AUDIO_PLAYER\',\'player_name\');.<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply(), ask().';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
    }
    
    // Say
    public function say_media_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        if ($message['CACHED_FILENAME']) {
            if (file_exists($message['CACHED_FILENAME'])) {
                if (IsWindowsOS()) {
                    safe_exec(DOC_ROOT . '/rc/madplay.exe ' . $message['CACHED_FILENAME'], 0, 0);
                } else {
                    if (defined('AUDIO_PLAYER') && AUDIO_PLAYER != '') {
                        $audio_player = AUDIO_PLAYER;
                    } else {
                        $audio_player = 'mplayer';
                    }
                    safe_exec($audio_player . ' ' . $message['CACHED_FILENAME'] . " >/dev/null 2>&1", 0, 0);
                }
                sleep($message['MESSAGE_DURATION']);
                $this->success = TRUE;
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    public function play_media($link)
    {
        if ($link) {
            if (file_exists($link)) {
                if (IsWindowsOS()) {
                    safe_exec(DOC_ROOT . '/rc/madplay.exe ' . $link, 0, 0);
                } else {
                    if (defined('AUDIO_PLAYER') && AUDIO_PLAYER != '') {
                        $audio_player = AUDIO_PLAYER;
                    } else {
                        $audio_player = 'mplayer';
                    }
                    safe_exec($audio_player . ' ' . $link . " >/dev/null 2>&1", 0, 0);
                }
                sleep(2);
                $this->success = TRUE;
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
}
