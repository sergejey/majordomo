<?php
if (!$terminal['PLAYER_PORT']) {
    $terminal['PLAYER_PORT']='80';
}
$playerAddr = "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT'];

if ($command == 'refresh')
{
    $path = $out['PLAY'];
    $res=getURL($playerAddr.'/cgi-bin/modules/web_radio/radio.cgi?station='.urlencode($path));
}

if ($command == 'pause')
{
    $res=getURL($playerAddr.'/cgi-bin/modules/web_radio/radio.cgi?station=');
}

if ($command == 'fullscreen')
{
}

if ($command == 'next')
{
}

if ($command == 'prev') {
}

if ($command == 'close')
{
    $res=getURL($playerAddr.'/cgi-bin/modules/web_radio/radio.cgi?station=');
}

if ($command == 'volume')
{
}

$res = '';

?>