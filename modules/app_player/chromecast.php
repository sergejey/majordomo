<?php
$address=$terminal['HOST']; // ip

include_once(DIR_MODULES.'app_player/castv2/Chromecast.php');

$cc = new Chromecast($address,8009);
$cc->requestId = time();

if ($command == 'refresh')
{
    $path= $out['PLAY'];

    echo "Checking stream type. ";
    if (preg_match('/\.mp3/',$path)) {
        $content_type='audio/mp3';
    } elseif (preg_match('/mp4/',$path)) {
        $content_type='video/mp4';
    } elseif (preg_match('/m4a/',$path)) {
        $content_type='audio/mp4';
    } elseif (preg_match('/^http/',$path)) {
        $content_type='';
        if ($fp = fopen($path, 'r')) {
            $meta = stream_get_meta_data($fp);
            if (is_array($meta['wrapper_data'])) {
                $items=$meta['wrapper_data'];
                foreach($items as $line) {
                    if (preg_match('/Content-Type:(.+)/is',$line,$m)) {
                        $content_type=trim($m[1]);
                    }
                }
            }
            fclose($fp);
        }
    }
    if (!$content_type) {
        $content_type='audio/mpeg';
    }
    $cc->DMP->play($path,"LIVE",$content_type,true,0);
}

if ($command == 'pause')
{
    $cc->DMP->pause();
}

if ($command == 'fullscreen')
{
}

if ($command == 'next')
{
}

if ($command == 'prev')
{
}

if ($command == 'close')
{
    $cc->DMP->Stop();
}

if ($command == 'volume') {
  $cc->DMP->SetVolume(round($volume/100,1));
}