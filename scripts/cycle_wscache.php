<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
 echo "Web-sockets disabled\n";
 exit;
}


SQLExec("TRUNCATE TABLE cached_ws");
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$latest_sent=time();
clearTimeout('restartWebSocket');

while (1)
{
   if (time() - $checked_time > 0)
   {
      $checked_time = time();
      $queue=SQLSelect("SELECT * FROM cached_ws");
      if ($queue[0]['PROPERTY']) {
       SQLExec("TRUNCATE TABLE cached_ws");
       $total=count($queue);
       $sent_ok=1;
       $properties=array();
       $values=array();
       for($i=0;$i<$total;$i++) {
           if ($queue[$i]['POST_ACTION']=='PostProperty') {
            $properties[]=$queue[$i]['PROPERTY'];
            $values[]=$queue[$i]['DATAVALUE'];
           } else {
            $dataValue=$queue[$i]['DATAVALUE'];
            if (is_array(json_decode($dataValue, true))) {
             $dataValue=json_decode($dataValue, true);
            }
            $sent=postToWebSocket($queue[$i]['PROPERTY'], $dataValue, $queue[$i]['POST_ACTION']);
            if (!$sent) {
             $sent_ok=0;
            }
           }
       }

       if (count($properties)>0) {
          $sent=postToWebSocket($properties, $values, 'PostProperty');
          if (!$sent) {
              $sent_ok=0;
          }
       }
          
       if ($sent_ok) {
        $latest_sent=time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $latest_sent, 1);
        setTimeout('restartWebSocket','sg("cycle_websocketsRun","");sg("cycle_websocketsControl","restart");',5*60); //registerError("websockets","Error posting to websocket daemon.");
       } else {
        echo date("H:i:s") . ' Error while posting to websocket.'."\n";
       }
      }

   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));


$db->Disconnect(); // closing database connection
