<?php

/*
 * @version 0.2
 */

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function GoogleTTS($message, $lang = 'ru') 
{
   $filename = md5($message) . '.mp3';
   $vFile = ROOT . 'cached/voice/' . $filename;
   if (file_exists($vFile)) 
   {
      @touch($vFile);
      return $vFile;
   }

   $base_url = 'http://translate.google.com/translate_tts?';
   $qs = http_build_query(array(
      'tl' => $lang,
      'ie' => 'UTF-8',
      'q'  => $message
   ));
   
   $contents = file_get_contents($base_url . $qs);
   if ($contents) 
   {
      if (!is_dir(ROOT.'cached/voice')) 
      {
         @mkdir(ROOT.'cached/voice', 0777);
      }
      SaveFile(ROOT.'cached/voice/' . $filename, $contents);
      return ROOT . 'cached/voice/' . $filename;
   } 
   else 
   {
      return 0;
   }
}

?>