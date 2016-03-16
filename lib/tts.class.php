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
/**
 * GoogleTTS
 * @param mixed $message Message
 * @param mixed $lang    Language (default 'ru')
 * @return int|string
 */
function GoogleTTS($message, $lang = 'ru')
{
   $filename       = md5($message) . '.mp3';
   $cachedVoiceDir = ROOT . 'cached/voice';
   $cachedFileName = $cachedVoiceDir . '/' . $filename;
   $base_url       = 'http://translate.google.com/translate_tts?';

   if (file_exists($cachedFileName))
   {
      @touch($cachedFileName);

      return $cachedFileName;
   }

   
   $qs = http_build_query(array('tl' => $lang, 'ie' => 'UTF-8', 'q' => $message));

   try
   {
      $contents = file_get_contents($base_url . $qs);
   }
   catch (Exception $e)
   {
      registerError('googletts', get_class($e) . ', ' . $e->getMessage());
   }

   if (isset($contents))
   {
      CreateDir($cachedVoiceDir);

      SaveFile($cachedFileName, $contents);
      
      return $cachedFileName;
   }
   
   return 0;
}

/**
 * YandexTTS
 * @param mixed $message Message
 * @param mixed $lang    Language (default 'ru-RU')
 * @return int|string
 */
function YandexTTS($message, $lang = 'ru-RU')
{
   $filename       = md5($message) . '_ya.mp3';
   $cachedVoiceDir = ROOT . 'cached/voice';
   $cachedFileName = $cachedVoiceDir . '/' . $filename;
   $base_url       = 'https://tts.voicetech.yandex.net/generate?';

   if (file_exists($cachedFileName))
   {
      @touch($cachedFileName);

      return $cachedFileName;
   }
   
   $qs = http_build_query(array('format' => 'mp3', 'lang' => $lang, 'speaker' => 'omazh', 'key' => SETTINGS_YANDEX_TTS_KEY, 'text' => $message));

   try
   {
      $contents = file_get_contents($base_url . $qs);
   }
   catch (Exception $e)
   {
      registerError('yandextts', get_class($e) . ', ' . $e->getMessage());
   }
   
   if (isset($contents))
   {
      CreateDir($cachedVoiceDir);

      SaveFile($cachedFileName, $contents);

      return $cachedFileName;
   }
   
   return 0;
}
