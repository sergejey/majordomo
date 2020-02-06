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

$new_width = 500;
$new_height = 500;
$gif_delay = 100; // 1/100 sec

$images=array();
for($i=0;$i<5;$i++) {
    $image_data=getURL($image_url);
    $lst = getimagesizefromstring($image_data);
    $image_width=$lst[0];
    $image_height=$lst[1];
    $image_format=$lst[2];
    if ($image_width>0 && $image_height>0) {
        if (($new_width!=0) && ($new_width<$image_width)) {
            $image_height=(int)($image_height*($new_width/$image_width));
            $image_width=$new_width;
        }
        if (($new_height!=0) && ($new_height<$image_height)) {
            $image_width=(int)($image_width*($new_height/$image_height));
            $image_height=$new_height;
        }
        $old_image=imagecreatefromstring($image_data);
        $new_image=imageCreateTrueColor($image_width, $image_height);
        $white = ImageColorAllocate($new_image, 255, 255, 255);
        ImageFill($new_image, 0, 0, $white);
        imagecopyresampled( $new_image, $old_image, 0, 0, 0, 0, $image_width, $image_height, imageSX($old_image), imageSY($old_image));
        $images[]=$new_image;
    }
}

$total=count($images);
if ($total>0) {
    $durations=array();
    for($i=0;$i<$total;$i++) {
        $durations[]=$gif_delay;
    }
    include_once(ROOT.'lib/utils/AnimGif.php');
    $gif_filename = ROOT.'cms/cached/'.$ot.'_'.date('Y-m-d_H-i-s').'.gif';

    $anim = new GifCreator\AnimGif();
    if ($anim->create($images,$durations)) {
        $anim->save($gif_filename);
        $this->setProperty('series',$gif_filename);
        unlink($gif_filename);
    } else {
        $this->setProperty('series','');
    }
} else {
    $this->setProperty('series','');
}
$value = $this->getProperty('series');
if ($value) {
    $value=ROOT.'cms/images/'.$value;
}

return $value;
