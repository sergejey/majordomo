<?php
/**
* Xiaomi miIO Cycle
* @author <skysilver.da@gmail.com>
* @copyright 2017-2019 Agaphonov Dmitri aka skysilver <skysilver.da@gmail.com> (c)
* @version 2.0
*/

chdir(dirname(__FILE__) . '/../');

include_once('./config.php');
include_once('./lib/loader.php');
include_once('./lib/threads.php');

set_time_limit(0);

$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once('./load_settings.php');
include_once(DIR_MODULES . 'control_modules/control_modules.class.php');

$ctl = new control_modules();

include_once(DIR_MODULES . 'xiaomimiio/xiaomimiio.class.php');
include_once(DIR_MODULES . 'xiaomimiio/lib/miio.class.php');

$miio_module = new xiaomimiio();
$miio_module->getConfig();

echo date('H:i:s') . ' Running ' . basename(__FILE__) . PHP_EOL;

$latest_check = 0;
$latest_disc = 0;
//$check_period = 5;
$check_period = 55;

$bind_ip = '0.0.0.0';
$miio_debug = false;
$cycle_debug = false;
$debmes_debug = false;
$disc_period = 120;
$socket_timeout = 2;

if ($miio_module->config['API_IP']) $bind_ip = $miio_module->config['API_IP'];
if ($miio_module->config['API_LOG_MIIO']) $miio_debug = true;
if ($miio_module->config['API_LOG_CYCLE']) $cycle_debug = true;
if ($miio_module->config['API_LOG_DEBMES']) $debmes_debug = true;
if ($miio_module->config['API_DISC_PERIOD'] !== null) $disc_period = $miio_module->config['API_DISC_PERIOD'];
if ($miio_module->config['API_SOCKET_TIMEOUT'] !== null) $socket_timeout = $miio_module->config['API_SOCKET_TIMEOUT'];

echo date('H:i:s') . ' Init miIO ' . PHP_EOL;
echo date('H:i:s') . " Bind IP - $bind_ip" . PHP_EOL;
echo date('H:i:s') . " Discover period - $disc_period seconds" . PHP_EOL;
echo date('H:i:s') . " Socket read timeout - $socket_timeout seconds" . PHP_EOL;
echo date('H:i:s') . ' Cycle debug - ' . ($cycle_debug ? 'yes' : 'no') . PHP_EOL;
echo date('H:i:s') . ' DebMes debug - ' . ($debmes_debug ? 'yes' : 'no') . PHP_EOL;
echo date('H:i:s') . ' miIO-Lib debug - ' . ($miio_debug ? 'yes' : 'no') . PHP_EOL;
echo date('H:i:s') . ' Extended debug - ' . (EXTENDED_LOGGING ? 'yes' : 'no') . PHP_EOL;

$dev = new miIO(null, $bind_ip, null, $miio_debug);

if ($socket_timeout != 2) {
   $dev->send_timeout = $socket_timeout;
}

$msg_id = time();

