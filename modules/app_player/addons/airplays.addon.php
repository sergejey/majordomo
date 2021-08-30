<?php

/*
Addon airplay for app_player
*/

class airplays extends app_player_addon
{
    
    // Constructor
    function __construct($terminal)
    {
        $this->reset_properties();
        $this->title       = "Airplay";
        $this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука на устройства поддерживающих протокол AirPlay.<br>';
        $this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Нет (если ТТС такого же типа, что и плеер).<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7000 (если по умолчанию, можно не указывать).';
        $this->terminal = $terminal;
        $this->port     = (empty($this->terminal['PLAYER_PORT']) ? 7000 : $this->terminal['PLAYER_PORT']);
        include_once(DIR_MODULES . 'app_player/libs/Airplay/airplay.php');
    }
    // Get player status
    function status()
    {
        $this->reset_properties();
        // Defaults
        $playlist_id      = -1;
        $playlist_content = array();
        $track_id         = -1;
        $name             = -1;
        $file             = -1;
        $length           = -1;
        $time             = -1;
        $state            = -1;
        $volume           = -1;
        $muted            = -1;
        $random           = -1;
        $loop             = -1;
        $repeat           = -1;
        $crossfade        = -1;
        $speed            = -1;
        
        $this->data = array(
            'playlist_id' => (int) $playlist_id, // номер или имя плейлиста 
            'playlist_content' => $playlist_content, // содержимое плейлиста должен быть ВСЕГДА МАССИВ 
            // обязательно $playlist_content[$i]['pos'] - номер трека
            // обязательно $playlist_content[$i]['file'] - адрес трека
            // возможно $playlist_content[$i]['Artist'] - артист
            // возможно $playlist_content[$i]['Title'] - название трека
            'track_id' => (int) track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'name' => (string) $name, //Current speed for playing media. float.
            'file' => (string) $file, //Current link for media in device. String.
            'length' => (int) $length, //Track length in seconds. Integer. If unknown = 0. 
            'time' => (int) $time, //Current playback progress (in seconds). If unknown = 0. 
            'state' => (string) strtolower($state), //Playback status. String: stopped/playing/paused/unknown 
            'volume' => (int) $volume, // Volume level in percent. Integer. Some players may have values greater than 100.
            'muted' => (int) $random, // Volume level in percent. Integer. Some players may have values greater than 100.
            'random' => (int) $random, // Random mode. Boolean. 
            'loop' => (int) $loop, // Loop mode. Boolean.
            'repeat' => (int) $repeat, //Repeat mode. Boolean.
            'crossfade' => (int) $crossfade, // crossfade
            'speed' => (int) $speed // crossfade
        );
        // удаляем из массива пустые данные
        foreach ($this->data as $key => $value) {
            if ($value == '-1' or !$value)
                unset($this->data[$key]);
        }
        
        $this->success = TRUE;
        $this->message = 'OK';
        return $this->success;
    }
    
    // Say
    function play($input)
    {
        $this->reset_properties();
        if (strlen($input)) {
            try {
                $fileinfo = pathinfo($input);
                $filename = $fileinfo[dirname] . '/' . $fileinfo[filename] . '.avi';
                if (!file_exists($filename)) {
                    if (!defined('PATH_TO_FFMPEG')) {
                        if (IsWindowsOS()) {
                            define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
                        } else {
                            define("PATH_TO_FFMPEG", 'ffmpeg');
                        }
                    }
                    shell_exec(PATH_TO_FFMPEG . " -loop 1 -y -i " . DOC_ROOT . "/img/logo.png -i " . $message['CACHED_FILENAME'] . " -shortest -acodec copy -vcodec mjpeg " . $filename);
                }
                $remote = new AirPlay($this->terminal['HOST'], $this->port);
                $remote->sendvideo($message_link);
                $this->success = TRUE;
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = $e->getMessage();
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Input is missing!';
        }
        return $this->success;
    }
}

?>
