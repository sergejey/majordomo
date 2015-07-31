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
  function GoogleTTS($message, $lang='ru') {
   $filename=md5($message).'.mp3';

   if (file_exists(ROOT.'cached/voice/'.$filename)) {
    @touch(ROOT.'cached/voice/'.$filename);
    return ROOT.'cached/voice/'.$filename;
   }

   $base_url = 'http://translate.google.com/translate_tts?';
   $qs = http_build_query(array(
    'tl' => $lang,
    'ie' => 'UTF-8',
    'q' => $message
   ));
   try {
    $contents = file_get_contents($base_url . $qs);
   } catch(Exception $e){
    registerError('googletts', get_class($e).', '.$e->getMessage());
   }
   if ($contents) {
    if (!is_dir(ROOT.'cached/voice')) {
     @mkdir(ROOT.'cached/voice', 0777);
    }
    SaveFile(ROOT.'cached/voice/'.$filename, $contents);
    return ROOT.'cached/voice/'.$filename;
   } else {
    return 0;
   }
  }


  function YandexTTS($message, $lang='ru') {
   $filename=md5($message).'_ya.mp3';

   if (file_exists(ROOT.'cached/voice/'.$filename)) {
    @touch(ROOT.'cached/voice/'.$filename);
    return ROOT.'cached/voice/'.$filename;
   }

   $base_url = 'https://tts.voicetech.yandex.net/generate?';
   $qs = http_build_query(array(
    'format' => 'mp3', 
    'lang' => 'ru-RU', 
    'speaker' => 'omazh',
    'key' => SETTINGS_YANDEX_TTS_KEY,
    'text' => $message
   ));
   try {
    $contents = file_get_contents($base_url . $qs);
   } catch(Exception $e){
    registerError('yandextts', get_class($e).', '.$e->getMessage());
   }
   if ($contents) {
    if (!is_dir(ROOT.'cached/voice')) {
     @mkdir(ROOT.'cached/voice', 0777);
    }
    SaveFile(ROOT.'cached/voice/'.$filename, $contents);
    return ROOT.'cached/voice/'.$filename;
   } else {
    return 0;
   }
  }

?>