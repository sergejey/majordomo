<?php

$port=$terminal['PLAYER_PORT'];
$language = SETTINGS_SITE_LANGUAGE;
if (!$port) {
    $port='8091';
}
$host=$terminal['HOST'];

if ($command == 'refresh')
{
    $path = $out['PLAY'];
    $url = 'http://'.$host.':'.$port.'/google-home-notifier?text='.urlencode($path);
    getURL($url,0);
}

if ($command=='pause' || $command=='close') {
    $path= 'http://somefakeurl.stream/';
    $url = 'http://'.$host.':'.$port.'/google-home-notifier?text='.urlencode($path);
    getURL($url,0);
}

$res = '';

