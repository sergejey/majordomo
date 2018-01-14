<?php
/*
* @version 0.1 (auto-set)
*/
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
chdir('../../');
include_once("./config.php");
include_once("./lib/loader.php");

if (!defined('PATH_TO_FFMPEG')) {
 if (IsWindowsOS()) {
  define("PATH_TO_FFMPEG", SERVER_ROOT.'/apps/ffmpeg/ffmpeg.exe');
 } else {
  define("PATH_TO_FFMPEG", 'ffmpeg');
 }
}

define("_I_CACHING","1");               //    Chaching enabled, 1 - yes, 0 - no
define("_I_CACHE_PATH","./cached/"); //    Path to cache dir
define("_I_CACHE_EXPIRED","2592000");   //    Expired time for images in seconds, 0 - never expired


//$img=($_REQUEST['img']);

//$img=urldecode($_REQUEST['img']);

if (IsSet($url) && $url != '') {
    $tmp_url = base64_decode($url);
    if ($tmp_url == 'usb') {
        $url = "";
        $img_tmp = $img . '_tmp';
        $resolution = '1280x720';
        if ($w && $h) {
            $resolution = $w.'x'.$h;
        }
        $cmd = 'fswebcam -r '.$resolution.' '.$img_tmp;
        exec($cmd);
        if (file_exists($img_tmp)) {
            rename($img_tmp, $img);
        }
    }
}

if ($_GET['debug']) {
    $live = 0;
}

if (IsSet($url) && $url!='') {

   $resize='';
   if ($w && $h) {
    $resize=' -vf scale='.$w.':'.$h;
   } elseif ($w) {
    $resize=' -vf scale='.$w.':-1';
   } elseif ($h) {
    $resize=' -vf scale=-1:'.$h;
   }
   $url=base64_decode($url);

   if ($username || $password) {
    $url=str_replace('://','://'.$username.':'.$password.'@',$url);
   }

   if (preg_match('/^rtsp:/is', $url)) {
    //-rtsp_transport tcp // -rtsp_transport tcp 
    $stream_options = '-timelimit 15 -y -i "'.$url.'"'.$resize.' -r 5 -f image2 -vframes 1'; //-ss 00:00:01.500
    if ($_GET['debug']) {
        $stream_options = '-v verbose '.$stream_options;
    }
    if ($_GET['transport']) {
        $stream_options = '-rtsp_transport '.$_GET['transport'].' '.$stream_options;
    }
    $cmd = PATH_TO_FFMPEG.' '.$stream_options.' '.$img;

    if ($live) {
        $boundary = "my_mjpeg";
            header("Cache-Control: no-cache");
            header("Cache-Control: private");
            header("Pragma: no-cache");
            header("Content-type: multipart/x-mixed-replace; boundary=$boundary");
            print "--$boundary\n";
            set_time_limit(0);
            @apache_setenv('no-gzip', 1);
            @ini_set('zlib.output_compression', 0);
            @ini_set('implicit_flush', 1);

        for ($i = 0; $i < ob_get_level(); $i++) ob_end_flush();
        ob_implicit_flush(1);
        while (true) {
            print "Content-type: image/jpeg\n\n";
            system($cmd);
            print LoadFile($img);
            print "--$boundary\n";
            sleep(1);
        }
    } else {
     @unlink($img);
     $output=array();
     $res = exec($cmd.' 2>&1',$output);

        if ($_GET['debug']) {
            echo $cmd;
            echo "<hr><pre>".implode("\n",$output)."</pre>";
            exit;
        }

    }
    $dc=1;
   } else {

       function mjpeg_grab_frame($url) {
           $f = fopen($url, 'r');
           if($f) {
               $r = null;
               $lines = 0;
               while(substr_count($r, "\xFF\xD8") != 2 && $lines<1000) {
                   $r .= fread($f, 512);
                   $lines++;
               }
               if ($lines>=1000) {
                   return false;
               }
               $start = strpos($r, "\xFF\xD8");
               $end = strpos($r, "\xFF\xD9", $start)+2;
               $frame = substr($r, $start, $end-$start);
               fclose($f);
               return $frame;
           }
       }

       $result=@mjpeg_grab_frame($url);

       if (!$result) {
           $result=getURL($url, 0, $username, $password);
       }


    if ($result) {

     if ($live) {

        $boundary = "my_mjpeg";
        header("Cache-Control: no-cache");
        header("Cache-Control: private");
        header("Pragma: no-cache");
        header("Content-type: multipart/x-mixed-replace; boundary=$boundary");
        print "--$boundary\n";
        set_time_limit(0);
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) ob_end_flush();
        ob_implicit_flush(1);
        while (true) {
            print "Content-type: image/jpeg\n\n";
            $result=getURL($url, 0, $username, $password);
            print $result;
            print "--$boundary\n";
            sleep(1);
        }

     } else {
      SaveFile($img, $result);
     }
     $dc=1;
    } else {
     $img='error';
    }

   }
}



