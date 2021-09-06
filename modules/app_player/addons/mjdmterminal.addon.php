<?php

class mjdmterminal extends app_player_addon
{
    
    function __construct($terminal)
    {
        $this->title       = "MjDM Terminal player";
        $this->description = '<b>Описание:</b>&nbsp;Предназначен для управления плеером устройств с ПО &nbsp;<a href="https://mjdm.ru/forum/viewtopic.php?f=5&t=6737">мой MjDM</a>.<br>';
	$this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Нет (если ТТС такого же типа, что и плеер).<br>';
	$this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
	$this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7999 (если по умолчанию, можно не указывать).<br>';
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        $this->port = empty($this->terminal['PLAYER_PORT']) ? 7999 : $this->terminal['PLAYER_PORT'];
    }
  
    // Pause
    function pause()
    {
        $this->reset_properties();
        try {
            $this->sendMjdmCommand('media pause');
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
            $this->sendMjdmCommand('media stop');
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
                $this->sendMjdmCommand('mvolume:' . $level);
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
	
    // Play
    function play($input) 
    {
        $this->reset_properties();
        if (strlen($input)) {
            try {
                $this->sendMjdmCommand('play:' . $input);
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
        
        $result = json_decode($this->sendMjdmCommand('status'));
        
        $this->data = array(
            'playlist_id' => (int) $playlist_id, // номер или имя плейлиста 
            'playlist_content' => $playlist_content, // содержимое плейлиста должен быть ВСЕГДА МАССИВ 
            // обязательно $playlist_content[$i]['pos'] - номер трека
            // обязательно $playlist_content[$i]['file'] - адрес трека
            // возможно $playlist_content[$i]['Artist'] - артист
            // возможно $playlist_content[$i]['Title'] - название трека
            'track_id' => (int) $track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'name' => (string) $name, //Current speed for playing media. float.
            'file' => (string) $file, //Current link for media in device. String.
            'length' => (int) $length, //Track length in seconds. Integer. If unknown = 0. 
            'time' => (int) $time, //Current playback progress (in seconds). If unknown = 0. 
            'state' => (string) strtolower($state), //Playback status. String: stopped/playing/paused/unknown 
            'volume' => (int)rtrim($result['device']['volume_media'],'%'), // Volume level in percent. Integer. Some players may have values greater than 100.
            'muted' => (int) $muted, // Volume level in percent. Integer. Some players may have values greater than 100.
            'random' => (int) $random, // Random mode. Boolean. 
            'loop' => (int) $loop, // Loop mode. Boolean.
            'repeat' => (string) $repeat, //Repeat mode. Boolean.
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
