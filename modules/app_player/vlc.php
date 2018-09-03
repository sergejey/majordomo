<?php

$terminal['PLAYER_PORT']='80';
if ($command=='refresh') {
    $out['PLAY']=preg_replace('/\\\\$/is', '', $out['PLAY']);
    $out['PLAY']=preg_replace('/\/$/is', '', $out['PLAY']);
    if (preg_match('/^http/', $out['PLAY'])) {
        $path=urlencode($out['PLAY']);
    } else {
        $path=urlencode(''.str_replace('/', "\\", ($out['PLAY'])));
    }
    curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_play&param=".$path);
    $res=curl_exec($ch);
}
if ($command=='fullscreen') {
    curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_fullscreen");
    $res=curl_exec($ch);
}
if ($command=='pause') {
    curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_pause");
    $res=curl_exec($ch);
}
if ($command=='next') {
    curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_next");
    $res=curl_exec($ch);
}
if ($command=='prev') {
    curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_prev");
    $res=curl_exec($ch);
}
if ($command=='close') {
    curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_close");
    $res=curl_exec($ch);
}
if ($command=='volume') {
    setGlobal('ThisComputer.volumeLevel', $volume);
    callMethod('ThisComputer.VolumeLevelChanged', array('VALUE'=>$volume, 'HOST'=>$terminal['HOST']));
}

//$res = json_encode($json);
//$res = '';
