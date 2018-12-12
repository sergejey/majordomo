<?php

/*
    Addon dnla for app_player
*/

class dnla extends app_player_addon {
    
    // Private properties
    private $curl;
    private $address;
    
    // Constructor
    function __construct($terminal) {
        $this->title = 'DNLA media player';
        $this->description = 'Проигрывание видео - аудио ';
        $this->description .= 'на всех устройства поддерживающих такой протокол. ';
        $this->terminal = $terminal;
        $this->reset_properties();

        // автозаполнение поля PLAYER_CONTROL_ADDRESS при его отсутствии
        if ($this->terminal['HOST'] and !$this->terminal['PLAYER_CONTROL_ADDRESS']) {
            $rec=SQLSelectOne('SELECT * FROM terminals WHERE HOST="'.$this->terminal['HOST'].'"');
            $this->terminal['PLAYER_CONTROL_ADDRESS'] = $this->search($this->terminal['HOST']);
            $rec['PLAYER_CONTROL_ADDRESS'] = $this->terminal['PLAYER_CONTROL_ADDRESS'];
            if ($rec['HOST']) {
                SQLUpdate('terminals', $rec); // update
                DebMes('Добавлен адрес управления устройством - '.$rec['PLAYER_CONTROL_ADDRESS']);
            }
        } else {
            // сделано специально для тех устройств которые периодически меняют свои порты и ссылки  на CONTROL_ADDRESS
            // проверяем на правильность PLAYER_CONTROL_ADDRESS некоторые устройства могут их изменять
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->terminal['PLAYER_CONTROL_ADDRESS']);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);
    
