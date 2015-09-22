<?php


 function saveToCache($key, $value, $ttl=60) {
  if (IsSet($_SERVER['REQUEST_METHOD']) && (($_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST'))) {
   global $memory_cache;
   $memory_cache[$key]=$value;
  }
  if (strlen($value)<=255) {
   $rec=array('KEYWORD'=>$key, 'DATAVALUE'=>$value, 'EXPIRE'=>date('Y-m-d H:i:s', time()+$ttl));
  } else {
   $rec=array('KEYWORD'=>$key, 'DATAVALUE'=>'(too big)', 'EXPIRE'=>date('Y-m-d H:i:s', time()+$ttl));
  }
  SQLExec("REPLACE INTO cached_values (KEYWORD, DATAVALUE, EXPIRE) VALUES('".DBSafe($rec['KEYWORD'])."', '".DBSafe($rec['DATAVALUE'])."', '".$rec['EXPIRE']."')");
 }


 function checkFromCache($key) {
  global $memory_cache;
  if (IsSet($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST') && !is_array($memory_cache)) {
   $tmp=SQLSelect("SELECT KEYWORD, DATAVALUE FROM cached_values");
   $total=count($tmp);
   for($i=0;$i<$total;$i++) {
    if ($tmp[$i]['DATAVALUE']!='(too big)') {
     $memory_cache[$tmp[$i]['KEYWORD']]=$tmp[$i]['DATAVALUE'];
    }
   }
  }

  if (isset($memory_cache[$key])) {
   return $memory_cache[$key];
  }

  $rec=SQLSelectOne("SELECT * FROM cached_values WHERE KEYWORD='".DBSafe($key)."'");
  if ($rec['KEYWORD'] && $rec['DATAVALUE']!='(too big)') {
   return $rec['DATAVALUE'];
  } else {
   return false;
  }
 }
