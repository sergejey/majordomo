<?php

/*
	Addon iobroker.paw HTTP for app_player
*/

class iobroker extends app_player_addon {
	
	// Private properties
	private $curl;
	private $address;
	
	// Constructor
	function __construct($terminal) {
		$this->title = 'ioBroker.paw';
		$this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука через отправку ссылки на андроид с помощью &nbsp;<a href="https://play.google.com/store/apps/details?id=ru.codedevice.iobrokerpawii">ioBroker.paw</a>.<br>';
		$this->description .= 'Воспроизведение видео на терминале этого типа поддерживается.<br>';
		$this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Нет (если ТТС такого же типа, что и плеер).<br>';
		$this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
		$this->description .= '<b>Настройка:</b>&nbsp; Не забудьте активировать HTTP интерфейс в настройках ioBroker.paw и включть работу сервиса кнопкой: Connection.<br>';
		$this->description .= 'Управление треками реальзовано через запуск команды в Tasker:<br>';
		$this->description .= '1. Создать на вкладке TASKS, 3 задачи: Play, Pause, Prev. Учитавая регистр!<br>';
		$this->description .= '2. В задачах добавить соответствующие команды. Выберите из списка действий «Медиа»->»Упр. медиа плеером» и далее выбираем вариант необходимо события.<br>';
		$this->description .= '3. Установить чекбокс на против строки "Use Notification if Availble.<br>';
		$this->description .= '4. В настройках таскера на вкладке разное установить чекбокс "Разрешить внешний доступ".<br>';
		
		$this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
		$this->reset_properties();
		
		// Curl
		$this->curl = curl_init();
		$this->address = 'http://'.$this->terminal['HOST'].':'.(empty($this->terminal['PLAYER_PORT'])?8080:$this->terminal['PLAYER_PORT']);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		if($this->terminal['PLAYER_USERNAME'] || $this->terminal['PLAYER_PASSWORD']) {
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
			curl_setopt($this->curl, CURLOPT_USERPWD, $this->terminal['PLAYER_USERNAME'].':'.$this->terminal['PLAYER_PASSWORD']);
		}
	}
	
	// Destructor
	function destroy() {
		curl_close($this->curl);
	}
 
    // ping mediaservise
    public function ping_mediaservice($host)
    {
        $this->reset_properties();
        $connection = @fsockopen($this->terminal['HOST'],$this->terminal['PLAYER_PORT'],$errno,$errstr,1);
        if (is_resource($connection)) {
            $this->success = TRUE;
            fclose($connection);
        } else {
            $this->success = FALSE;
        }
        return $this->success;
    }
	
	// Play
	function play($input) {
		$this->reset_properties();
		if(strlen($input)) {
			$input = preg_replace('/\\\\$/is', '', $input);
			$url = $this->address . "/api/set.json?link=" . urlencode($input);
			getURL($url,0);
		} 
		return $this->success;
	}
	
	function stop() {
		$this->reset_properties();
		$url = $this->address . "/api/set.json?link=" . BASE_URL . "/stop.mp3";
		getURL($url,0); 
		return $this->success;
	}
	
	// Set volume
	function set_volume($level) {
		$this->reset_properties();
                $data =  json_decode(getURL($this->address . "/api/get.json",0), true); 
		$music_max = $data['volume']['music-max'];
		if(strlen($level)) {
			$level = round((int)$level * $music_max / 100);
			getURL($this->address . "/api/set.json?volume=" . urlencode($level),0);
		}
		return $this->success;
	}
	
	function pause() {
		$this->reset_properties();
		getURL($this->address . "/api/set.json?tasker=Pause",0); 
		return $this->success;
	}
	
	function next() {
		$this->reset_properties();
		getURL($this->address . "/api/set.json?tasker=Next",0); 
		return $this->success;
	}
	
	function previous() {
		$this->reset_properties();
		getURL($this->address . "/api/set.json?tasker=Prev",0); 
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
		
        $result = json_decode(getURL($this->address . "/api/get.json",0), true); 
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
                'volume' => (int)($result['volume']['music'] * 100 / $result['volume']['music-max']), // Volume level in percent. Integer. Some players may have values greater than 100.
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
