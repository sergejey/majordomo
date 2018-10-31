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
        $retcode = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
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

    private function instanceOnly ($command, $id = 0) {
        $args = array('InstanceID' => $id);
        return $this->sendRequestToDevice($command, $args);
    }

    private function sendRequestToDevice ($command, $arguments) {
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
        
        return $response;
    }

    public function play($url = "") {
         if ($url === "") {
            return self::unpause();
        }
 
        // neobhodimo ostanovit vosproizvedenie
        $this->instanceOnly('Stop');

        // proverem est li rashirenie
        $path_info = pathinfo($url);
        if ($path_info['extension']) {
            $urimetadata = $this->get_extfile(trim ($path_info['extension']));
        } else {
            $content_type = get_headers($url, 1)["Content-Type"];
            var_dump($content_type);
            // poluchaem zagolovki dlyz protokola i classa contenta massiv 'iteam' i 'httphead'
            $urimetadata = $this->get_urihead($content_type);
        }
        //var_dump($urimetadata);
        $MetaData ='&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;';
        $MetaData.='&lt;DIDL-Lite xmlns=&quot;urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/&quot; xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:sec=&quot;http://www.sec.co.kr/&quot; xmlns:upnp=&quot;urn:schemas-upnp-org:metadata-1-0/upnp/&quot;&gt;';
        $MetaData.='&lt;item id=&quot;0&quot; parentID=&quot;-1&quot; restricted=&quot;0&quot;&gt;';
        $MetaData.='&lt;upnp:class&gt;'.$urimetadata['item'].'&lt;/upnp:class&gt;';
        $MetaData.='&lt;dc:title&gt;Majordomo mesage&lt;/dc:title&gt;';
        $MetaData.='&lt;dc:creator&gt;Majordomoterminal&lt;/dc:creator&gt;';
        $MetaData.='&lt;res protocolInfo=&quot;'.$urimetadata['httphead'].'&quot;&gt;' . $url . '&lt;/res&gt;';
        $MetaData.='&lt;/item&gt;';
        $MetaData.='&lt;/DIDL-Lite&gt;';
        
        $args = array('InstanceID' => 0, 'CurrentURI' => '<![CDATA[' . $url . ']]>', 'CurrentURIMetaData' => $MetaData);
        $response = $this->sendRequestToDevice('SetAVTransportURI', $args);
        // wait for stream
        sleep(1);
        $args = array( 'InstanceID' => 0, 'Speed' => 1);
        $response = $this->sendRequestToDevice('Play', $args);
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
// dorabativaem
private function get_urihead($uri_head){
    $avmetadatauri = array(
    'video/avi'=>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/avi:DLNA.ORG_PN=PV_DIVX_DX50;DLNA.ORG_OP=11;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'video/x-ms-asf' =>        array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/x-ms-asf:DLNA.ORG_PN=MPEG4_P2_ASF_SP_G726;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'video/x-ms-wmv' =>        array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/x-ms-wmv:DLNA.ORG_PN=WMVMED_FULL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'video/mp4'=>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/mp4:*'),
    'video/mpeg' =>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/mpeg:DLNA.ORG_PN=MPEG_TS_SD_NA_ISO;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'video/mpeg2'=>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/mpeg2:DLNA.ORG_PN=MPEG_PS_PAL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'video/mp2t' =>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/mp2t:DLNA.ORG_PN=MPEG_TS_HD_NA;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'video/mp2p' =>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/mp2t:DLNA.ORG_PN=MPEG_TS_HD_NA;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'video/quicktime'=>        array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/quicktime:*'),
    'video/x-mkv'=>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/x-matroska:*'), 
    'video/3gpp' =>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/3gpp:*'),
    'video/x-flv'=>            array('item'=>'object.item.videoItem', 'httphead'=>'http-get:*:video/x-flv:*'),
    'audio/x-aac'=>            array('item'=>'object.item.audioItem.musicTrack', 'httphead'=>'http-get:*:audio/x-aac:*'),
    'audio/x-ac3'=>            array('item'=>'object.item.audioItem.musicTrack', 'httphead'=>'http-get:*:audio/x-ac3:DLNA.ORG_PN=AC3;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'audio/mpeg' =>            array('item'=>'object.item.audioItem.musicTrack', 'httphead'=>'http-get:*:audio/mpeg:DLNA.ORG_PN=MP3;DLNA.ORG_OP=11;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'application/ogg'=>        array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/x-ogg:*'),
    'audio/x-ms-wma' =>        array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/x-ms-wma:DLNA.ORG_PN=WMAFULL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'application/octet-stream' =>    array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/mpeg:DLNA.ORG_PN=MP3;DLNA.ORG_OP=11;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    /// provereno
    'audio/aacp'=>            array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:video/x-flv:*'),
    );
	
    return $avmetadatauri[$uri_head];
    }
// get headers with extends files
private function get_extfile($ext){
    $extmetadatauri = array(
    'avi'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/avi:DLNA.ORG_PN=PV_DIVX_DX50;DLNA.ORG_OP=11;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'asf'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/x-ms-asf:DLNA.ORG_PN=MPEG4_P2_ASF_SP_G726;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000 '), 
    'wmv'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/x-ms-wmv:DLNA.ORG_PN=WMVMED_FULL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'mp4'=>     array('item'=>'object.item.videoItem',        'httphead'=>'http-get:*:video/mp4:*'),
    'mpeg'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/mpeg:DLNA.ORG_PN=MPEG_PS_PAL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'mpeg_ts' => array('item'=>'object.item.videoItem',        'httphead'=>'http-get:*:video/mpeg:DLNA.ORG_PN=MPEG_TS_SD_NA_ISO;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000 '),
    'mpeg1' =>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/mpeg:DLNA.ORG_PN=MPEG1;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'mpeg2' =>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/mpeg2:DLNA.ORG_PN=MPEG_PS_PAL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'ts'  =>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/mp2t:DLNA.ORG_PN=MPEG_TS_HD_NA;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'mp2t' =>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/mp2t:DLNA.ORG_PN=MPEG_TS_HD_NA;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'mp2p' =>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/mp2t:DLNA.ORG_PN=MPEG_TS_HD_NA;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'), 
    'mov'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/quicktime:*'),
    'mkv'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/x-matroska:*'),
    '3gp'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/3gpp:*'), 
    'flv'=>     array('item'=>'object.item.videoItem',         'httphead'=>'http-get:*:video/x-flv:*'),
    'aac'=>     array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/x-aac:*'),
    'ac3'=>     array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/x-ac3:DLNA.ORG_PN=AC3;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    'ogg'=>     array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/x-ogg:*'),
    'wma'=>     array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/x-ms-wma:DLNA.ORG_PN=WMAFULL;DLNA.ORG_OP=00;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),
    // provereno
    'mp3'=>     array('item'=>'object.item.audioItem.audioBroadcast', 'httphead'=>'http-get:*:audio/mpeg:DLNA.ORG_PN=MP3;DLNA.ORG_OP=11;DLNA.ORG_CI=0;DLNA.ORG_FLAGS=01700000000000000000000000000000'),

    );

    return $extmetadatauri[$ext];
    }

}
