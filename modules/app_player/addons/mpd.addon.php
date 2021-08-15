<?php

/*
Addon MPD for app_player
*/

class mpd extends app_player_addon
{
    
    // Constructor
    function __construct($terminal)
    {
        $this->reset_properties();
        $this->title       = 'Music Player Daemon (MPD)';
        $this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука через кроссплатформенный музыкальный сервер, который имеет клиент-серверную архитектуру.<br>';
        $this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Да (если ТТС такого же типа, что и плеер).<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping ("пингование" проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 6600 (если по умолчанию, можно не указывать).';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        
        // Network
        $this->port     = (empty($this->terminal['PLAYER_PORT']) ? 6600 : $this->terminal['PLAYER_PORT']);
        $this->password = (empty($this->terminal['PLAYER_PASSWORD']) ? NULL : $this->terminal['PLAYER_PASSWORD']);
        
        // MPD
        include_once(DIR_MODULES . 'app_player/libs/mpd/mpd.class.php');
        $this->mpd = new mpd_player($this->terminal['HOST'], $this->port, $this->password);
        $this->mpd->Disconnect();
    }
    
    // ping mediaservice
    public function ping_mediaservice($host)
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
	
	
    // Set volume
    function set_volume($level = 0)
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->SetVolume($level)) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing volume';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing volume';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Set Repeat
    function set_repeat($repeat = 0)
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->SetRepeat($repeat)) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing repeat';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing repeat';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Set random
    function set_random($random = 0)
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->SetRandom($random)) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing random';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing random';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Set crossfade
    function set_crossfade($crossfade = 0)
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->SetCrossfade($crossfade)) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing crosfade';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing crosfade';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Play
    function play($input)
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($input && $this->mpd->connected) {
            try {
                $this->mpd->PLClear();
                if ($this->mpd->PLAddFile($input)) {
		    $this->mpd->Play();
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing play';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing play';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Pause
    function pause()
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->Pause()) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing play';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing play';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Stop
    function stop()
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->Stop()) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing stop';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing stop';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Next
    function next()
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->Next()) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing next';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing next';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Previous
    function previous()
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                if ($this->mpd->Previous()) {
                    $this->message = 'OK';
                    $this->success = TRUE;
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing Previous';
                }
            }
            catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing Previous';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // restore playlist
    function restore_playlist($playlist_id = 0, $playlist_content = array(), $track_id = 0, $time = 0, $state = 'stopped')
    {
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
        if ($this->mpd->connected) {
            try {
                // create new playlist
                $this->mpd->PLClear();
                // add files to playlist
                foreach ($playlist_content as $song) {
                    if (remote_file_exists($song['file'])) { 
                        $this->mpd->PLAddFileWithPosition($song['file'], $song['Pos']);
                    } else {
                        $out = parse_url($song['file']);
                        $path = isset($out['path']) ? $out['path'] : '';
                        $this->mpd->PLAddFileWithPosition($path, $song['Pos']);
                    }
                }
                // change played file
                $this->mpd->PLSeek($track_id, $time);
                // restore state player
                //stopped/playing/paused/unknown
                switch ($state) {
                    case 'playing':
                        if ($this->mpd->Play()) {
                            $this->success = TRUE;
                            $this->message = 'OK';
                        } else {
                            $this->success = FALSE;
                            $this->message = 'Missing restore playlist';
                        }
			break;
                    case 'paused':
                        if ($this->mpd->Play()) {
                            if ($this->mpd->Pause()) {
                                $this->success = TRUE;
                                $this->message = 'OK';
                            } else {
                                $this->success = FALSE;
                                $this->message = 'Missing restore playlist';
                            }
                        } else {
                            $this->success = FALSE;
                            $this->message = 'Missing restore playlist';
                        }
			break;
                    case 'stoped':
                        if ($this->mpd->Stop()) {
                            $this->success = TRUE;
                            $this->message = 'OK';
                        } else {
                            $this->success = FALSE;
                            $this->message = 'Missing restore playlist';
                        }
			break;
                    case 'unknown':
                        $this->success = TRUE;
                        $this->message = 'OK';
			break;
                    default:
                        $this->success = TRUE;
                        $this->message = 'OK';
			break;
                }
            } catch (Exception $e) {
                $this->success = FALSE;
                $this->message = 'Error ' . $e;
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Missing restore playlist';
        }
        if ($this->mpd->mpd_sock AND $this->mpd->connected)
            $this->mpd->Disconnect();
        return $this->success;
    }
    
    // Get player status
    function status()
    {
        $this->reset_properties();
        if (!$this->mpd->mpd_sock OR !$this->mpd->connected)
            $this->mpd->Connect();
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
        
        if ($this->mpd->connected) {
            $result = $this->mpd->GetStatus();
        }
        // получаем плейлист - возможно он не сохранен поэтому получаем его полностью
        if ($this->mpd->connected) {
            $playlist_content = $this->mpd->GetPlaylistinfo();
        }
	switch (strtolower($result['state'])) {
            case 'play':
                $result['state'] = 'playing';
                break;
            case 'pause':
                $result['state'] = 'paused';
                break;
            case 'stop':
                $result['state'] = 'stoped';
                break;
        }
        $this->data = array(
            'playlist_id' => (int) $result['playlist'], // номер или имя плейлиста 
            'playlist_content' => json_encode($playlist_content), 	// содержимое плейлиста должен быть ВСЕГДА МАССИВ 
									// обязательно $playlist_content[$i]['pos'] - номер трека
									// обязательно $playlist_content[$i]['file'] - адрес трека
									// возможно $playlist_content[$i]['Artist'] - артист
									// возможно $playlist_content[$i]['Title'] - название трека
            'track_id' => (int) $result['song'], //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'name' => (string) $name, //Current speed for playing media. float.
            'file' => (string) $file, //Current link for media in device. String.
            'length' => (int) $result['duration'], //Track length in seconds. Integer. If unknown = 0. 
            'time' => (int) $result['time'], //Current playback progress (in seconds). If unknown = 0. 
            'state' => (string) strtolower($result['state']), //Playback status. String: stopped/playing/paused/unknown 
            'volume' => (int) $result['volume'], // Volume level in percent. Integer. Some players may have values greater than 100.
            'muted' => (int) $result['muted'], // Volume level in percent. Integer. Some players may have values greater than 100.
            'random' => (int) $result['random'], // Random mode. Boolean. 
            'loop' => (int) $result['loop'], // Loop mode. Boolean.
            'repeat' => (int) $result['repeat'], //Repeat mode. Boolean.
            'crossfade' => (int) $result['xfade'], // crossfade
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
    
}

?>
