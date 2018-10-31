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
                if ($rec['HOST']) {
                    SQLUpdate('terminals', $rec); // update
                    DebMes('Добавлен адрес управления устройством - '.$rec['PLAYER_CONTROL_ADDRESS']);
                    }
                }
            }

        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/MediaRenderer.php');
        include_once(DIR_MODULES.'app_player/libs/MediaRenderer/MediaRendererVolume.php');
        }
    

    // Play
    function play($input) {
        DebMes('Ссылка подана на плеер - '.$input);
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->play($input);
        DebMes($answer);
        if($answer) {
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
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->stop();
        if($answer) {
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
       $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->pause();
        if($answer) {
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
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->next();
        if($answer) {
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
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remote = new MediaRenderer($current_dev);
        $answer = $remote->previous();
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Previous file changed';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }

    
    // Set volume
    function set_volume($level) {
        $this->reset_properties();
        $current_dev = ($this->terminal['PLAYER_CONTROL_ADDRESS']);
        $current_dev = str_ireplace("Location:", "", $current_dev);
        $remotevolume = new MediaRendererVolume($current_dev);
        //DebMes($level);
        $answer = $remotevolume->SetVolume($level);
        //DebMes($this->success);
        if($answer) {
            $this->success = TRUE;
            $this->message = 'Volume changed';
         } else {
            $this->success = FALSE;
            $this->message = 'Command execution error!';
            }
        return $this->success;
    }  

    // функция автозаполнения поля PLAYER_CONTROL_ADDRESS при его отсутствии
     function search() {
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
        
        @socket_sendto($socket, $request, strlen($request), 0, '239.255.255.250', 1900);

        // send the data from socket
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>'2', 'usec'=>'128'));
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
}
?>
