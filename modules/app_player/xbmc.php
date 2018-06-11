<?php

    function directoryToArray($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '') {
        $arrayItems = array();
        $skipByExclude = false;
        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
            preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
            if($exclude){
                preg_match($exclude, $file, $skipByExclude);
            }
            if (!$skip && !$skipByExclude) {
                if (is_dir($directory. DIRECTORY_SEPARATOR . $file)) {
                    if($recursive) {
                        $arrayItems = array_merge($arrayItems, directoryToArray($directory. DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude));
                    }
                    if($listDirs){
                        $file = $directory . DIRECTORY_SEPARATOR . $file;
                        $arrayItems[] = $file;
                    }
                } else {
                    if($listFiles){
                        $file = $directory . DIRECTORY_SEPARATOR . $file;
                        $arrayItems[] = $file;
                    }
                }
            }
        }
        closedir($handle);
        }
        return $arrayItems;
    }


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
   $path = $out['PLAY'];
   $path = str_replace('/', "\\", $path);
   if (IsWindowsOs()) {
    $path = utf2win($path);
   }

   $items=array();
   if (is_dir($path))
   {
     $items=directoryToArray($path);
   } else {
     $items[]=$path;
   }

   $result=xbmc_request($ch, $terminal, 'Playlist.Clear', array('playlistid'=>0));


   foreach($items as $v) {
      if (IsWindowsOs()) {
       $v=win2utf($v);
      }
      $v = preg_replace('/^\\\\\\\\/is', 'SMB://', $v);
      $v = str_replace('\\', '/', $v);
      $result=xbmc_request($ch, $terminal, 'Playlist.Add', array('playlistid'=>0, 'item'=>array('file'=>$v)));
   }

   $result=xbmc_request($ch, $terminal, 'Player.Open', array('item'=>array('playlistid'=>0)));
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
 
