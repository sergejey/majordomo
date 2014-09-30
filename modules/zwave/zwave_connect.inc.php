<?php


 $cookie_file=ROOT.'cached/zwave_cookie.txt';


  //CHECK
   $url=$this->config['ZWAVE_API_URL'].'ZWaveAPI/Data/0';

   if (!$url || $url=='http://') {
    return 0;
   }


   $fields=array();

   foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
   rtrim($fields_string, '&');

   $ch = curl_init();  
   curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_POST, count($fields));  
   curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
   curl_setopt($ch, CURLOPT_TIMEOUT, 30);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
   $result = curl_exec($ch);
   curl_close($ch);


   SaveFile(ROOT.'cached/zwave_init.txt',$result);


   if (preg_match('/307 Temporary Redirect/is', $result)) {
    // CONNECT
    $url=$this->config['ZWAVE_API_URL'].'zboxweb';

    $fields=array();
    if ($this->config['ZWAVE_API_AUTH']) {
     $fields['login']=$this->config['ZWAVE_API_USERNAME'];
     $fields['pass']=$this->config['ZWAVE_API_PASSWORD'];
    }
    $fields['act']='login';

    foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
    rtrim($fields_string, '&');

    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));  
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    $result = curl_exec($ch);
    curl_close($ch);
    SaveFile(ROOT.'cached/zwave_login.txt',$result);

    //RECHECK
    $url=$this->config['ZWAVE_API_URL'];
    $fields=array();

    foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
    rtrim($fields_string, '&');

    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));  
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    $result = curl_exec($ch);
    curl_close($ch);
    SaveFile(ROOT.'cached/zwave_recheck.txt',$result);

    if (preg_match('/307 Temporary Redirect/is', $result)) {
     return false;
    }

   }

   if (!preg_match('/controller/is', $result)) {
    return false;
   }

return 1;

?>