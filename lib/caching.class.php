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

function clearPropertiesCache() {
    SQLExec("TRUNCATE cached_values;");
}


function postToWebSocketQueue($property, $value, $post_action='PostProperty') {
    if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
        return false;
    }
  SQLExec("DELETE FROM cached_ws WHERE PROPERTY='".DBSafe($property)."'");
  $rec=array();
  $rec['PROPERTY']=$property;
  $rec['DATAVALUE']=$value;
  $rec['POST_ACTION']=$post_action;
  $rec['ADDED']=date('Y-m-d H:i:s');
  SQLInsert('cached_ws', $rec);
}

function postToWebSocket($property, $value, $post_action='PostProperty') {

 if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
  return false;
 }

 global $websockets_script_started;
 if ($websockets_script_started) {
  return false;
 }

 require_once ROOT.'lib/websockets/client/lib/class.websocket_client.php';

 global $wsClient;

 if (!Is_Object($wsClient)) {
  $wsClient = new WebsocketClient;
  if (!(@$wsClient->connect('127.0.0.1', WEBSOCKETS_PORT, '/majordomo'))) {
   $wsClient=false;
   if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
    DebMes("Failed to connect to websocket");
    echo date('Y-m-d H:i:s')." Failed to connect to websocket\n";
   }
  }
 }

 if (!Is_Object($wsClient) && IsSet($_SERVER['REQUEST_METHOD'])) {
  return false;
 }

    if (is_array($property) && is_array($value) ) {
        $data=array();
        $total = count($property);
        for ($i = 0; $i < $total; $i++) {
            $data[] = array('NAME'=>$property[$i],'VALUE'=>$value[$i]);
        }
    } else {
        $data = array('NAME'=>$property, 'VALUE'=>$value);
    }
    
   
 $payload = json_encode(array(
        'action' => $post_action,
        'data' => $data
 ));

 $data_sent=false;
 if (Is_Object($wsClient)) {
  $data_sent=@$wsClient->sendData($payload);
  if (!$data_sent) {
   if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
    DebMes("Failed to send data to websocket");
    echo date('Y-m-d H:i:s')." Failed to send data to websocket\n";
   }
  }
 }

 if (!$data_sent && !IsSet($_SERVER['REQUEST_METHOD'])) {
  //reconnect
  $wsClient = new WebsocketClient;
  if ((@$wsClient->connect('127.0.0.1', WEBSOCKETS_PORT, '/majordomo'))) {
   $data_sent=@$wsClient->sendData($payload);
  } else {
   if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
    DebMes("Failed to reconnect to websocket");
    echo date('Y-m-d H:i:s')." Failed to reconnect to websocket\n";
   }
  }
 }

 return $data_sent;

}