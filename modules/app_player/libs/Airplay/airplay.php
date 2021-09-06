<?php
//Requires a http library, if you want you can copy and past it and include it all in one file if that suites your purposes
require_once 'httplib.php';

//This class you can use in any normal PHP program
//You'll have to  not exit the script if you want an image to remain
class AirPlay {
	const NONE = 'None';
	const SLIDE_LEFT = 'SlideLeft';
	const SLIDE_RIGHT = 'SlideRight';
	const DISSOLVE = 'Dissolve';
	var $http = null;
	var $hostname = null;
	var $port = 7000;
	
	function __construct($hostname, $port = 7000) {
		$this->hostname = $hostname;
		$this->port = $port;
	}
	function getHttp() {
		if ($this->http == null) {
			$this->http = new HTTPRequest('http://'.$this->hostname.':'.$this->port,true,10000);
		}
		return $this->http;
	}
	function sendphoto($file, $transition = AirPlay::NONE) {
		$http = $this->getHttp();
		$headers = array();
		$headers['User-Agent'] = 'MediaControl/1.0';
		$headers['X-Apple-Transition'] = $transition;
		$http->SetUri('/photo');
		$http->Post(file_get_contents($file));
	}
	function sendvideo($url, $position=0, $transition = AirPlay::NONE) {
		$http = $this->getHttp();
		$http->SetUri('/play');
		$headers = array();
		$headers['User-Agent'] = 'iTunes/10.6 (Macintosh; Intel Mac OS X 10.7.3) AppleWebKit/535.18.5';
		$headers['Content-Type'] = 'text/parameters';
		$http->Post('Start-Position: '.$position.' Content-Location: '.$url.'',0,$headers);
	}
	function stop() {
		$http = $this->getHttp();
		$http->SetUri('/stop');
		$http->Post('');
		$http->Close();
		$this->http = null;
	}
}


?>