while (1) {
    $time = time();
   if ((time() - $latest_check) >= $check_period) {
      $latest_check = $time;
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $time, 1);
   }

   $queue = SQLSelect("SELECT miio_queue.*, miio_devices.TOKEN, miio_devices.DEVICE_TYPE, miio_devices.IP FROM miio_queue LEFT JOIN miio_devices ON miio_queue.DEVICE_ID=miio_devices.ID ORDER BY miio_queue.ID");

   if ($queue[0]['ID']) {
      $total = count($queue);
      for ($i = 0; $i < $total; $i++) {

         if ($cycle_debug) echo date('H:i:s') . ' Queue command: ' . json_encode($queue[$i]) . PHP_EOL;

         SQLExec("DELETE FROM miio_queue WHERE ID=" . $queue[$i]['ID']);

         $reply = '';
         $dev->data = '';

         if ($queue[$i]['DEVICE_ID']) {
            if ($queue[$i]['IP']) {
               $dev->ip = $queue[$i]['IP'];
            } else {
               $dev->ip = null;
            }
            if ($queue[$i]['TOKEN']) {
               $dev->token = $queue[$i]['TOKEN'];
            } else {
               $dev->token = null;
            }
            if (defined('EXTENDED_LOGGING') && EXTENDED_LOGGING == 1) {
               $time_start = microtime(true);
               if($dev->msgSendRcv($queue[$i]['METHOD'], $queue[$i]['DATA'], $msg_id)) {
                  $reply = $dev->data;
               } else {
                  $reply = '{"error":"Device not answered"}';
               }
               $time = microtime(true) - $time_start;
               if ($cycle_debug) echo date('H:i:s') . " msgSendRcv() runtime: {$time} s." . PHP_EOL;
            } else {
               if($dev->msgSendRcv($queue[$i]['METHOD'], $queue[$i]['DATA'], $msg_id)) {
                  $reply = $dev->data;
               } else {
                  $reply = '{"error":"Device not answered"}';
               }
            }
            $msg_id++;
         }

         if ($reply != '') {
            if ($cycle_debug) echo date('H:i:s') . " Reply: $reply" . PHP_EOL;
            $url = BASE_URL.'/ajax/xiaomimiio.html?op=process&command='.urlencode($queue[$i]['METHOD']).'&device_id='.$queue[$i]['DEVICE_ID'].'&message='.urlencode($reply);
            if (defined('EXTENDED_LOGGING') && EXTENDED_LOGGING == 1) {
               $time_start = microtime(true);
               $miio_module->RunInBackground($url);
               $time = microtime(true) - $time_start;
               if ($cycle_debug) echo date('H:i:s') . " RunInBackground() runtime: {$time} s." . PHP_EOL;
            } else {
               $miio_module->RunInBackground($url);
            }
            if ($cycle_debug) echo date('H:i:s') . ' Background processing of the response is started' . PHP_EOL;
         }
      }
   }

   $devices = SQLSelect("SELECT * FROM miio_devices WHERE UPDATE_PERIOD>0 AND NEXT_UPDATE<=NOW() AND DEVICE_TYPE!='' AND TOKEN!=''");

   if ($devices[0]['ID']) {
      $total = count($devices);
      for ($i = 0; $i < $total; $i++) {
         $devices[$i]['NEXT_UPDATE'] = date('Y-m-d H:i:s', time()+(int)$devices[$i]['UPDATE_PERIOD']);
         SQLUpdate('miio_devices', $devices[$i]);
         $ip = $devices[$i]['IP'];
         if ($cycle_debug) echo date('H:i:s') . " Request to update the properties of the device $ip" . PHP_EOL;
         $miio_module->requestStatus($devices[$i]['ID']);
      }
      continue;
   }

   if (((time() - $latest_disc) >= $disc_period) && ($disc_period != 0)) {
      $latest_disc = time();
      if ($cycle_debug) echo date('H:i:s') . " Starting periodic search for devices in the network (every $disc_period seconds)" . PHP_EOL;
      $url = BASE_URL.'/ajax/xiaomimiio.html?op=broadcast_search';
      if (defined('EXTENDED_LOGGING') && EXTENDED_LOGGING == 1) {
         $time_start = microtime(true);
         $miio_module->RunInBackground($url);
         $time = microtime(true) - $time_start;
         if ($cycle_debug) echo date('H:i:s') . " RunInBackground() runtime: {$time} s." . PHP_EOL;
      } else {
         $miio_module->RunInBackground($url);
      }
      if ($cycle_debug) echo date('H:i:s') . ' Background search process is started' . PHP_EOL;
      continue;
   }

   if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
      $db->Disconnect();
      echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME ' . basename(__FILE__) . PHP_EOL;
      exit;
   }

   sleep(1);
}

echo date('H:i:s') . ' Unexpected close of cycle' . PHP_EOL;

DebMes('Unexpected close of cycle: ' . basename(__FILE__));