            // proverka na otvet
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            // если не получен ответ делаем поиск устройства по новой
            if ($retcode!=200) {
                $rec=SQLSelectOne('SELECT * FROM terminals WHERE HOST="'.$this->terminal['HOST'].'"');
                $this->terminal['PLAYER_CONTROL_ADDRESS'] = $this->search();
                if ($this->terminal['PLAYER_CONTROL_ADDRESS']){}
                $rec['PLAYER_CONTROL_ADDRESS'] = $this->terminal['PLAYER_CONTROL_ADDRESS'];
                if (is_string($rec['PLAYER_CONTROL_ADDRESS'])) {
                    SQLUpdate('terminals', $rec); // update
                    DebMes('Добавлен адрес управления устройством - '.$rec['PLAYER_CONTROL_ADDRESS']);
                    }
                }
            }

        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/MediaRenderer.php');
        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/MediaRendererVolume.php');
        }
    

    // Get player status
    function status() {
        // Defaults
        $track_id      = -1;
        $length        = 0;
        $time          = 0;
        $state         = 'unknown';
        $volume        = 0;
        $random        = FALSE;
        $loop          = FALSE;
        $repeat        = FALSE;
        $current_speed = 1;
        $curren_url    = '';
        
        // создаем хмл документ
        $doc = new \DOMDocument();
        //  для получения уровня громкости
        $remotevolume = new MediaRendererVolume($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remotevolume->GetVolume();
        $doc->loadXML($response);
        $volume = $doc->getElementsByTagName('CurrentVolume')->item(0)->nodeValue;
        // Для получения состояния плеера
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->getState();
        $doc->loadXML($response);
        $state = $doc->getElementsByTagName('CurrentTransportState')->item(0)->nodeValue;
		if ($state == 'TRANSITIONING' ) {$state = 'playing';}
		//Debmes ('current_speed '.$current_speed);
        $response = $remote->getPosition();
        $doc->loadXML($response);
        $track_id = $doc->getElementsByTagName('Track')->item(0)->nodeValue;
        $length = $this->parse_to_second($doc->getElementsByTagName('TrackDuration')->item(0)->nodeValue);
        $time = $this->parse_to_second($doc->getElementsByTagName('RelTime')->item(0)->nodeValue);
        // Results
        if ($response) {
			$this->reset_properties();
            $this->success = TRUE;
            $this->message = 'OK';
            $this->data = array(
            'track_id'        => (int)$track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
            'length'          => (int)$length, //Track length in seconds. Integer. If unknown = 0. 
            'time'            => (int)$time, //Current playback progress (in seconds). If unknown = 0. 
            'state'           => (string)strtolower($state), //Playback status. String: stopped/playing/paused/unknown 
            'volume'          => (int)$volume, // Volume level in percent. Integer. Some players may have values greater than 100.
            'random'          => (boolean)$random, // Random mode. Boolean. 
            'loop'            => (boolean)$loop, // Loop mode. Boolean.
            'repeat'          => (boolean)$repeat, //Repeat mode. Boolean.
            );
		}
        return $this->success;    
    }

    // Play
    function play($input) {
        $this->reset_properties();
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        // для радио 101 ру
        if( stripos( $input, '?userid=0&setst') ) {
            $input = stristr($input, '&setst', True).'.mp4';
            }
        //DebMes('Ссылка '.$input.' подана на терминал - '.$this->terminal['NAME']);
        $response = $remote->play($input);
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('PlayResponse')) {
            $this->success = TRUE;
            $this->message = 'Play files';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    // Stop
    function stop() {
        $this->reset_properties();
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->stop();
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('StopResponse ')) {
            $this->success = TRUE;
            $this->message = 'Stop play';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    // Pause
    function pause() {
        $this->reset_properties();
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->pause();
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('PauseResponse ')) {
            $this->success = TRUE;
            $this->message = 'Pause enabled';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }
    
    // Next
    function next() {
        $this->reset_properties();
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->next();
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('NextResponse')) {
            $this->success = TRUE;
            $this->message = 'Next file changed';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }
    
    // Previous
    function previous() {
        $this->reset_properties();
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->previous();
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('PreviousResponse')) {
            $this->success = TRUE;
            $this->message = 'Previous file changed';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    // Seek
    function seek($position) {
        $this->reset_properties();
		// преобразуем в часы минуты и секунды
        $hours = floor($position / 3600);
        $minutes = floor($position % 3600 / 60);
        $seconds = $position % 60;

		DebMes($hours.':'.$minutes.':'.$seconds);
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->seek($hours.':'.$minutes.':'.$seconds);
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('SeekResponse')) {
            $this->success = TRUE;
            $this->message = 'Position changed';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }
    // Set volume
    function set_volume($level) {
        $this->reset_properties();
        $remotevolume = new MediaRendererVolume($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remotevolume->SetVolume($level);
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if($doc->getElementsByTagName('SetVolumeResponse')) {
            DebMes('Изменена громкость на терминале - '.$this->terminal['NAME'].' установлен уровень '.$level);
            $this->success = TRUE;
            $this->message = 'Volume changed';
         } else {
            DebMes('Громкость на терминале - '.$this->terminal['NAME'].' НЕ ИЗМЕНЕНА ОШИБКА!');
            $this->success = FALSE;
            $this->message = 'Command execution error!';
        }
        return $this->success;
    }  

    // Get media volume level
    function get_volume() {
            $this->success = FALSE;
            $this->message = 'Command execution error!';        
            if($this->status()) {
            $volume = $this->data['volume'];
            $this->success = TRUE;
            $this->message = 'Volume get';
            $this->data = $volume;
        } else if (strtolower($this->terminal['HOST']) == 'localhost' || $this->terminal['HOST'] == '127.0.0.1') {
            $this->reset_properties(array('success'=>TRUE, 'message'=>'OK'));
            $this->data = (int)getGlobal('ThisComputer.volumeMediaLevel');
            $this->success = TRUE;
            $this->message = 'Volume get';
        } else {
            // создаем хмл документ
            $doc = new \DOMDocument();
            //  для получения уровня громкости
            $remotevolume = new MediaRendererVolume($this->terminal['PLAYER_CONTROL_ADDRESS']);
            $response = $remotevolume->GetVolume();
            $doc->loadXML($response);
            $this->data = $doc->getElementsByTagName('CurrentVolume')->item(0)->nodeValue;
            if ($this->data) {
                $this->success = TRUE;
                $this->message = 'Volume get';
            } else {
            DebMes('Громкость на терминале - '.$this->terminal['NAME'].' НЕ ПОЛУЧЕНА ОШИБКА!');
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        }
        return $this->success;
    }
    
    // Playlist: Get
    function pl_get() {
        $this->success = FALSE;
        $this->message = 'Command execution error!';    
        $track_id      = -1;
        $name          = 'unknow';
        $curren_url    = '';

        // создаем хмл документ
        $doc = new \DOMDocument();
        // Для получения состояния плеера
        $remote = new MediaRenderer($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $response = $remote->getPosition();
        $doc->loadXML($response);
        $track_id = $doc->getElementsByTagName('Track')->item(0)->nodeValue;
        $name = 'Played url om the device';
        $curren_url = $doc->getElementsByTagName('TrackURI')->item(0)->nodeValue;
        if ($response) {
            // Results
            $this->reset_properties();
            $this->success = TRUE;
            $this->message = 'OK';
            $this->data = array(
                'id'        => (int)$track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
                'name'      => (string)$name, //Current speed for playing media. float.
                'file'      => (string)$curren_url, //Current link for media in device. String.
                );
        }
        return $this->success;    
    }
    
    // функция автозаполнения поля PLAYER_CONTROL_ADDRESS при его отсутствии
    private function search() {
        //create the socket
        $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true);

        //all
        $request = 'M-SEARCH * HTTP/1.1'."\r\n";
        $request .= 'HOST: 239.255.255.250:1900'."\r\n";
        $request .= 'MAN: "ssdp:discover"'."\r\n";
        $request .= 'MX: 2'."\r\n";
        $request .= 'ST: ssdp:all'."\r\n";
        $request .= 'USER-AGENT: Majordomo/ver-x.x UDAP/2.0 Win/7'."\r\n";
        $request .= "\r\n";
        
        @socket_sendto($socket, $request, strlen($request), 0, '255.255.255.255', 1900);
        @socket_sendto($socket, $request, strlen($request), 0, '239.255.255.250', 1900);

        // send the data from socket
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>'1', 'usec'=>'128'));
        $response = array();
        do {
            $buf = null;
            if (($len = @socket_recvfrom($socket, $buf, 2048, 0, $ip, $port)) == -1) {
                echo "socket_read() failed: " . socket_strerror(socket_last_error()) . "\n";
            }
            if(!is_null($buf)){
                $messages = explode("\r\n", $buf);
                    foreach( $messages as $row ) {
                        if( stripos( $row, 'AVTransport') ) {
                              break;
                         }
                        if( stripos( $row, 'loca') === 0 and stripos( $row, $this->terminal['HOST'])) {
                              $response = str_ireplace( 'location: ', '', $row );
                         }
                    }
            }
        } while(!is_null($buf));
        socket_close($socket);
        $response = str_ireplace("Location:", "", $response);
        return $response;
    } 

    // функция преобразования в секунды времени
    private function parse_to_second($time) {
        $parsed = date_parse($time);
        $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
        return $seconds;
    }
}
?>
