<?php
/** AVTransport UPnP Class
 * Used for controlling renderers
 *
 * @author jalder
 */

class MediaRenderer
{
    public function __construct($server)
    {
        if (!$server) {
            return;
        }
        // получаем XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        if (!$content) {
			return;
		}

        // получаем айпи и порт устройства
        $url        = parse_url($server);
        $this->ip   = $url['host'];
        $this->port = $url['port'];

        // загружаем xml
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        // получаем адрес управления устройством
        foreach ($xml->device->serviceList->service as $service) {
            if ($service->serviceId == 'urn:upnp-org:serviceId:AVTransport') {
                $chek_url           = (substr($service->controlURL, 0, 1));
                $this->service_type = ($service->serviceType);
                if ($chek_url == '/') {
                    $this->ctrlurl = ($url['scheme'] . '://' . $this->ip . ':' . $this->port . $service->controlURL);
                } else {
                    $this->ctrlurl = ($url['scheme'] . '://' . $this->ip . ':' . $this->port . '/' . $service->controlURL);
                }
            }
            if ($service->serviceId == 'urn:upnp-org:serviceId:ConnectionManager') {
                $chek_url           = (substr($service->controlURL, 0, 1));
                $this->conn_manager = ($service->serviceType);
                if ($chek_url == '/') {
                    $this->conn_url = ($url['scheme'] . '://' . $this->ip . ':' . $this->port . $service->controlURL);
                } else {
                    $this->conn_url = ($url['scheme'] . '://' . $this->ip . ':' . $this->port . '/' . $service->controlURL);
                }
            }
        }
        
		// получаем все значения необходиміе для постройки правильного урла
        $body = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
        $body .= '<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">';
        $body .= '<s:Body>';
        $body .= '<u:GetProtocolInfo xmlns:u="' . $this->conn_manager . '" />';
        $body .= '</s:Body>';
        $body .= '</s:Envelope>';
        
