<?php


 function saveToCache($key, $value, $ttl=60) {
  if (strlen($value)<=255) {
   $rec=array('KEYWORD'=>$key, 'DATAVALUE'=>$value, 'EXPIRE'=>date('Y-m-d H:i:s', time()+$ttl));
  } else {
   $rec=array('KEYWORD'=>$key, 'DATAVALUE'=>'(too big)', 'EXPIRE'=>date('Y-m-d H:i:s', time()+$ttl));
  }
  SQLExec("REPLACE INTO cached_values (KEYWORD, DATAVALUE, EXPIRE) VALUES('".DBSafe($rec['KEYWORD'])."', '".DBSafe($rec['DATAVALUE'])."', '".$rec['EXPIRE']."')");
 }


 function checkFromCache($key) {
  $rec=SQLSelectOne("SELECT * FROM cached_values WHERE KEYWORD='".DBSafe($key)."'");
  if ($rec['KEYWORD'] && $rec['DATAVALUE']!='(too big)') {
   return $rec['DATAVALUE'];
  } else {
   return false;
  }
 }
