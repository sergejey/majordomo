<?php

$cameraUsername = $this->getProperty('cameraUsername');
$cameraPassword = $this->getProperty('cameraPassword');

if ($this->getProperty('snapshotURL')) {
    $html='<img src="'.$this->getProperty('snapshotURL').'" width="250"/>';
} elseif ($this->getProperty('streamURL_HQ')) {
    $streamURL=$this->getProperty('streamURL_HQ');
    if ($cameraUsername || $cameraPassword) {
        $streamURL=str_replace('://','://'.$cameraUsername.':'.$cameraPassword.'@',$streamURL);
    }
    $html=processTitle('<a href="'.$streamURL.'" target="_blank">[#module name="thumb" url="'.$this->getProperty('streamURL').'" username="'.$cameraUsername.'" password="'.$cameraPassword.'" width="250" live="0"#]',$this);
} else {
    $streamURL=$this->getProperty('streamURL');
    if ($cameraUsername || $cameraPassword) {
        $streamURL=str_replace('://','://'.$cameraUsername.':'.$cameraPassword.'@',$streamURL);
    }
    $html=processTitle('<a href="'.$this->getProperty('$streamURL').'" target="_blank">[#module name="thumb" url="'.$this->getProperty('streamURL').'" username="'.$cameraUsername.'" password="'.$cameraPassword.'" width="250" live="0"#]',$this);
}

$this->setProperty('previewHTML',$html);