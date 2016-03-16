<?php

/**
 * Title
 *
 * Description
 *
 * @access public
 */

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$uid = rand(1, 9999999);

$playerAddr = "http://" . $terminal['HOST'] . ":" . $terminal['PLAYER_PORT'];

if ($command == 'refresh')
{
   $out['PLAY'] = preg_replace('/\\\\$/is', '', $out['PLAY']);
   $path = str_replace('/', "\\", ($out['PLAY']));
   
   curl_setopt($ch, CURLOPT_URL, $playerAddr ."/default/?cmd=EmptyPlaylist&param3=NoResponse");
   $res = curl_exec($ch);
   
   curl_setopt($ch, CURLOPT_URL, $playerAddr ."/default/?cmd=Browse&param1=".urlencode($path)."&param2=EnqueueDirSubdirs&param3=NoResponse");
   $res = curl_exec($ch);

   curl_setopt($ch, CURLOPT_URL, $playerAddr ."/default/?cmd=Start&param1=0&param3=NoResponse");
   $res = curl_exec($ch);
}

if ($command == 'pause')
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr ."/default/?cmd=PlayOrPause&param3=NoResponse");
   $res=curl_exec($ch);
}

if ($command == 'fullscreen')
{
}

if ($command == 'next')
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr ."/default/?cmd=StartNext&param3=NoResponse");
   $res = curl_exec($ch);
}

if ($command == 'prev')
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/default/?cmd=StartPrevious&param3=NoResponse");
   $res = curl_exec($ch);
}

if ($command == 'close')
{
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/default/?cmd=Stop&param3=NoResponse");
   $res=curl_exec($ch);
}

?>