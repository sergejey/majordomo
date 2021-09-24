<?php
/*
Addon VLC HTTP for app_player
*/

class vlcweb extends app_player_addon
{
    
    // Private properties
    private $curl;
    private $address;
    
    // Constructor
    function __construct($terminal)
    {
        $this->title       = 'VLC через HTTP';
        $this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука через VideoLAN Client (VLC). Управление VLC производится по протоколу HTTP (используется веб интерфейс).<br>';
        $this->description .= 'Воспроизведение видео на терминале этого типа пока не поддерживается.<br>';
        $this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Да (если ТТС такого же типа, что и плеер).<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Не забудьте активировать HTTP интерфейс в настройках VLC<br>';
        $this->description .= '(Инструменты -> Настройки -> Все -> Основные интерфейсы -> Дополнительные модули интерфейса -> Web)<br>';
        $this->description .= 'и установить для него пароль (Основные интерфейсы -> Lua -> HTTP -> Пароль).';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
        $this->reset_properties();
        
        $this->address = 'http://' . $this->terminal['HOST'] . ':' . (empty($this->terminal['PLAYER_PORT']) ? 8080 : $this->terminal['PLAYER_PORT']);
        
    }
    
    // ping mediaservise
    public function ping_mediaservice($host)
    {
        $this->reset_properties();
        $connection = @fsockopen($this->terminal['HOST'],$this->terminal['PLAYER_PORT'],$errno,$errstr,1);
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
        if ($this->terminal['PLAYER_USERNAME'] || $this->terminal['PLAYER_PASSWORD']) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->terminal['PLAYER_USERNAME'] . ':' . $this->terminal['PLAYER_PASSWORD']);
        }
        curl_setopt($curl, CURLOPT_URL, $this->address . '/requests/' . $path . (strlen($params) ? '?' . $params : ''));
        
        if ($result = curl_exec($curl)) {
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($code) {
                case 200:
                    $this->success = true;
                    $this->message = 'OK';
                    $this->data    = $result;
                    break;
                case 401:
                    $this->success = false;
                    $this->message = 'Authorization failed!';
                    break;
                default:
                    $this->success = false;
                    $this->message = 'Unknown error (code ' . $code . ')!';
            }
        } else {
            $this->success = false;
            $this->message = 'VLC HTTP interface not available!';
        }
        curl_close($curl);
        return $this->success;
    }
    
    // Private: VLC-WEB parse XML
    private function vlcweb_parse_xml($data)
    {
        $this->reset_properties();
        try {
            if ($xml = @new SimpleXMLElement($data)) {
                $this->success = true;
                $this->message = 'OK';
                $this->data    = $xml;
            } else {
                $this->success = false;
                $this->message = 'SimpleXMLElement error!';
            }
        }
        catch (Exception $e) {
            $this->success = false;
            $this->message = $e->getMessage();
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
        
        if ($this->vlcweb_request('status.xml')) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $xml = $this->data;
            }
        }
        
        if ($this->pl_get()) {
            $playlist_content = $this->data;
        }
        
        $this->data = array(
            'playlist_id' => (int) 1, // номер или имя плейлиста
            'playlist_content' => json_encode($playlist_content), // содержимое плейлиста должен быть ВСЕГДА МАССИВ
            // обязательно $playlist_content[$i]['pos'] - номер трека
            // обязательно $playlist_content[$i]['file'] - адрес трека
            // возможно $playlist_content[$i]['Artist'] - артист
            // возможно $playlist_content[$i]['Title'] - название трека
            'track_id' => (int) $xml->currentplid, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'name' => (string) $name, //Current speed for playing media. float.
            'file' => (string) $file, //Current link for media in device. String.
            'length' => (int) $xml->length, //Track length in seconds. Integer. If unknown = 0.
            'time' => (int) $xml->time, //Current playback progress (in seconds). If unknown = 0.
            'state' => (string) strtolower($xml->state), //Playback status. String: stopped/playing/paused/unknown
            'volume' => (int) round((int) $xml->volume * 100 / 256), // Volume level in percent. Integer. Some players may have values greater than 100.
            'muted' => (int) $random, // Volume level in percent. Integer. Some players may have values greater than 100.
            'random' => (int) $xml->random == 'true' ? 1 : 0, // Random mode. Boolean.
            'loop' => (int) $xml->loop == 'true' ? 1 : 0, // Loop mode. Boolean.
            'repeat' => (int) $xml->repeat == 'true' ? 1 : 0, //Repeat mode. Boolean.
            'crossfade' => (int) $crossfade, // crossfade
            'speed' => (int) $speed // crossfade
            
        );
        
        // удаляем из массива пустые данные
        foreach ($this->data as $key => $value) {
            if ($value == '-1' or !$value)
                unset($this->data[$key]);
        }
        
        $this->success = true;
        $this->message = 'OK';
        return $this->success;
    }
    
    // Play
    function play($input)
    {
        $this->reset_properties();
        if (strlen($input)) {
            $input = preg_replace('/\\\\$/is', '', $input);
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'in_play',
                'input' => $input
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Input is missing!';
        }
        return $this->success;
    }
    
    // Pause
    function pause()
    {
        if ($this->vlcweb_request('status.xml', array(
            'command' => 'pl_pause'
        ))) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $this->reset_properties();
                $this->success = true;
                $this->message = 'OK';
            }
        }
        return $this->success;
    }
    
    // Stop
    function stop()
    {
        if ($this->vlcweb_request('status.xml', array(
            'command' => 'pl_stop'
        ))) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $this->reset_properties();
                $this->success = true;
                $this->message = 'OK';
            }
        }
        return $this->success;
    }
    
    // Next
    function next()
    {
        if ($this->vlcweb_request('status.xml', array(
            'command' => 'pl_next'
        ))) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $this->reset_properties();
                $this->success = true;
                $this->message = 'OK';
            }
        }
        return $this->success;
    }
    
    // Previous
    function previous()
    {
        if ($this->vlcweb_request('status.xml', array(
            'command' => 'pl_previous'
        ))) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $this->reset_properties();
                $this->success = true;
                $this->message = 'OK';
            }
        }
        return $this->success;
    }
    
    // Seek
    function seek($position)
    {
        $this->reset_properties();
        if (strlen($position)) {
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'seek',
                'val' => (int) $position
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Position is missing!';
        }
        return $this->success;
    }
    
    // Set volume
    function set_volume($level)
    {
        $this->reset_properties();
        if (strlen($level)) {
            $level = round((int) $level * 256 / 100);
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'volume',
                'val' => (int) $level
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Level is missing!';
        }
        return $this->success;
    }
    
    // Playlist: Get
    function pl_get()
    {
        if ($this->vlcweb_request('playlist.xml')) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $xml = $this->data;
                $this->reset_properties();
                $this->success = true;
                $this->message = 'OK';
                foreach ($xml->node[0] as $item) {
                    $this->data[] = array(
                        'pos' => (int) $item['id'],
                        'Title' => (string) $item['name'],
                        'file' => (string) $item['uri']
                    );
                }
            }
        }
        return $this->success;
    }
    
    // Playlist: Add
    function pl_add($input)
    {
        $this->reset_properties();
        if (strlen($input)) {
            $input = preg_replace('/\\\\$/is', '', $input);
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'in_enqueue',
                'input' => $input
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Input is missing!';
        }
        return $this->success;
    }
    
    // Playlist: Delete
    function pl_delete($id)
    {
        $this->reset_properties();
        if (strlen($id)) {
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'pl_delete',
                'id' => (int) $id
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Id is missing!';
        }
        return $this->success;
    }
    
    // Playlist: Empty
    function pl_empty()
    {
        if ($this->vlcweb_request('status.xml', array(
            'command' => 'pl_empty'
        ))) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $this->reset_properties();
                $this->success = true;
                $this->message = 'OK';
            }
        }
        return $this->success;
    }
    
    // Playlist: Play
    function pl_play($id)
    {
        $this->reset_properties();
        if (strlen($id)) {
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'pl_play',
                'id' => (int) $id
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Id is missing!';
        }
        return $this->success;
    }
    
    // Playlist: Sort
    function pl_sort($order)
    {
        $this->reset_properties();
        if (strlen($order)) {
            $order = explode(':', $order);
            switch ($order[0]) {
                case 'name':
                    $order[0] = 1;
                    break;
                case 'author':
                    $order[0] = 3;
                    break;
                case 'random':
                    $order[0] = 5;
                    break;
                case 'track':
                    $order[0] = 7;
                    break;
                default:
                    $order[0] = 0;
                    // id
                    
            }
            $order[1] = (isset($order[1]) && $order[1] == 'desc' ? 1 : 0);
            if ($this->vlcweb_request('status.xml', array(
                'command' => 'pl_sort',
                'id' => (int) $order[1],
                'val' => (int) $order[0]
            ))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        } else {
            $this->success = false;
            $this->message = 'Order is missing!';
        }
        return $this->success;
    }
    
    // Playlist: Random
    function set_random($data=0)
    {
        // получаем статус все данные будут в $this->data
        $this->status();
        // если состояние плеера НЕ такое же как и запрашиваемое состояние то меняем его
        if (($this->data['random'] AND !$data) OR (!$this->data['random'] AND $data)) {
            if ($this->vlcweb_request('status.xml', array('command' => 'pl_random'))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            } else {
                $this->reset_properties();
                $this->success = false;
                $this->message = 'Can not set random';
            }
        } else {
            // НИЧЕГО не делаем поскльку состояние такое же
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Playlist: Loop
    function set_loop($data=0)
    {
        // получаем статус все данные будут в $this->data
        $this->status();
        // если состояние плеера НЕ такое же как и запрашиваемое состояние то меняем его
        if (($this->data['loop'] AND !$data) OR (!$this->data['loop'] AND $data)) {
            if ($this->vlcweb_request('status.xml', array('command' => 'pl_loop'))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            } else {
                $this->reset_properties();
                $this->success = false;
                $this->message = 'Can not set loop';
            }
        } else {
            // НИЧЕГО не делаем поскльку состояние такое же
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Playlist: Repeat
    function set_repeat($data=0)
    {
        // получаем статус все данные будут в $this->data
        $this->status();
        // если состояние плеера НЕ такое же как и запрашиваемое состояние то меняем его
        if (($this->data['repeat'] AND !$data) OR (!$this->data['repeat'] AND $data)) {
            if ($this->vlcweb_request('status.xml', array('command' => 'pl_repeat'))) {
                if ($this->vlcweb_parse_xml($this->data)) {
                    $this->reset_properties();
                    $this->success = true;
                    $this->message = 'OK';
                }
            } else {
                $this->reset_properties();
                $this->success = false;
                $this->message = 'Can not set loop';
            }
        } else {
            // НИЧЕГО не делаем поскльку состояние такое же
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Default command
    function command($command, $parameter)
    {
        if ($this->vlcweb_request('vlm_cmd.xml', array(
            'command' => $command . (strlen($parameter) ? ' ' . $parameter : '')
        ))) {
            if ($this->vlcweb_parse_xml($this->data)) {
                $xml = $this->data;
                $this->reset_properties();
                if (strlen((string) $xml->error)) {
                    $this->success = false;
                    $this->message = (string) $xml->error;
                } else {
                    $this->success = true;
                    $this->message = 'OK';
                }
            }
        }
        return $this->success;
    }
    
    // restore playlist
    function restore_playlist($playlist_id = 0, $playlist_content = array(), $track_id = 0, $time = 0, $state = 'stopped')
    {
        // cleare playlist
        $this->vlcweb_request('status.xml', array(
            'command' => 'pl_empty'
        ));
        
        // add files to playlist
        foreach ($playlist_content as $song) {
            $this->vlcweb_request('status.xml', array(
                'command' => 'in_enqueue',
                'input' => $song['file']
            ));
            if ($song['pos'] == $track_id)
                $track_url = $song['file'];
        }
        
        //  get playlist for seach id played file
        if ($this->pl_get()) {
            $playlist_content = $this->data;
        }
        // seech id file
        $found_key = array_search($track_url, array_column($playlist_content, 'file'));
        // get new id file in playlist
        $track_id  = $playlist_content[$found_key]['pos'];
        // seek to position
        $this->vlcweb_request('status.xml', array('command' => 'seek', 'val' => $time ));
        // restore state player
        //stopped/playing/paused/unknown
        switch ($state) {
            case 'playing':
                if ($this->vlcweb_request('status.xml', array(
                    'command' => 'pl_play',
                    'id' => $track_id
                ))) {
                    $this->success = TRUE;
                    $this->message = 'OK';
                } else {
                    $this->success = FALSE;
                    $this->message = 'Missing restore playlist';
                }
                break;
            case 'paused':
                if ($this->vlcweb_request('status.xml', array(
                    'command' => 'pl_play',
                    'id' => $track_id
                ))) {
                    if ($this->pause()) {
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
                if ($this->stop()) {
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
        }
        return $this->success;
    }
}
?>