//$img=str_replace('\\\\', '\\', $img);

//echo $img;exit;

$type = (IsSet($_REQUEST['t']) ? $_REQUEST['t']:0);

/*
   Allowed types:
    0 - fit
    2 - exact size
*/
//$img='./../../'.$img;

if (file_exists($img)) {

 $new_width=(int)$_REQUEST['w'];
 $new_height=(int)$_REQUEST['h'];

 $cached_filename=md5($img.filemtime($img).$new_width.$new_height).'.jpg';
 $path_to_cache_file=_I_CACHE_PATH.substr($cached_filename, 0, 2);
 $cache = $path_to_cache_file.'/'.$cached_filename;

 //   Check the cache
 if(_I_CACHING == "1" && !$dc)
         
   //$cache = _I_CACHE_PATH.md5($img.filemtime($img).$image_width.$image_height).".pic";
   if(file_exists($cache))
   {
    //  $resc=GetImageSize($cache);
    //  list($w_o,$h_o,$t_o) = $resc;
    //  if ($w_o == $image_width && $h_o == $image_height && $t_o == $image_format)
    //  {
         header("Content-Type:image/jpeg");
         header("Content-Length: ".filesize($cache));
         header("Cache-Control: public"); // HTTP/1.1
         header("Expires: ".gmdate('D, d M Y H:i:s', (time()+60*60*24*30)).' GMT'); // Date in the future (+30 days)
         header('Last-Modified: '.gmdate('D, d M Y H:i:s', @filemtime($cache)).' GMT');
         readfile($cache);
         exit;
   //   }
   }

 $filename=$img;
 $lst=GetImageSize($filename);
 $image_width=$lst[0];
 $image_height=$lst[1];
 $image_format=$lst[2];


 switch($type)
 {
   case 0:
    if (($new_width!=0) && ($new_width<$image_width)) {
     $image_height=(int)($image_height*($new_width/$image_width));
     $image_width=$new_width;
    }
    if (($new_height!=0) && ($new_height<$image_height)) {
     $image_width=(int)($image_width*($new_height/$image_height));
     $image_height=$new_height;
    }
   break;

   case 1:
     $image_width=$new_width;
     $image_height=$image_height;
   break;
 }


   // Remove old cached images
   /*
   if ($handle = opendir(_I_CACHE_PATH)) {
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != ".." && time() - filemtime(_I_CACHE_PATH.$file) > _I_CACHE_EXPIRED)
            @unlink(_I_CACHE_PATH.$file);
      }
      closedir($handle);
   }
   */
   //

 //   endof check
 if ($image_format==1) {
//   Header("Content-Type:image/gif");
//   readfile($filename);
//   exit;
  $old_image=imagecreatefromgif($filename);
 } elseif ($image_format==2) {
  $old_image=imagecreatefromjpeg($filename);
 } elseif ($image_format==3) {
  $old_image=imagecreatefrompng($filename);
 } else {
  return;
 }

// echo("$image_width x $image_height");
 $new_image=imageCreateTrueColor($image_width, $image_height);
 $white = ImageColorAllocate($new_image, 255, 255, 255);
 ImageFill($new_image, 0, 0, $white);

 /*imageCopyResized*/imagecopyresampled( $new_image, $old_image, 0, 0, 0, 0, $image_width, $image_height, imageSX($old_image), imageSY($old_image));

 //   Save to cache
 if(_I_CACHING == "1" && !$_REQUEST['dc'])
 {
    //if (!file_exists(_I_CACHE_PATH)) {
    // @mkdir(_I_CACHE_PATH, 0777);
    //}
    if (!is_dir($path_to_cache_file)) {
     @mkdir($path_to_cache_file);
    }

    imageJpeg($new_image,$cache);
    //@chmod($cache, '0666');
 }
 //   Endof save

 Header("Content-type:image/jpeg");
 imageJpeg($new_image);

}
