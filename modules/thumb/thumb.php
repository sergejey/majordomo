<?php

/**
 * Thumbnail builder
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.2
 */

chdir('../../');
include_once("./config.php");
include_once("./lib/loader.php");

$ffmpegPath = IsWindowsOS() ? SERVER_ROOT.'/apps/ffmpeg/ffmpeg.exe' : 'ffmpeg';

define("PATH_TO_FFMPEG", $ffmpegPath);  // Path to FFMPEG

define("_I_CACHING", "1");               // Chaching enabled, 1 - yes, 0 - no
define("_I_CACHE_PATH", "./cached/");    // Path to cache dir
define("_I_CACHE_EXPIRED", "2592000");   // Expired time for images in seconds, 0 - never expired

if ($url)
{
   $url = base64_decode($url);
   
   if (preg_match('/^rtsp:/is', $url)) 
   {
      exec(PATH_TO_FFMPEG.' -y -i "' . $url . '" -r 10 -f image2 -ss 00:00:01.500 -vframes 1 ' . $img);
      $dc = 1;
   }
   else 
   {
      $result = getURL($url, 0, $username, $password);
      
      if ($result)
      {
         SaveFile($img, $result);
         $dc = 1;
      } 
      else 
      {
         $img = 'error';
      }
   }
}

// Allowed types: 0 - fit, 2 - exact size
$type = (isset($_REQUEST['t']) ? $_REQUEST['t'] : 0);

if (file_exists($img)) 
{
   $newWidth = (int)$_REQUEST['w'];
   $newHeight = (int)$_REQUEST['h'];

   $cachedFilename = md5($img . filemtime($img) . $newWidth . $newHeight) . '.jpg';
   $pathToCacheFile = _I_CACHE_PATH . substr($cachedFilename, 0, 2);
   $cache = $pathToCacheFile . '/' . $cachedFilename;

   //   Check the cache
   if(_I_CACHING == "1" && !$dc)
   {
      if(file_exists($cache))
      {
         header("Content-Type:image/jpeg");
         header("Content-Length: ".filesize($cache));
         header("Cache-Control: public"); // HTTP/1.1
         header("Expires: ".gmdate('D, d M Y H:i:s', (time()+60*60*24*30)).' GMT'); // Date in the future (+30 days)
         header('Last-Modified: '.gmdate('D, d M Y H:i:s', @filemtime($cache)).' GMT');
         readfile($cache);
         exit;
      }
   }
   
   $fileName = $img;
   $lst = GetImageSize($fileName);
   $imageWidth = $lst[0];
   $imageHeight = $lst[1];
   $imageFormat = $lst[2];

   switch($type)
   {
      case 0:
         if (($newWidth > 0) && ($newWidth < $imageWidth))
         {
            $imageHeight = (int)($imageHeight * ($newWidth / $imageWidth));
            $imageWidth = $newWidth;
         }
         
         if (($newHeight > 0) && ($newHeight < $imageHeight)) 
         {
            $imageWidth = (int)($imageWidth * ($newHeight / $imageHeight));
            $imageHeight = $newHeight;
         }
         
         break;
      case 1:
         $imageWidth = $newWidth;
         $imageHeight = $newHeight;
         break;
   }

   //   endof check
   if ($imageFormat == 1)
      $oldImage = imagecreatefromgif($fileName);
   elseif ($imageFormat == 2) 
      $oldImage = imagecreatefromjpeg($fileName);
   elseif ($imageFormat == 3)
      $oldImage = imagecreatefrompng($fileName);
   else
      return;
   
   $newImage = imageCreateTrueColor($imageWidth, $imageHeight);
   $imageColor = ImageColorAllocate($newImage, 255, 255, 255);
   ImageFill($newImage, 0, 0, $imageColor);

   imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $imageWidth, $imageHeight, imageSX($oldImage), imageSY($oldImage));

   // Save to cache
   if(_I_CACHING == "1" && !isset($_REQUEST['dc']))
   {
      if (!is_dir($pathToCacheFile))
         @mkdir($pathToCacheFile);

      imageJpeg($newImage, $cache);
   }
   //   Endof save

   Header("Content-type:image/jpeg");
   imageJpeg($newImage);
}
