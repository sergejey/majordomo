<?php
class volumio extends app_player_addon
{
    
    // Private properties
    private $curl;
    private $address;
    
    // Constructor
    function __construct($terminal)
    {
        $this->title       = 'VOLUMIO';
        $this->description = '<b>Описание:</b>&nbsp; Воспроизведение звука через VOLUMIO.<br>';
        //$this->description .= 'Воспроизведение видео на терминале этого типа пока не поддерживается.<br>';
        //$this->description .= '<b>Восстановление воспроизведения после TTS:</b>&nbsp; Да (если ТТС такого же типа, что и плеер).<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;ip_ping.<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт по умолчанию для устройства 3000<br>';
        //$this->description .= '(Инструменты -> Настройки -> Все -> Основные интерфейсы -> Дополнительные модули интерфейса -> Web)<br>';
        //$this->description .= 'и установить для него пароль (Основные интерфейсы -> Lua -> HTTP -> Пароль).';
        $this->terminal = $terminal;
        if (!$this->terminal['HOST'])
            return false;
        $this->reset_properties();
        
        $this->address     = 'http://' . $this->terminal['HOST'] . ':' . (empty($this->terminal['PLAYER_PORT']) ? 3000 : $this->terminal['PLAYER_PORT']) . 'api/v1/commands/?cmd=';
        $this->postaddress = 'http://' . $this->terminal['HOST'] . ':' . (empty($this->terminal['PLAYER_PORT']) ? 3000 : $this->terminal['PLAYER_PORT']) . 'api/v1/';
    }
    
    private function sendCommand($cmd = '')
    {
        if ($this->terminal['HOST']) {
            getURLBackground($this->address . $cmd, 0, $this->terminal['PLAYER_USERNAME'], $this->terminal['PLAYER_PASSWORD']);
            return $result;
        }
    }
    
    private function sendPostCommand($cmd = '', $qerry = array())
    {
        if ($this->terminal['HOST']) {
            postURLBackground($this->postaddress . $cmd, $query, 0, $this->terminal['PLAYER_USERNAME'], $this->terminal['PLAYER_PASSWORD']);
            return $result;
        }
    }
    
