<?php
/** AVTransport UPnP Class
 * Used for controlling renderers
 *
 * @author jalder
 */

class MediaRendererVolume {
    public function __construct($server) {
        $crl = str_ireplace("Location:", "", $server);

        // получаем айпи и порт устройства
        $url = parse_url($crl);
        $this->ip = $url['host'];
        $this->port = $url['port'];

        // получаем XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $crl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);

        // proverka na otvet
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // если не получен ответ делаем поиск устройства по новой
	// сделано специально для тех устройств которые периодически меняют свои порты и ссылки 
        if ($retcode!=200) {
            $crl = $this->search($this->ip);
            // получаем айпи и порт устройства по новой
            $url = parse_url($crl);
            $this->ip = $url['host'];
            $this->port = $url['port'];
            // получаем XML по новой
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $crl);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);
            curl_close($ch);
            }

        // загружаем xml
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);

        // получаем адрес управления устройством
        foreach($xml->device->serviceList->service as $service) {
            if ($service->serviceId == 'urn:upnp-org:serviceId:RenderingControl') {
                $chek_url = (substr($service->controlURL, 0, 1));
                $this->service_type = ($service->serviceType);
                if ($chek_url == '/') {
                    $this->ctrlurl = ($url['scheme'] . '://' . $url['host'] . ':' . $url['port'] . $service->controlURL);
                } else {
                    $this->ctrlurl = ($url['scheme'] . '://' . $url['host'] . ':' . $url['port'] . '/' . $service->controlURL);
                }
            }
        }
    }

	public function SetVolume($volume)
	{
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredVolume' => $volume);
		return $this->sendRequestToDevice('SetVolume',$args);
	}

	public function mute()
	{
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredMute' => 1);
		return $this->sendRequestToDevice('SetMute',$args);
	}
	public function unmute()
	{
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredMute' => 0);
		return $this->sendRequestToDevice('SetMute',$args);
	}

    private function sendRequestToDevice($command, $arguments) {
        $body = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>'."\r\n";
        $body.= '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">';
        $body.= '<s:Body>';
        $body.= '<u:' . $command . ' xmlns:u="' . $this->service_type . '">';
        foreach($arguments as $arg => $value) {
            $body.= '<' . $arg . '>' . $value . '</' . $arg . '>';
        }

        $body.= '</u:' . $command . '>';
        $body.= '</s:Body>';
        $body.= '</s:Envelope>';
        $header = array(
            'Host: ' . $this->ip . ':' . $this->port,
            'User-Agent: Majordomo/ver-x.x UDAP/2.0 Win/7', //fudge the user agent to get desired video format
            'Content-Length: ' . strlen($body) ,
            'Connection: close',
            'Content-Type: text/xml; charset="utf-8"',
            'SOAPAction: "' . $this->service_type . '#' . $command . '"',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $this->ctrlurl);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);
        curl_close($ch);
        // создает документ хмл
        $doc = new \DOMDocument();
        //  загружет его
        $doc->loadXML($response);
        //  выбирает поле соответсвтуещее
        $result = $doc->getElementsByTagName($command.'Response');
        if(is_object($result->item(0))){
          return $command.' ok';
        }
        
        return $result;
    }

    
    // функция получения CONTROL_ADDRESS при его отсутствии или его ге правильности
     private function search($ip = '255.255.255.255') {
        //create the socket
        $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true);
        //all
        $request  = 'M-SEARCH * HTTP/1.1'."\r\n";
        $request .= 'HOST: 239.255.255.250:1900'."\r\n";
        $request .= 'MAN: "ssdp:discover"'."\r\n";
        $request .= 'MX: 2'."\r\n";
        $request .= 'ST: ssdp:all'."\r\n";
        $request .= 'USER-AGENT: Majordomo/ver-x.x UDAP/2.0 Win/7'."\r\n";
        $request .= "\r\n";
        
        socket_sendto($socket, $request, strlen($request), 0, $ip, 1900);
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
                         if( stripos( $row, 'loca') === 0 ) {
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
