<?php

// Base class to extend to provide functionality for different receivers

class CCBaseSender {
	
	public $chromecast; // The chromecast the initiated this instance.
	public $mediaid; // The media session id.
	
	public function __construct($hostchromecast) {
		$this->chromecast = $hostchromecast;
	}

	public function launch() {

		if (Defined('CHROMECAST_DEBUG') && CHROMECAST_DEBUG) {
			echo '<hr>Basic Launch ' . __FILE__ . ' ' . __LINE__ .' '. __METHOD__. str_repeat(' ', 2048);
			flush();
			flush();
		}

		// Launch the player or connect to an existing instance if one is already running
		// First connect to the chromecast

		$this->chromecast->transportid = "";
		$this->chromecast->cc_connect();
		$s = $this->chromecast->getStatus();
		// Grab the appid
		preg_match("/\"appId\":\"([^\"]*)/",$s,$m);
		$appid = $m[1];

		if ($this->chromecast->sessionid) {
			$this->mediaid=$this->chromecast->sessionid;
		}


		//$appid = 'undefined';

		if (Defined('CHROMECAST_DEBUG') && CHROMECAST_DEBUG) {
			echo '<hr>Curent app '.$appid. ' ' . __FILE__ . ' ' . __LINE__ .' '. __METHOD__. str_repeat(' ', 2048);
			flush();
			flush();
		}

		if ($appid == $this->appid) {
			if (Defined('CHROMECAST_DEBUG') && CHROMECAST_DEBUG) {
				echo '<hr>Mediareceiver already live ' . __FILE__ . ' ' . __LINE__ .' '. __METHOD__. str_repeat(' ', 2048);
				flush();
				flush();
			}

			// Default Media Receiver is live
			$this->chromecast->getStatus();
			$this->chromecast->connect();
		} else {
			if (Defined('CHROMECAST_DEBUG') && CHROMECAST_DEBUG) {
				echo '<hr>Starting media receiver ' . __FILE__ . ' ' . __LINE__ .' '. __METHOD__. str_repeat(' ', 2048);
				flush();
				flush();
			}
			// Default Media Receiver is not currently live, start it.
			$this->chromecast->launch($this->appid);
			$this->chromecast->transportid = "";
			$r = "";
			while (!preg_match("/Ready to Cast/",$r) && !preg_match("/Default Media Receiver/",$r)) {
				$r = $this->chromecast->getStatus();
				sleep(1);
			}
			$this->chromecast->connect();
		}


	}
	
}

?>