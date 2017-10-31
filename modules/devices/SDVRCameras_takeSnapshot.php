<?php
if ($this->getProperty('snapshotURL')) {
    $image_url=$this->getProperty('snapshotURL');
} elseif ($this->getProperty('streamURL_HQ')) {
    $html=processTitle('[#module name="thumb" url="'.$this->getProperty('streamURL_HQ').'" width="250" live="1"#]',$this);
    if (preg_match('/<img src="(.+?)"/is',$html,$m)) {
        $image_url=BASE_URL.$m[1];
    }
} elseif ($this->getProperty('streamURL')) {
    $html=processTitle('[#module name="thumb" url="'.$this->getProperty('streamURL').'" width="250" live="1"#]',$this);
    if (preg_match('/<img src="(.+?)"/is',$html,$m)) {
        $image_url=BASE_URL.$m[1];
    }
}
//echo $image_url."!";
if ($image_url!='') {
    $this->setProperty('snapshot',$image_url);
    $this->setProperty('updated',time());
}
