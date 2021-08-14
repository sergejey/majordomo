<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");

set_time_limit(0);

include_once("./load_settings.php");

if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
   echo "Web-sockets disabled\n";
   exit;
}

SQLTruncateTable('cached_ws');
echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';

$latest_sent=time();

while (1)
{
      $queue=SQLSelect("SELECT * FROM cached_ws");
      if ($queue && $queue[0]['PROPERTY']) {
         $total=count($queue);
         $properties=array();
         $values=array();
         for($i=0;$i<$total;$i++) {
            //$queue[$i]['PROPERTY']=mb_strtolower($queue[$i]['PROPERTY'],'UTF-8');
            if ($queue[$i]['POST_ACTION']=='PostProperty') {
               $properties[]=$queue[$i]['PROPERTY'];
               $values[]=$queue[$i]['DATAVALUE'];
            } else {
               $dataValue=$queue[$i]['DATAVALUE'];
               if (is_array(json_decode($dataValue, true))) {
                  $dataValue=json_decode($dataValue, true);
               }
               if (postToWebSocket($queue[$i]['PROPERTY'], $dataValue, $queue[$i]['POST_ACTION'])=== false) {
				if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
					DebMes("Failed to send data to websocket");
				}
				sg("cycle_websocketsRun","");
				sg("cycle_websocketsControl","restart");
				sleep(20);
				sg("cycle_wscacheRun","");
				sg("cycle_wscacheControl","restart");
			   }
            }
         }

         if (count($properties)>0) {
            if (postToWebSocket($properties, $values, 'PostProperty') === false) {
				if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
					DebMes("Failed to send data to websocket");
				}
				sg("cycle_websocketsRun","");
				sg("cycle_websocketsControl","restart");
				sleep(20);
				sg("cycle_wscacheRun","");
				sg("cycle_wscacheControl","restart");
			   }
         }

   }
            SQLTruncateTable('cached_ws');
		if (time() - $checked_time > 30) {
			  $checked_time = time();
			  //setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', $checked_time, 1);
			echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';
        }
   if (isRebootRequired() || IsSet($_GET['onetime']))
   {
      exit;
   }
   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
