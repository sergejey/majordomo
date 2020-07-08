<?php

$ot=$this->object_title;

$cameraUsername = $this->getProperty('cameraUsername');
$cameraPassword = $this->getProperty('cameraPassword');

$body = '';
$streamURL = '';

if ($this->getProperty('snapshotURL')) {
    $streamURL = $this->getProperty('snapshotURL');
} elseif ($this->getProperty('streamURL_HQ')) {
    $streamURL = $this->getProperty('streamURL_HQ');
} elseif ($this->getProperty('streamURL')) {
    $streamURL = $this->getProperty('streamURL');
}
$thumb_params='';
$thumb_params.= 'username="' . $cameraUsername . '" password="' . $cameraPassword . '"';
$thumb_params.= ' url="' . $streamURL . '"';
$streamTransport = $this->getProperty('streamTransport');
if ($streamTransport!='auto' && $streamTransport!='') {
    $thumb_params.= ' transport="'.$streamTransport.'"';
}

$body = '[#module name="thumb" '. $thumb_params. '#]';
$body = processTitle($body, $this);
if (preg_match('/img src="(.+?)"/is',$body,$m)) {
    $snapshotPreviewURL=$m[1];
    $snapshotPreviewURL = preg_replace('/&w=(\d+?)/','', $snapshotPreviewURL);
    $snapshotPreviewURL = preg_replace('/&h=(\d+?)/','', $snapshotPreviewURL);
} else {
    $snapshotPreviewURL='';
}
$rootHTML=preg_replace('/\//', '\/', ROOTHTML);
if (preg_match('/^' . $rootHTML . '/', $snapshotPreviewURL)) {
    $snapshotPreviewURL=preg_replace('/^' . $rootHTML . '/', '/', $snapshotPreviewURL);
}
$image_url=BASE_URL.$snapshotPreviewURL;

//echo $image_url."!";
if ($image_url!='') {
    $this->setProperty('snapshot',$image_url);
    $this->setProperty('updated',time());
}