    // Pause
    function pause()
    {
        if ($this->sendCommand('pause')) {
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Stop
    function stop()
    {
        if ($this->sendCommand('stop')) {
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Next
    function next()
    {
        if ($this->sendCommand('next')) {
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Previous
    function previous()
    {
        if ($this->sendCommand('prev')) {
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Set volume
    function set_volume($level)
    {
        if ($this->sendCommand('volume&volume=' . $level)) {
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Playlist: Empty
    function pl_empty()
    {
        if ($this->sendCommand('clearQueue')) {
            $this->reset_properties();
            $this->success = true;
            $this->message = 'OK';
        }
        return $this->success;
    }
    
    // Play
    function play($input)
    {
        $this->pl_empty();
        $qerry = array(
            "uri" => $input,
            "service" => "mpd",
            "title" => "Majordomo player",
            "artist" => "Majordomo",
            "album" => "New",
            "type" => "song",
            "tracknumber" => 0
            //"duration"=>180,
            //"trackType"=>"flac"
            
        );
        sendPostCommand('addToQueue', $qerry);
    }
    
    /*
    his->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    'command' => 'pl_sort',
    'id' => (int) $order[1],
    'val' => (int) $order[0]
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    }
    } else {
    $this->success = false;
    $this->message = 'Order is missing!';
    }
    return $this->success;
    }
    
    // Playlist: Random
    function set_random($data=0)
    {
    // получаем статус все данные будут в $this->data
    $this->status();
    // если состояние плеера НЕ такое же как и запрашиваемое состояние то меняем его
    if (($this->data['random'] AND !$data) OR (!$this->data['random'] AND $data)) {
    if ($this->vlcweb_request('status.xml', array('command' => 'pl_random'))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    } else {
    $this->reset_properties();
    $this->success = false;
    $this->message = 'Can not set random';
    }
    } else {
    // НИЧЕГО не делаем поскльку состояние такое же
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    return $this->success;
    }
    
    // Playlist: Loop
    function set_loop($data=0)
    {
    // получаем статус все данные будут в $this->data
    $this->status();
    // если состояние плеера НЕ такое же как и запрашиваемое состояние то меняем его
    if (($this->data['loop'] AND !$data) OR (!$this->data['loop'] AND $data)) {
    if ($this->vlcweb_request('status.xml', array('command' => 'pl_loop'))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    } else {
    $this->reset_properties();
    $this->success = false;
    $this->message = 'Can not set loop';
    }
    } else {
    // НИЧЕГО не делаем поскльку состояние такое же
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    return $this->success;
    }
    
    // Playlist: Repeat
    function set_repeat($data=0)
    {
    // получаем статус все данные будут в $this->data
    $this->status();
    // если состояние плеера НЕ такое же как и запрашиваемое состояние то меняем его
    if (($this->data['repeat'] AND !$data) OR (!$this->data['repeat'] AND $data)) {
    if ($this->vlcweb_request('status.xml', array('command' => 'pl_repeat'))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    } else {
    $this->reset_properties();
    $this->success = false;
    $this->message = 'Can not set loop';
    }
    } else {
    // НИЧЕГО не делаем поскльку состояние такое же
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    return $this->success;
    }
    
    // restore playlist
    function restore_playlist($playlist_id = 0, $playlist_content = array(), $track_id = 0, $time = 0, $state = 'stopped')
    {
    // cleare playlist
    $this->vlcweb_request('status.xml', array(
    'command' => 'pl_empty'
    ));
    
    // add files to playlist
    foreach ($playlist_content as $song) {
    $this->vlcweb_request('status.xml', array(
    'command' => 'in_enqueue',
    'input' => $song['file']
    ));
    if ($song['pos'] == $track_id)
    $track_url = $song['file'];
    }
    
    //  get playlist for seach id played file
    if ($this->pl_get()) {
    $playlist_content = $this->data;
    }
    // seech id file
    $found_key = array_search($track_url, array_column($playlist_content, 'file'));
    // get new id file in playlist
    $track_id  = $playlist_content[$found_key]['pos'];
    // seek to position
    $this->vlcweb_request('status.xml', array('command' => 'seek', 'val' => $time ));
    // restore state player
    //stopped/playing/paused/unknown
    switch ($state) {
    case 'playing':
    if ($this->vlcweb_request('status.xml', array(
    'command' => 'pl_play',
    'id' => $track_id
    ))) {
    $this->success = TRUE;
    $this->message = 'OK';
    } else {
    $this->success = FALSE;
    $this->message = 'Missing restore playlist';
    }
    break;
    case 'paused':
    if ($this->vlcweb_request('status.xml', array(
    'command' => 'pl_play',
    'id' => $track_id
    ))) {
    if ($this->pause()) {
    $this->success = TRUE;
    $this->message = 'OK';
    } else {
    $this->success = FALSE;
    $this->message = 'Missing restore playlist';
    }
    } else {
    $this->success = FALSE;
    $this->message = 'Missing restore playlist';
    }
    break;
    case 'stoped':
    if ($this->stop()) {
    $this->success = TRUE;
    $this->message = 'OK';
    } else {
    $this->success = FALSE;
    $this->message = 'Missing restore playlist';
    }
    break;
    case 'unknown':
    $this->success = TRUE;
    $this->message = 'OK';
    }
    return $this->success;
    }
    
    // Get player status
    function status()
    {
    $this->reset_properties();
    // Defaults
    $playlist_id      = -1;
    $playlist_content = array();
    $track_id         = -1;
    $name             = -1;
    $file             = -1;
    $length           = -1;
    $time             = -1;
    $state            = -1;
    $volume           = -1;
    $muted            = -1;
    $random           = -1;
    $loop             = -1;
    $repeat           = -1;
    $crossfade        = -1;
    $speed            = -1;
    
    if ($this->vlcweb_request('status.xml')) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $xml = $this->data;
    }
    }
    
    if ($this->pl_get()) {
    $playlist_content = $this->data;
    }
    
    $this->data = array(
    'playlist_id' => (int) 1, // номер или имя плейлиста
    'playlist_content' => json_encode($playlist_content), // содержимое плейлиста должен быть ВСЕГДА МАССИВ
    // обязательно $playlist_content[$i]['pos'] - номер трека
    // обязательно $playlist_content[$i]['file'] - адрес трека
    // возможно $playlist_content[$i]['Artist'] - артист
    // возможно $playlist_content[$i]['Title'] - название трека
    'track_id' => (int) $xml->currentplid, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
    'name' => (string) $name, //Current speed for playing media. float.
    'file' => (string) $file, //Current link for media in device. String.
    'length' => (int) $xml->length, //Track length in seconds. Integer. If unknown = 0.
    'time' => (int) $xml->time, //Current playback progress (in seconds). If unknown = 0.
    'state' => (string) strtolower($xml->state), //Playback status. String: stopped/playing/paused/unknown
    'volume' => (int) round((int) $xml->volume * 100 / 256), // Volume level in percent. Integer. Some players may have values greater than 100.
    'muted' => (int) $random, // Volume level in percent. Integer. Some players may have values greater than 100.
    'random' => (int) $xml->random == 'true' ? 1 : 0, // Random mode. Boolean.
    'loop' => (int) $xml->loop == 'true' ? 1 : 0, // Loop mode. Boolean.
    'repeat' => (int) $xml->repeat == 'true' ? 1 : 0, //Repeat mode. Boolean.
    'crossfade' => (int) $crossfade, // crossfade
    'speed' => (int) $speed // crossfade
    
    );
    
    // удаляем из массива пустые данные
    foreach ($this->data as $key => $value) {
    if ($value == '-1' or !$value)
    unset($this->data[$key]);
    }
    
    $this->success = true;
    $this->message = 'OK';
    return $this->success;
    }
    
    // Seek
    function seek($position)
    {
    $this->reset_properties();
    if (strlen($position)) {
    if ($this->vlcweb_request('status.xml', array(
    'command' => 'seek',
    'val' => (int) $position
    ))) {
    if ($this->vlcweb_parse_xml($this->data)) {
    $this->reset_properties();
    $this->success = true;
    $this->message = 'OK';
    }
    }
    } else {
    $this->success = false;
    $this->message = 'Position is missing!';
    }
    return $this->success;
    }
    
    */
}
?>
