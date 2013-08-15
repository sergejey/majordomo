<?php

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function sendSMS($phone, $text, $pass_server='000000') 
{
   $address = gethostbyname ('127.0.0.1'); //IP Ђ¤аҐб ў иҐЈ® Є®¬ЇмовҐа 
   $service_port = 8000; //Џ®ав
   //$pass_server='000000'; //Џ а®«м

   $phone = preg_replace('/^\+/', '', $phone);
     
   $socket = socket_create (AF_INET, SOCK_STREAM, 0);
   if ($socket < 0) 
   {
      echo "socket create failed reason: " . socket_strerror ($socket) . "\n";
   }
     
   $result = socket_connect ($socket, $address, $service_port);
   if ($result < 0) 
   {
      echo "socket connect failed.\nReason: ($result) " . socket_strerror($result) . "\n";
   }

   $text = iconv("UTF-8","Windows-1251",$text);
   $in = base64_encode($pass_server."#SENDSMS#[TYPE]0[NUMBER]".$phone."[TEXT]".$text); //ЏаЁ¬Ґа ®вЇа ўЄЁ б¬б
     
   //$in = base64_encode($pass_server."#CMD#[USSD]*102#"); //ЏаЁ¬Ґа § Їа®б  USSD Є®¬ ­¤л
     
   $out = '';
     
   socket_write ($socket, $in, strlen ($in));
   //echo "Response:\n\n";
   $res='';
   
   while ($out = socket_read ($socket, 2048)) 
   {
      $res.=$out;
   }
   
   socket_close ($socket);
   
   $res = iconv("Windows-1251","UTF-8", $res);
   return $res;
}

function sendUSD($text, $pass_server='000000') 
{
   $address = gethostbyname ('127.0.0.1'); //IP Ђ¤аҐб ў иҐЈ® Є®¬ЇмовҐа 
   $service_port=8000; //Џ®ав
   //$pass_server='000000'; //Џ а®«м

   $phone=preg_replace('/^\+/', '', $phone);
     
   $socket = socket_create (AF_INET, SOCK_STREAM, 0);
   if ($socket < 0) 
   {
      echo "socket create failed reason: " . socket_strerror ($socket) . "\n";
   }
     
   $result = socket_connect ($socket, $address, $service_port);
   if ($result < 0) 
   {
      echo "socket connect failed.\nReason: ($result) " . socket_strerror($result) . "\n";
   }

   $text = iconv("UTF-8","Windows-1251",$text);
   $in = base64_encode($pass_server . "#CMD#[USSD]" . $text); //ЏаЁ¬Ґа ®вЇа ўЄЁ б¬б
     
   //$in = base64_encode($pass_server."#CMD#[USSD]*102#"); //ЏаЁ¬Ґа § Їа®б  USSD Є®¬ ­¤л
     
   $out = '';
     
   socket_write ($socket, $in, strlen ($in));
   //echo "Response:\n\n";
   $res='';
   
   while ($out = socket_read ($socket, 2048)) 
   {
      $res.=$out;
   }
   
   socket_close ($socket);

   $res = iconv("Windows-1251","UTF-8", $res);
    
   if (preg_match('/USSD-RESPONSE\[.+?\]:(.+)/is', $res, $m)) 
   {
      $res=$m[1];
   }
   
   return $res;
}

?>