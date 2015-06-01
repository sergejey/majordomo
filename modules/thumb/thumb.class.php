<?php

/**
 * Thumbnail builder
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.2
 */

class thumb extends module
{
   function thumb()
   {
      // setting module name
      $this->name="thumb";
      $this->title="<#LANG_MODULE_THUMB#>";
      $this->module_category="<#LANG_SECTION_SYSTEM#>";

      $this->checkInstalled();
   }

   function run()
   {
      if ($this->userpassword)
      {
         $this->userpassword = processTitle($this->userpassword);
         $tmp = explode(':', $this->userpassword);
         $this->username = $tmp[0];
         $this->password = $tmp[1];
      }

      if ($this->url)
      {
         $this->url = processTitle($this->url);
         $this->username = processTitle($this->username);
         $this->password = processTitle($this->password);

         $filename = 'thumb_' . md5($this->url) . basename(preg_replace('/\W/', '', $this->url));
         
         if (preg_match('/\.cgi$/is', $filename)) 
            $filename = str_replace('.cgi', '.jpg', $filename);

         $this->src = ROOT . 'cached/' . $filename;

         $this->src_def = urlencode('/cached/' . $filename);
      }
      else 
      {
         preg_match('/(.*)?\/.*$/', $_SERVER['PHP_SELF'], $match);
         $this->src_def = urlencode('http://' . $_SERVER['SERVER_NAME'] . $match[1] . $this->src);
      }

      $out['REQUESTED'] = $this->src;

      if (file_exists($this->src) || $this->url)
      {
         $out['URL'] = base64_encode($this->url);

         $out['USERNAME'] = urlencode($this->username);
         $out['PASSWORD'] = urlencode($this->password);
         
         $out['UNIQ'] = rand(1, time());
         $out['WIDTH'] = $this->width;
         $out['HEIGHT'] = $this->height;
         $out['MAX_HEIGHT'] = $this->max_height;
         $out['MAX_WIDTH'] = $this->max_width;
         $out['CLOSE'] = $this->close;
         
         $out['ENLARGE'] = $this->enlarge;
         $out['SRC'] = urlencode($this->src);
         $out['SRC_REAL'] = $this->src_def;
      }

      $this->data = $out;
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
      $this->result = $p->result;
   }

   /**
    * Resize image
    * @param mixed $fileName 
    * @param mixed $newWidth 
    * @param mixed $newHeight 
    * @return int
    */
   function resizeImage($fileName, $newWidth = 0, $newHeight = 0)
   {
      if (!file_exists($fileName))
         return 0;

      $lst = GetImageSize($fileName);
      $imageWidth = $lst[0];
      $imageHeight = $lst[1];
      $imageFormat = $lst[2];

      if (($newWidth > 0) && ($newWidth < $imageWidth)) 
      {
         $imageHeight = (int)($imageHeight * ($newWidth / $imageWidth));
         $imageWidth = $newWidth;
      }
      
      if (($newHeight > 0) && ($newHeight < $imageHeight)) 
      {
         $imageWidth = (int)($imageWidth * ($newHeight / $newHeight));
         $imageHeight = $newHeight;
      }
      
      if ($imageFormat == 1)
         $oldImage = imagecreatefromgif($fileName);
      elseif ($imageFormat == 2)
         $oldImage = imagecreatefromjpeg($fileName);
      elseif ($imageFormat == 3)
         $oldImage = imagecreatefrompng($fileName);
      else
         return 0;
      
      $newImage = imageCreateTrueColor($imageWidth, $imageHeight);
      $imageColor = ImageColorAllocate($newImage, 255, 255, 255); // white
      
      ImageFill($newImage, 0, 0, $imageColor);

      imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $imageWidth, $imageHeight, imageSX($oldImage), imageSY($oldImage));

      //   Save to file
      imageJpeg($newImage, $fileName);
      
      return 1;
   }
}

