<?php
/** AVTransport UPnP Class
 * Used for controlling renderers
 *
 * @author jalder
 */

class MediaRenderer {
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
            if ($service->serviceId == 'urn:upnp-org:serviceId:AVTransport') {
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

    private function instanceOnly($command, $id = 0) {
        $args = array('InstanceID' => $id);
        return $this->sendRequestToDevice($command, $args);
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

    public function play($url = "") {
        // neobhodimo ostanovit vosproizvedenie
        $this->instanceOnly('Stop');
        if ($url === "") {
            return self::unpause();
        }
        $content_type = get_headers($url, 1)["Content-Type"];
        var_dump($content_type);
    	$MetaData='&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;DIDL-Lite xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dlna="urn:schemas-dlna-org:metadata-1-0/" xmlns:sec="http://www.sec.co.kr/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/"&gt;
&lt;item id="0" parentID="0" restricted="1"&gt;
&lt;upnp:class&gt;object.item.audioItem.musicTrack&lt;/upnp:class&gt;
&lt;dc:title&gt;Majordomo mesage&lt;/dc:title&gt;
&lt;dc:creator&gt;Majordomo terminal&lt;/dc:creator&gt;
&lt;upnp:artist&gt;tarasfrompir&lt;/upnp:artist&gt;
&lt;upnp:albumArtURI&gt;&lt;/upnp:albumArtURI&gt;
&lt;upnp:album&gt;Stream&lt;/upnp:album&gt;
&lt;res protocolInfo="http-get:*:'.$content_type.':DLNA.ORG_OP=00;DLNA.ORG_FLAGS=017000000000000 00000000000000000"&gt;' . $url . '&lt;/res&gt;
&lt;/item&gt;
&lt;/DIDL-Lite&gt;';
        $args = array('InstanceID' => 0, 'CurrentURI' => '<![CDATA[' . $url . ']]>', 'CurrentURIMetaData' => $MetaData);
        $response = $this->sendRequestToDevice('SetAVTransportURI', $args);
        var_dump($response);
        $args = array( 'InstanceID' => 0, 'Speed' => 1);
        $response = $this->sendRequestToDevice('Play', $args);
        var_dump($response);
	return $response;
    }

    public function seek($unit = 'TRACK_NR', $target = 0) {
        $response = $this->sendRequestToDevice('Seek', $args);
        return $response['s:Body']['u:SeekResponse'];
    }

    public function setNext($url) {
        $tags = get_meta_tags($url);
        $args = array(
            'InstanceID' => 0,
            'NextURI' => '<![CDATA[' . $url . ']]>',
            'NextURIMetaData' => ''
        );
        return $this->sendRequestToDevice('SetNextAVTransportURI', $args);
    }

    public function getState() {
        return $this->instanceOnly('GetTransportInfo');
    }

    public function getPosition() {
        return $this->instanceOnly('getPositionInfo');
    }

    public function getMedia() {
        $response = $this->instanceOnly('GetMediaInfo');

        // создает документ хмл
        $doc = new \DOMDocument();
        //  загружет его
        $doc->loadXML($response);
        //  выбирает поле соответсвтуещее
        $result = $doc->getElementsByTagName('CurrentURI');
        foreach($result as $item) {
            $track = $item->nodeValue;
        }

        return $track;
    }

    public function stop() {
        return $this->instanceOnly('Stop');
    }

    public function unpause() {
        $args = array(
            'InstanceID' => 0,
            'Speed' => 1
        );
        return $this->sendRequestToDevice('Play', $args);
    }

    public function pause() {
        return $this->instanceOnly('Pause');
    }

    public function next() {
        return $this->instanceOnly('Next');
    }

    public function previous() {
        return $this->instanceOnly('Previous');
    }

    public function fforward() {
        return $this->next();
    }

    public function rewind() {
        return $this->previous();
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
