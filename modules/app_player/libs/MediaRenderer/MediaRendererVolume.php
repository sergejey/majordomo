<?php
/** AVTransport UPnP Class
 * Used for controlling renderers
 *
 * @author jalder
 */

class MediaRendererVolume {
    public function __construct($server) {
        // получаем айпи и порт устройства
        $url = parse_url($server);
        $this->ip = $url['host'];
        $this->port = $url['port'];

        // получаем XML
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        // загружаем xml
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);

        // получаем адрес управления устройством
        foreach($xml->device->serviceList->service as $service) {
            if ($service->serviceId == 'urn:upnp-org:serviceId:RenderingControl') {
                $chek_url = (substr($service->controlURL, 0, 1));
                $this->service_type = ($service->serviceType);
                if ($chek_url == '/') {
                    $this->ctrlurl = ($url['scheme'] . '://' . $this->ip . ':' . $this->port . $service->controlURL);
                } else {
                    $this->ctrlurl = ($url['scheme'] . '://' . $this->ip . ':' . $this->port . '/' . $service->controlURL);
                }
            }
        }
    }

	public function SetVolume($volume)
	{
		$response = $this->sendRequestToDevice('SetVolume',array('InstanceId' => 0,'Channel' => 'Master','DesiredVolume' => $volume));
		// создаем хмл документ
        $doc = new \DOMDocument();
		$doc->loadXML($response);
        //DebMes($response);
        if ($doc->getElementsByTagName('SetVolumeResponse ')) {
            return TRUE;
        } else {
            return FALSE;
        }
	}

	public function GetVolume()
	{
		return $this->sendRequestToDevice('GetVolume',array('InstanceId' => 0,'Channel' => 'Master'));
	}

	public function mute() {
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredMute' => 1);
		return $this->sendRequestToDevice('SetMute',$args);
	}
	public function unmute() {
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
        return $response;
    }
}
