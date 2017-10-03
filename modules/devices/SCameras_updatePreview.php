<?php

if ($this->getProperty('snapshotURL')) {
    $html='<img src="'.$this->getProperty('snapshotURL').'" width="250"/>';
} elseif ($this->getProperty('streamURL_HQ')) {
    $html=processTitle('<a href="'.$this->getProperty('streamURL_HQ').'" target="_blank">[#module name="thumb" url="'.$this->getProperty('streamURL').'" username="'.$this->getProperty('cameraUsername').'" password="'.$this->getProperty('cameraPassword').'" width="250" live="1"#]',$this);
} else {
    $html=processTitle('<a href="'.$this->getProperty('streamURL').'" target="_blank">[#module name="thumb" url="'.$this->getProperty('streamURL').'" username="'.$this->getProperty('cameraUsername').'" password="'.$this->getProperty('cameraPassword').'" width="250" live="1"#]',$this);
}

$this->setProperty('previewHTML',$html);