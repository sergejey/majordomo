<?php
/**
 * VLC over HTTP
 * @access public
 */

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$uid = rand(1, 9999999);
$playerAddr = "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT'];
$volumeLevelStep = 3;

if ($command == 'refresh')
{
   $out['PLAY'] = preg_replace('/\\\\$/is', '', $out['PLAY']);
   $path = $out['PLAY'];
   
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=pl_empty");
   $res=curl_exec($ch);

   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=in_play&input=" . rawurlencode($path));
   $res=curl_exec($ch);
}

if ($command == 'pause') 
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=pl_pause");
   $res = curl_exec($ch);
}

if ($command == 'fullscreen') 
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=fullscreen");
   $res = curl_exec($ch);
}

if ($command == 'next') 
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=pl_next");
   $res = curl_exec($ch);
}

if ($command == 'prev') {
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=pl_previous");
   $res = curl_exec($ch);
}

if ($command == 'close')
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=pl_stop");
   $res = curl_exec($ch);
}

if ($command == 'volume')
{
   $volume = $volume * $volumeLevelStep;
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/requests/status.xml?command=volume&val=$volume");
   $res = curl_exec($ch);
}

$res = '';

?>