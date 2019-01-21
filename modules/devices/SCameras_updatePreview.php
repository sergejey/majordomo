<?php

$ot = $this->object_title;
$cameraUsername = $this->getProperty('cameraUsername');
$cameraPassword = $this->getProperty('cameraPassword');

$link = '';
$body = '';
$streamURL = '';

if ($this->getProperty('snapshotURL')) {
    $link = $this->getProperty('snapshotURL');
    $streamURL = $this->getProperty('snapshotURL');
    if ($this->getProperty('streamURL_HQ')) {
        $link = $this->getProperty('streamURL_HQ');
    } elseif ($this->getProperty('streamURL')) {
        $link = $this->getProperty('streamURL');
    }
} elseif ($this->getProperty('streamURL_HQ')) {
    $streamURL = $this->getProperty('streamURL_HQ');
    $link = $streamURL;
} elseif ($this->getProperty('streamURL')) {
    $streamURL = $this->getProperty('streamURL');
    $link = $streamURL;
}

$thumb_params ='';
$thumb_params.= 'username="' . $cameraUsername . '" password="' . $cameraPassword . '"';
$thumb_params.= ' width="250"';
$thumb_params.= ' url="' . $streamURL . '"';
if ($this->getProperty('previewType')=='slideshow') {
    //$thumb_params.= ' live="1"';
    $thumb_params.= ' slideshow="1"';
}

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
$this->setProperty('snapshotPreviewURL',$snapshotPreviewURL);

$clickAction = $this->getProperty('clickAction');

if ($clickAction == 'stream') {
    if ($cameraUsername || $cameraPassword) {
        $link = str_replace('://', '://' . $cameraUsername . ':' . $cameraPassword . '@', $link);
    }
    $html = processTitle('<a href="' . $link . '">' . $body . "</a>", $this);
} else {
    $html = processTitle('<a href="' . $snapshotPreviewURL . '" onclick="return showBigImage'.$ot.'(this.href);" target=_blank>' . $body . "</a>", $this);
}

$this->setProperty('previewHTML', $html);