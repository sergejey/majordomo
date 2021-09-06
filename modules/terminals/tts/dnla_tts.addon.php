<?php

/*
Addon dnla for app_player
*/

class dnla_tts extends tts_addon
{
    function __construct($terminal)
    {
        $this->title       = 'Устройства с поддержкой протокола DLNA';
        $this->description = '<b>Описание:</b>&nbsp; Работает на устройствах поддерживающих протокол DNLA.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping (пингование проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Адрес управления вида http://ip:port/ (указывать не нужно, т.к. определяется автоматически и может отличаться для различных устройств).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';

        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
	    
	$this->setting = json_decode($this->terminal['TTS_SETING'], true);
        
        // proverka na otvet
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->setting['TTS_CONTROL_ADDRESS']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // автозаполнение поля PLAYER_CONTROL_ADDRESS при его отсутствии
        if ($retcode != 200 OR !stripos($content, 'AVTransport')) {
            // сделано специально для тех устройств которые периодически меняют свои порты и ссылки  на CONTROL_ADDRESS
            $this->setting['TTS_CONTROL_ADDRESS'] = $this->search($this->terminal['HOST']);
            if ($this->setting['TTS_CONTROL_ADDRESS']) {
                $terminal['TTS_SETING'] = json_encode($this->setting);
                if (is_string($terminal['TTS_SETING'])) {
					sg($this->terminal['LINKED_OBJECT'].'.UPNP_CONTROL_ADDRESS',$this->setting['TTS_CONTROL_ADDRESS']);
                    SQLUpdate('terminals', $terminal); // update
                }
            }
        }
        include_once(DIR_MODULES . 'app_player/libs/MediaRenderer/MediaRenderer.php');
        include_once(DIR_MODULES . 'app_player/libs/MediaRenderer/MediaRendererVolume.php');
        $this->remote = new MediaRenderer($this->setting['TTS_CONTROL_ADDRESS']);
        $this->remotevolume = new MediaRendererVolume($this->setting['TTS_CONTROL_ADDRESS']);  
    }
 
    // Say
    function say_media_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        $outlink = $message['CACHED_FILENAME'];
        // берем ссылку http
        if (preg_match('/\/cms\/cached.+/', $outlink, $m)) {
            $server_ip = getLocalIp();
            if (!$server_ip) {
                DebMes("Server IP not found", 'terminals');
                return false;
            } else {
                $message_link = 'http://' . $server_ip . $m[0];
            }
        }
        $response = $this->remote->play($message_link);
        if (stristr($response, 'PlayResponse')) {
            $this->success = TRUE;
            sleep($message['MESSAGE_DURATION']);
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }

    // Set volume
    function set_volume($level)
    {
        $response = $this->remotevolume->SetVolume($level);
        if ($response) {
            $this->success = TRUE;
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
    
    // ping terminal
    function ping_ttsservice($host)
    {
        // proverka na otvet
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->setting['TTS_CONTROL_ADDRESS']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($retcode != 200 OR !stripos($content, 'AVTransport')) {
            $this->success = FALSE;
        } else {
            $this->success = TRUE;
        }
        return $this->success;
    }
	
    // функция автозаполнения поля PLAYER_CONTROL_ADDRESS при его отсутствии
    private function search($ip = '239.255.255.250')
    {
        if (!$ip) {
            return false;
        }
        //create the socket
        $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true);
        //all
        $request = 'M-SEARCH * HTTP/1.1' . "\r\n";
        $request .= 'HOST: 239.255.255.250:1900' . "\r\n";
        $request .= 'MAN: "ssdp:discover"' . "\r\n";
        $request .= 'MX: 2' . "\r\n";
        $request .= 'ST: ssdp:all' . "\r\n";
        $request .= 'USER-AGENT: Majordomo/ver-x.x UDAP/2.0 Win/7' . "\r\n";
        $request .= "\r\n";
        
        @socket_sendto($socket, $request, strlen($request), 0, $ip, 1900);
        
        // send the data from socket
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => '1', 'usec' => '128'));
        do {
            $buf = null;
            if (($len = @socket_recvfrom($socket, $buf, 2048, 0, $ip, $port)) == -1) {
                echo "socket_read() failed: " . socket_strerror(socket_last_error()) . "\n";
            }
            if (!is_null($buf)) {
                $messages = explode("\r\n", $buf);
                foreach ($messages as $row) {
                    if (stripos($row, 'loca') === 0 and stripos($row, $this->terminal['HOST'])) {
                        $response = str_ireplace('location: ', '', $row);
                        $out = str_ireplace('location:', '', $response);
                    }
                    if (stripos($row, 'AVTransport')) {
                        $out = $response;
                        break;
                    }
                }
            }
        } while (!is_null($buf));
        socket_close($socket);
        return $out;
    }
	// Get terminal status
    function terminal_status()
    {
        // Defaults
        $listening_keyphrase = -1;
		$volume_media        = -1;
        $volume_ring         = -1;
        $volume_alarm        = -1;
        $volume_notification = -1;
        $brightness_auto     = -1;
        $recognition         = -1;
        $fullscreen          = -1;
        $brightness          = -1;
        $display_state       = -1;
        $battery             = -1;
	    
	// создаем хмл документ
        $doc          = new \DOMDocument();
        //  для получения уровня громкости
        $response     = $this->remotevolume->GetVolume();
        $doc->loadXML($response);
        $volume_media = $doc->getElementsByTagName('CurrentVolume')->item(0)->nodeValue;
		
        $out_data = array(
                'listening_keyphrase' =>(string) strtolower($listening_keyphrase), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
                'volume_media' => (int)$volume_media, // громкость медиа на терминале (-1 - не поддерживается терминалом)
                'volume_ring' => (int)$volume_ring, // громкость звонка к пользователям на терминале (-1 - не поддерживается терминалом)
                'volume_alarm' => (int)$volume_alarm, // громкость аварийных сообщений на терминале (-1 - не поддерживается терминалом)
                'volume_notification' => (int)$volume_notification, // громкость простых сообщений на терминале (-1 - не поддерживается терминалом)
                'brightness_auto' => (int) $brightness_auto, // автояркость включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
                'recognition' => (int) $recognition, // распознавание на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
                'fullscreen' => (int) $recognition, // полноекранный режим на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
                'brightness' => (int) $brightness, // яркость екрана (-1 - не поддерживается терминалом)
                'battery' => (int) $battery, // заряд акумулятора терминала в процентах (-1 - не поддерживается терминалом)
                'display_state'=> (int) $display_state, // 1, 0  - состояние дисплея (-1 - не поддерживается терминалом)
            );
		
        // удаляем из массива пустые данные
        foreach ($out_data as $key => $value) {
            if ($value == '-1') unset($out_data[$key]); ;
        }
        return $out_data;
    }
    
    // Say
    public function play_media ($link)
    {
        // проверяем айпи
        $server_ip = getLocalIp();
        if (!$server_ip) {
            DebMes("Server IP not found", 'terminals');
            return false;
        }
        // превращаем в адрес 
        $file_link = 'http://' . $server_ip . '/' . str_ireplace(ROOT, "", $link);
        $response = $this->remote->play($file_link);
        if (stristr($response, 'PlayResponse')) {
            $this->success = TRUE;
            sleep(2);
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
}
?>
