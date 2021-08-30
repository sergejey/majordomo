<?php

/*
Addon Chromecast for app_player
*/

class chromecast extends app_player_addon
{
    
    // Constructor
    function __construct($terminal)
    {
        $this->reset_properties();
        $this->title       = 'Google Chromecast';
        $this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука на устройствах поддерживающих протокол Chromecast (CASTv2) от компании Google.<br>';
        $this->description .= 'Воспроизведение видео на терминале этого типа пока не поддерживается.<br>';
        $this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Да (если ТТС такого же типа, что и плеер).<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping ("пингование" проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 8009 (если по умолчанию, можно не указывать).<br>';
        $this->terminal = $terminal;
        $this->port     = (empty($this->terminal['PLAYER_PORT']) ? 8009 : $this->terminal['PLAYER_PORT']);
        
        if (!$this->terminal['HOST'])
            return false;

        // Chromecast
        include_once(DIR_MODULES . 'app_player/libs/castv2/Chromecast.php');
        $this->Gcc = new GChromecast($this->terminal['HOST'], $this->port);
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
        
        $this->Gcc->requestId = time();
        $status              = $this->Gcc->getStatus();
        $this->Gcc->requestId = time();
        $result              = $this->Gcc->getMediaSession();
        
        $this->data = array(
            'playlist_id' => (int) $playlist_id, // номер или имя плейлиста 
            'playlist_content' => $playlist_content, // содержимое плейлиста должен быть ВСЕГДА МАССИВ 
            // обязательно $playlist_content[$i]['pos'] - номер трека
            // обязательно $playlist_content[$i]['file'] - адрес трека
            // возможно $playlist_content[$i]['Artist'] - артист
            // возможно $playlist_content[$i]['Title'] - название трека
            'track_id' => (int) $result['status'][0]['media']['tracks'][0]['trackId'], //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'name' => (string) $name, //Current speed for playing media. float.
            'file' => (string) $result['status'][0]['media']['contentId'], //Current link for media in device. String.
            'length' => (int) $result['status'][0]['media']['duration'], //Track length in seconds. Integer. If unknown = 0. 
            'time' => (int) $result['status'][0]['currentTime'], //Current playback progress (in seconds). If unknown = 0. 
            'state' => (string) strtolower($result['status'][0]['playerState']), //Playback status. String: stopped/playing/paused/unknown 
            'volume' => (int) ($status['status']['volume']['level'] * 100), // Volume level in percent. Integer. Some players may have values greater than 100.
            'muted' => (int) $result['status'][0]['volume']['muted'], // Volume level in percent. Integer. Some players may have values greater than 100.
            'random' => (int) $random, // Random mode. Boolean. 
            'loop' => (int) $loop, // Loop mode. Boolean.
            'repeat' => (string) $result['status'][0]['repeatMode'], //Repeat mode. Boolean.
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
    
    
    // Play
    function play($input) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        $this->reset_properties();
        if (strlen($input)) {
            try {
                $this->Gcc->requestId = time();
                $this->Gcc->load($input, 0);
                $this->Gcc->requestId = time();
                $this->Gcc->play();
                $this->success = TRUE;
                $this->message = 'Ok!';
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
    
    // Pause
    function pause()
    {
        $this->reset_properties();
        try {
            $this->Gcc->requestId = time();
            $this->Gcc->pause();
            $this->success = TRUE;
            $this->message = 'OK';
        }
        catch (Exception $e) {
            $this->success = FALSE;
            $this->message = $e->getMessage();
        }
        return $this->success;
    }
    
    // Stop
    function stop()
    {
        $this->reset_properties();
        try {
            $this->Gcc->requestId = time();
            $this->Gcc->stop();
            $this->success = TRUE;
            $this->message = 'OK';
        }
        catch (Exception $e) {
            $this->success = FALSE;
            $this->message = $e->getMessage();
        }
        return $this->success;
    }
    
    // Set volume
    function set_volume($level)
    {
        $this->reset_properties();
        if (strlen($level)) {
            try {
                $this->Gcc->requestId = time();
                $level               = $level / 100;
                $this->Gcc->SetVolume($level);
                $this->success = TRUE;
                $this->message = 'OK';
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = $e->getMessage();
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Level is missing!';
        }
        return $this->success;
    }
    
    // Restore player data from terminals
    function restore_media($input, $position = 0)
    {
        $this->reset_properties();
        $this->Gcc->requestId = time();
        $this->Gcc->load($input, 0);
        $this->Gcc->requestId = time();
        //$this->Gcc->seek($position);
        $this->Gcc->requestId = time();
        $response = $this->Gcc->play();
        if ($response) {
            $this->success = TRUE;
            $this->message = 'Play files';
        } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
        }
        return $this->success;
    }
    
    // ping mediaservise
    public function ping_mediaservice($host)
    {
        if (ping($host)) {
            $this->Gcc->requestId = time();
            $status = $this->Gcc->getStatus();
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
}

?>
