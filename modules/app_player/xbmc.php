<?php

/**
 * Description
 * @access public
 */
function xbmc_request($ch, $terminal, $method, $params = 0) 
{
   if (!$params)
      $params = array();

   $json = array('jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => $uid);
   $playerAddr = 'http://' . $terminal['HOST'] . ":" . $terminal['PLAYER_PORT'];
   $request = json_encode($json);

   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_URL, $playerAddr . "/jsonrpc?request=".urlencode($request));
   $responseRaw = curl_exec($ch);

   //DebMes("XBMC request (".$playerAddr . "/jsonrpc?request=".urlencode($request));
   //DebMes("XBMC response: ".$responseRaw);

   /*
   if (!$responseRaw) {
    DebMes("XBMC error: ".curl_error ($ch));
   }
   */

   
   return json_decode($responseRaw);
}

$uid = rand(1, 9999999);
$players = xbmc_request($ch, $terminal, 'Player.GetActivePlayers');
$player_type = $players->result[0]->type;
$player_id = $players->result[0]->playerid;

if ($command == 'refresh')
{
   $out['PLAY'] = preg_replace('/\\\\$/is', '', $out['PLAY']);
   $path = str_replace('/', "\\", utf2win($out['PLAY']));
   
   if (is_file($path))
   {
      //play file
      $result=xbmc_request($ch, $terminal, 'Player.Open', array('item'=>array('file'=>$path)));
   } 
   else
   {
      //play folder
      ///!!!!!! how to get it working???
      $result=xbmc_request($ch, $terminal, 'Player.Open', array('item'=>array('path'=>$path)));
   }
}

if ($command == 'pause') 
   $result=xbmc_request($ch, $terminal, 'Player.PlayPause', array('playerid'=>(int)$player_id));

if ($command == 'fullscreen')
{
}

if ($command == 'next') 
   $result = xbmc_request($ch, $terminal, 'Player.GoNext', array('playerid'=>(int)$player_id));

if ($command == 'prev') 
   $result = xbmc_request($ch, $terminal, 'Player.GoPrevious', array('playerid'=>(int)$player_id));

if ($command == 'close') 
   $result=xbmc_request($ch, $terminal, 'Player.Stop', array('playerid'=>(int)$player_id));

if ($command == 'volume') 
   $result=xbmc_request($ch, $terminal, 'Application.SetVolume', array('volume'=>(int)$volume));

if ($command == 'notify') {
 global $type;
 global $title;
 global $message;
 if (!$type) $type='info';
 if (!$title) $title='MajorDoMo';
 $result=xbmc_request($ch, $terminal, 'GUI.ShowNotification', array('title'=>(string)$title,'message'=>(string)$message,'image'=>(string)$type));

}
 

