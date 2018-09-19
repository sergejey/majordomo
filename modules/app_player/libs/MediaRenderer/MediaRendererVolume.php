<?php
/** AVTransport UPnP Class
 * Used for controlling renderers
 *
 * @author jalder
 */

class MediaRendererVolume {
    public $ctrlurl;
    public $service_type;
    public function __construct($server) {
    $control_url = str_ireplace("Location:", "", $server);
    $xml=simplexml_load_file($control_url);
    foreach($xml->device->serviceList->service as $service){
          if($service->serviceId == 'urn:upnp-org:serviceId:RenderingControl'){
                $chek_url = (substr($service->controlURL,0,1));
                $this->service_type = ($service->serviceType);
                if ($chek_url == '/') {
                   $this->ctrlurl = ($this->baseUrl($control_url,True).$service->controlURL);
                 } else {
                    $this->ctrlurl = ($this->baseUrl($control_url,True).'/'.$service->controlURL);
                }
          }
         }
        }


	public function SetVolume($volume)
	{
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredVolume' => $volume);
echo ($this->service_type);
		return $this->upnp->sendRequestToDevice('SetVolume',$args,$this->ctrlurl,$this->service_type);
	}

	public function mute()
	{
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredMute' => 1);
		return $this->upnp->sendRequestToDevice('SetMute',$args,$this->ctrlurl,$this->service_type);
	}
	public function unmute()
	{
		$args = array('InstanceId' => 0,'Channel' => 'Master','DesiredMute' => 0);
		return $this->upnp->sendRequestToDevice('SetMute',$args,$this->ctrlurl,$this->service_type);
	}

		//this should be moved to the upnp and renderer model
	public function getControlURL($description_url, $service = 'RenderingControl')
	{
		$description = $this->getDescription($description_url);

		switch($service)
		{
			case 'RenderingControl':
				$serviceType = $this->service_type;
				break;
			default:
				$serviceType = $this->service_type;
				break;
		}

		foreach($description['device']['serviceList']['service'] as $service)
		{
			if($service['serviceType'] == $serviceType)
			{
				$url = parse_url($description_url);
				return $url['scheme'].'://'.$url['host'].':'.$url['port'].$service['controlURL'];
			}
		}
	}
private function sendRequestToDevice($method, $arguments, $url, $type, $hostIp = '127.0.0.1', $hostPort = '80')
    {
        $body  ='<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
        $body .='<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">';
        $body .='<s:Body>';
        $body .='<u:'.$method.' xmlns:u="'.$this->service_type.'">';
        foreach( $arguments as $arg=>$value ) {
            $body .='<'.$arg.'>'.$value.'</'.$arg.'>';
        }
        $body .='</u:'.$method.'>';
        $body .='</s:Body>';
        $body .='</s:Envelope>';
 
        $header = array(
			'Host: '.$this->getLocalIp().':'.$hostPort,
            'User-Agent: '.$this->user_agent, //fudge the user agent to get desired video format
            'Content-Length: ' . strlen($body),
			'Connection: close',
            'Content-Type: text/xml; charset="utf-8"',
			'SOAPAction: "'.$this->service_type.'#'.$method.'"',
        );

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, TRUE );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        $response = curl_exec( $ch );
        curl_close( $ch );
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        $result = $doc->getElementsByTagName('Result');
        if(is_object($result->item(0))){
            return $result->item(0)->nodeValue;
        }
        return $response;
    }
//получаем hostname адрес локального компьютера
    private function getLocalIp() { 
      return gethostbyname(trim(`hostname`)); 
    }
 private function baseUrl($url)
    {
        $url = parse_url($url);
        return $url['scheme'].'://'.$url['host'].':'.$url['port'];
    }

}
