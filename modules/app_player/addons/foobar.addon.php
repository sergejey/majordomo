<?php

/*
	Addon Foobar2000 for app_player
*/

class foobar extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'Foobar2000';
		$this->description = '<b>Описание:</b>&nbsp; Мощный медиаплеер, созданный одним из разработчиков WinAmp.<br>';
		$this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Не применимо (нет такого TTS плеера).<br>';
		$this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
		$this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 8888 (если по умолчанию, можно не указывать).';
				
		$this->terminal = $terminal;
		if (!$this->terminal['HOST'])
                     return false;
		
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?8888:$this->terminal['PLAYER_PORT']);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
	}
	
	// Destructor
	function destroy() {
		curl_close($this->curl);
	}

	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=EmptyPlaylist&param3=NoResponse');
			curl_exec($this->curl);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=Browse&param1='.urlencode($input).'&param2=EnqueueDirSubdirs&param3=NoResponse');
			curl_exec($this->curl);
			curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=Start&param1=0&param3=NoResponse');
			if($result = curl_exec($this->curl)) {
				$this->success = TRUE;
				$this->message = 'OK';
			} else {
				$this->success = FALSE;
				$this->message = 'HTTP interface not available!';
			}
		} else {
			$this->success = FALSE;
			$this->message = 'Input is missing!';
		}
		return $this->success;
	}
	
	// Pause
	function pause() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=PlayOrPause&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}

	// Stop
	function stop() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=Stop&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Next
	function next() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=StartNext&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}
	
	// Previous
	function previous() {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd=StartPrevious&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}

	// Default command
	function command($command, $parameter) {
		$this->reset_properties();
		curl_setopt($this->curl, CURLOPT_URL, $this->address.'/default/?cmd='.urlencode($command).(strlen($parameter)?'&param1='.urlencode($parameter):'').'&param3=NoResponse');
		if($result = curl_exec($this->curl)) {
			$this->success = TRUE;
			$this->message = 'OK';
		} else {
			$this->success = FALSE;
			$this->message = 'HTTP interface not available!';
		}
		return $this->success;
	}
	
	    // Get player status
    function status()
    {
        $this->reset_properties();
        // Defaults
		$playlist_id = -1;
		$playlist_content = array();
        $track_id = -1;
		$name     = -1;
		$file     = -1;
        $length   = -1;
        $time     = -1;
        $state    = -1;
        $volume   = -1;
		$muted    = -1;
        $random   = -1;
        $loop     = -1;
        $repeat   = -1;
        $crossfade= -1;
		$speed = -1;
		
        $this->data = array(
                'playlist_id' => (int)$playlist_id, // номер или имя плейлиста 
                'playlist_content' => $playlist_content, // содержимое плейлиста должен быть ВСЕГДА МАССИВ 
                                                         // обязательно $playlist_content[$i]['pos'] - номер трека
                                                         // обязательно $playlist_content[$i]['file'] - адрес трека
                                                         // возможно $playlist_content[$i]['Artist'] - артист
                                                         // возможно $playlist_content[$i]['Title'] - название трека
				'track_id' => (int) track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
			    'name' => (string) $name, //Current speed for playing media. float.
				'file' => (string) $file, //Current link for media in device. String.
                'length' => (int) $length, //Track length in seconds. Integer. If unknown = 0. 
                'time' => (int) $time, //Current playback progress (in seconds). If unknown = 0. 
                'state' => (string) strtolower($state), //Playback status. String: stopped/playing/paused/unknown 
                'volume' => (int)$volume, // Volume level in percent. Integer. Some players may have values greater than 100.
                'muted' => (int) $random, // Volume level in percent. Integer. Some players may have values greater than 100.
                'random' => (int) $random, // Random mode. Boolean. 
                'loop' => (int) $loop, // Loop mode. Boolean.
                'repeat' => (int) $repeat, //Repeat mode. Boolean.
                'crossfade' => (int) $crossfade, // crossfade
                'speed' => (int) $speed, // crossfade
            );

	    // удаляем из массива пустые данные
		foreach ($this->data as $key => $value) {
			if ($value == '-1' or !$value) unset($this->data[$key]);
		}
				        
        $this->success = TRUE;
        $this->message = 'OK';
        return $this->success;
    }
	
}

?>