        $header = array(
            'Host: ' . $this->ip . ':' . $this->port,
            'User-Agent: Majordomo/ver-x.x UDAP/2.0 Win/7', //fudge the user agent to get desired video format
            'Content-Length: ' . strlen($body),
            'Connection: close',
            'Content-Type: text/xml; charset="utf-8"',
            'SOAPAction: "' . $this->conn_manager . '#GetProtocolInfo"'
        );
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $this->conn_url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);
        curl_close($ch);
          $doc = new \DOMDocument();
        $doc->loadXML($response);

        $this->all_extension = explode(",", $doc->getElementsByTagName('GetProtocolInfoResponse')->item(0)->nodeValue);
    }
    
    private function instanceOnly($command, $id = 0)
    {
        $args = array(
            'InstanceID' => $id
        );
        return $this->sendRequestToDevice($command, $args);
    }
    
    private function sendRequestToDevice($command, $arguments)
    {
        $body = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>' . "\r\n";
        $body .= '<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">';
		
        $body .= '<s:Body>';
        $body .= '<u:' . $command . ' xmlns:u="' . $this->service_type . '">';
        foreach ($arguments as $arg => $value) {
            $body .= '<' . $arg . '>' . $value . '</' . $arg . '>';
        }
        
        $body .= '</u:' . $command . '>';
        $body .= '</s:Body>';
        $body .= '</s:Envelope>';
        $header = array(
            'Host: ' . $this->ip . ':' . $this->port,
            'User-Agent: Majordomo/ver-x.x UDAP/2.0 Win/7', //fudge the user agent to get desired video format
            'Content-Length: ' . strlen($body),
            'Connection: close',
            'Content-Type: text/xml; charset="utf-8"',
            'SOAPAction: "' . $this->service_type . '#' . $command . '"'
        );
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $this->ctrlurl);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    
    public function play($url = "")
    {
        
        if ($url === "") {
            return $this->sendRequestToDevice('Play', $args = array('InstanceID' => 0,'Speed' => 1));
        }
        
        $this->instanceOnly('Stop');
        
        // berem Content-Type
        if ($fp = fopen($url, 'r')) {
            $meta = stream_get_meta_data($fp);
            if (is_array($meta['wrapper_data'])) {
                $items = $meta['wrapper_data'];
                foreach ($items as $line) {
                    if (preg_match('/Content-Type:(.+)/is', $line, $m)) {
                        $content_type = trim($m[1]);
                    }
                }
            }
            fclose($fp);
        }
        if ($content_type = 'application/octet-stream') {
            $content_type = 'audio/mpeg';
        }
        //DebMes('ct ' . $content_type);
        // proveryaem
        foreach($this->all_extension as $index => $urimetadata) {
            if (stripos($urimetadata, 'http-get:*:'.$content_type.':*') !== FALSE) {
                break ;
            } else if (stripos($urimetadata, 'http-get:*:'.$content_type.':') !== FALSE) {
                break ;
            }
        }

        $type_data = substr($content_type, 0, strpos($content_type, '/'));
        //DebMes($type_data);
        //DebMes ($urimetadata);
		
		//get all information about audiofile
		$info_data = get_media_info($url);

        $MetaData = '&lt;DIDL-Lite xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dlna="urn:schemas-dlna-org:metadata-1-0/" xmlns:sec="http://www.sec.co.kr/" xmlns:pv="http://www.pv.com/pvns/"&gt;';
        $MetaData .= '&lt;item id=&quot;0&quot; parentID=&quot;-1&quot; restricted=&quot;1&quot;&gt;';
        $MetaData .= '&lt;upnp:class&gt;object.item.' . $type_data . 'Item&lt;/upnp:class&gt;';
        $MetaData .= '&lt;dc:title&gt;Majordomo mesage&lt;/dc:title&gt;'; 
        $MetaData .= '&lt;dc:creator&gt;Majordomoterminal&lt;/dc:creator&gt;&lt;upnp:artist&gt;Majordomo&lt;/upnp:artist&gt;';
        $MetaData .= '&lt;upnp:genre&gt;Message&lt;/upnp:genre&gt;';
        $MetaData .= '&lt;upnp:albumArtURI dlna:profileID="JPEG_TN"&gt;http://'.getLocalIp().'/img/logo.png&lt;/upnp:albumArtURI&gt;';		
        $MetaData .= '&lt;res protocolInfo=&quot;' . $urimetadata . '&quot; size=&quot;'.get_remote_filesize($url).'&quot; sampleFrequency=&quot;'.$info_data['Audio_sample_rate'].'&quot; nrAudioChannels=&quot;'.$info_data['Audio_chanel'].'&quot; duration=&quot;' . gmdate('H:i:s', $info_data['duration']) . '&quot;&gt;' . $url . '&lt;/res&gt;';
        $MetaData .= '&lt;/item&gt;';
        $MetaData .= '&lt;/DIDL-Lite&gt;';
        //DebMes($MetaData);
        //&lt;res protocolInfo="http-get:*:audio/mpeg:*" size="1135829" bitsPerSample="16" sampleFrequency="44100" nrAudioChannels="2" bitrate="40565" duration="00:00:28.000"&gt;http://192.168.8.100:31415/play/21A51710_mime=audio!mpeg_bits=16_channels=2_rate=044100_duration=28.mp3&lt;/res&gt;

		
	    $response = $this->sendRequestToDevice('SetAVTransportURI', array('InstanceID' => 0,'CurrentURI' => '<![CDATA[' . $url . ']]>','CurrentURIMetaData' => $MetaData));
        
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        
        if (!$doc->getElementsByTagName('SetAVTransportURIResponse ')) {
           return $response;
        }
		
        $response = $this->sendRequestToDevice('Play', array('InstanceID' => 0,'Speed' => 1));
        // создаем хмл документ
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        
        if ($doc->getElementsByTagName('PlayResponse ')) {
            return $response;
        }
		

		return false;
    }
    
    public function setNext($url)
    {
        $tags = get_meta_tags($url);
        $args = array(
            'InstanceID' => 0,
            'NextURI' => '<![CDATA[' . $url . ']]>',
            'NextURIMetaData' => ''
        );
        return $this->sendRequestToDevice('SetNextAVTransportURI', $args);
    }
    
    public function getState()
    {
        return $this->instanceOnly('GetTransportInfo');
    }
    
    public function getPosition()
    {
        return $this->instanceOnly('getPositionInfo');
    }
    
    public function getMedia()
    {
        return $this->instanceOnly('GetMediaInfo');
    }
    
    public function stop()
    {
        $response = $this->instanceOnly('Stop');
        // создаем хмл документ
        $doc      = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if ($doc->getElementsByTagName('StopResponse ')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function pause()
    {
        $response = $this->getState();
        // создаем хмл документ
        $doc      = new \DOMDocument();
        $doc->loadXML($response);
        if ($doc->getElementsByTagName('CurrentTransportState')->item(0)->nodeValue == 'PLAYING') {
            $response = $this->instanceOnly('Pause');
        } else {
            $response = $this->sendRequestToDevice('Play', array( 'InstanceID' => 0,'Speed' => 1));
        }
        $doc->loadXML($response);
        //DebMes($response);
        if ($doc->getElementsByTagName('PauseResponse ') OR $doc->getElementsByTagName('PlayResponse ')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function next()
    {
        $response = $this->instanceOnly('Next');
        // создаем хмл документ
        $doc      = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if ($doc->getElementsByTagName('NextResponse ')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function previous()
    {
        $response = $this->instanceOnly('Previous');
        // создаем хмл документ
        $doc      = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if ($doc->getElementsByTagName('PreviousResponse ')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function seek($target = 0)
    {
        // преобразуем в часы минуты и секунды
        $hours    = floor($target / 3600);
        $minutes  = floor($target % 3600 / 60);
        $seconds  = $target % 60;
        $response = $this->sendRequestToDevice('Seek', array(
            'InstanceID' => 0,
            'Unit' => 'REL_TIME',
            'Target' => $hours . ':' . $minutes . ':' . $seconds
        ));
        // создаем хмл документ
        $doc      = new \DOMDocument();
        $doc->loadXML($response);
        //DebMes($response);
        if ($doc->getElementsByTagName('SeekResponse ')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    // функция преобразования в секунды времени
    public function parse_to_second($time)
    {
        $parsed  = date_parse($time);
        $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
        return $seconds;
    }
	
}
