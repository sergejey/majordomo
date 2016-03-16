<?php

/**
 * Summary of saveToCache
 * @param mixed $key   Key
 * @param mixed $value Value
 * @param mixed $ttl   TTL (default 60)
 * @return void
 */
function saveToCache($key, $value, $ttl = 60)
{
   if (isset($_SERVER['REQUEST_METHOD']))
   {
      global $memory_cache;
      $memory_cache[$key] = $value;
   }

   if (strlen($value) <= 255)
   {
      $rec = array('KEYWORD' => $key, 'DATAVALUE' => $value, 'EXPIRE' => date('Y-m-d H:i:s', time() + $ttl));
   }
   else
   {
      $rec = array('KEYWORD' => $key, 'DATAVALUE' => '(too big)', 'EXPIRE' => date('Y-m-d H:i:s', time() + $ttl));
   }

   $sqlQuery = "REPLACE INTO cached_values (KEYWORD, DATAVALUE, EXPIRE)
                VALUES ('" . DBSafe($rec['KEYWORD']) . "',
                        '" . DBSafe($rec['DATAVALUE']) . "',
                        '" . $rec['EXPIRE'] . "')";
   SQLExec($sqlQuery);
}

/**
 * Summary of checkFromCache
 * @param mixed $key Key
 * @return mixed
 */
function checkFromCache($key)
{
   global $memory_cache;
   
   if (isset($_SERVER['REQUEST_METHOD'])
         && !is_array($memory_cache))
   {
      $tmp   = SQLSelect("SELECT KEYWORD, DATAVALUE FROM cached_values");
      $total = count($tmp);
      
      for ($i = 0; $i < $total; $i++)
      {
         if ($tmp[$i]['DATAVALUE'] != '(too big)')
         {
            $memory_cache[$tmp[$i]['KEYWORD']] = $tmp[$i]['DATAVALUE'];
         }
      }
   }

   if (isset($memory_cache[$key]))
      return $memory_cache[$key];

   $rec = SQLSelectOne("SELECT * FROM cached_values WHERE KEYWORD = '" . DBSafe($key) . "'");
   
   if ($rec['KEYWORD'] && $rec['DATAVALUE'] != '(too big)')
   {
      return $rec['DATAVALUE'];
   }
   else
   {
      return false;
   }
}


function postToWebSocket($property, $value, $post_action='PostProperty') {

 if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
  return;
 }

 require_once ROOT.'lib/websockets/client/lib/class.websocket_client.php';

 global $wsClient;

 if (!Is_Object($wsClient)) {
  $wsClient = new WebsocketClient;
  if (!(@$wsClient->connect('127.0.0.1', WEBSOCKETS_PORT, '/majordomo'))) {
   $wsClient=false;
   if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
    echo date('Y-m-d H:i:s')." Failed to connect to websocket\n";
   }
  }
 }

 if (!Is_Object($wsClient) && IsSet($_SERVER['REQUEST_METHOD'])) {
  return false;
 }

 $payload = json_encode(array(
        'action' => $post_action,
        'data' => array('NAME'=>$property, 'VALUE'=>$value)
 ));

 $data_sent=false;
 if (Is_Object($wsClient)) {
  $data_sent=@$wsClient->sendData($payload);
  if (!$data_sent) {
   if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
    echo date('Y-m-d H:i:s')." Failed to send data to websocket\n";
   }
  }
 }

 if (!$data_sent && !IsSet($_SERVER['REQUEST_METHOD'])) {
  //reconnect
  $wsClient = new WebsocketClient;
  if ((@$wsClient->connect('127.0.0.1', WEBSOCKETS_PORT, '/majordomo'))) {
   $wsClient->sendData($payload);
  } else {
   if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
    echo date('Y-m-d H:i:s')." Failed to reconnect to websocket\n";
   }
  }
 }


}