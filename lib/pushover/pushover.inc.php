<?php

/**
* Title
*
* Description
*
* @access public
*/
  Define('SETTINGS_PUSHOVER_API_TOKEN', 'X1GvhWhmSSX1V5J6cbkPfm3oEpgT7K');

 function postToPushover($message) {
  curl_setopt_array($ch = curl_init(), array(
   CURLOPT_URL => "https://api.pushover.net/1/messages.json",
   CURLOPT_RETURNTRANSFER => 1,
   CURLOPT_SSL_VERIFYPEER => FALSE, 
   CURLOPT_SSL_VERIFYHOST => 2,
   CURLOPT_POSTFIELDS => array(
   "token" => SETTINGS_PUSHOVER_API_TOKEN,
   "user" => SETTINGS_PUSHOVER_USER_KEY,
   "message" => $message,
  )));
  $res=curl_exec($ch);
  curl_close($ch);
 }
?>
