<?php
/**
* Xiaomi Home Cycle
* @version 2018.08.10
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

include_once(DIR_MODULES . 'xiaomihome/xiaomihome.class.php');

$xiaomihome_module = new xiaomihome();
$xiaomihome_module->getConfig();

echo date('H:i:s') . ' Running ' . basename(__FILE__) . PHP_EOL;

$latest_check = 0;
$latest_report = time();
$latest_data_received = 0;
//$check_period = 5;
$check_period = 55;
$report_period = 14400;

$bind_ip = '0.0.0.0';
$gate_ip = '';
$cycle_debug = false;
$debmes_debug = false;
$gw_heartbeat_debug = false;

if ($xiaomihome_module->config['API_BIND']) $bind_ip = $xiaomihome_module->config['API_BIND'];
if ($xiaomihome_module->config['API_IP']) $gate_ip = $xiaomihome_module->config['API_IP'];
if ($xiaomihome_module->config['API_LOG_CYCLE']) $cycle_debug = true;
if ($xiaomihome_module->config['API_LOG_DEBMES']) $debmes_debug = true;
if ($xiaomihome_module->config['API_LOG_GW_HEARTBEAT']) $gw_heartbeat_debug = true;

echo date('H:i:s') . ' Init Xiaomi Home ' . PHP_EOL;
echo date('H:i:s') . " Bind IP - $bind_ip" . PHP_EOL;
echo date('H:i:s') . ' Gate IP - ' . ($gate_ip != '' ? $gate_ip : 'undefined') . PHP_EOL;
echo date('H:i:s') . ' Cycle debug - ' . ($cycle_debug ? 'yes' : 'no') . PHP_EOL;
echo date('H:i:s') . ' DebMes debug - ' . ($debmes_debug ? 'yes' : 'no') . PHP_EOL;
echo date('H:i:s') . ' Heartbeat debug - ' . ($gw_heartbeat_debug ? 'yes' : 'no') . PHP_EOL;

$sock = 0;

function xiaomi_socket_connect() {

   global $sock;
   global $bind_ip;
   global $cycle_debug;

   if (!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);
      if ($cycle_debug) echo date('H:i:s') . " Failed to create socket [$errorcode] $errormsg" . PHP_EOL;
      die("Failed to create socket [$errorcode] $errormsg \n");
   }

   if ($cycle_debug) echo date('H:i:s') . ' Socket created' . PHP_EOL;

   if (!socket_bind($sock, $bind_ip, XIAOMI_MULTICAST_PORT)) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);
      if ($cycle_debug) echo date('H:i:s') . " Could not bind socket (Binding IP: $bind_ip) [$errorcode] $errormsg" . PHP_EOL;
      die("Could not bind socket [$errorcode] $errormsg \n");
   }

   if ($cycle_debug) echo date('H:i:s') . " Socket bind OK (Binding IP: $bind_ip)" . PHP_EOL;

   socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
   socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
   socket_set_option($sock, IPPROTO_IP, IP_MULTICAST_LOOP, true);
   socket_set_option($sock, IPPROTO_IP, IP_MULTICAST_TTL, 32);
   socket_set_option($sock, IPPROTO_IP, MCAST_JOIN_GROUP, array('group' => XIAOMI_MULTICAST_ADDRESS, 'interface' => 0, 'source' => 0));

   $message = '{"cmd":"whois"}';

   if ($cycle_debug) echo date('H:i:s') . ' Sending discovery packet to ' . XIAOMI_MULTICAST_ADDRESS . " ($message)" . PHP_EOL;
   socket_sendto($sock, $message, strlen($message), 0, XIAOMI_MULTICAST_ADDRESS, XIAOMI_MULTICAST_PEER_PORT);
}

xiaomi_socket_connect();

$latest_data_received = time();

$rcv_count = 0;
$tcv_count = 0;

while (1) {
    $time = time();
   if (($time - $latest_check) >= $check_period) {
      $latest_check = $time;
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $time, 1);
   }

   $queue = SQLSelect("SELECT * FROM xiqueue ORDER BY ID");

   if ($queue[0]['ID']) {
      $total = count($queue);
      for ($i = 0; $i < $total; $i++) {
         $data = $queue[$i]['DATA'];
            if ($cycle_debug) echo date('H:i:s') . ' Queue command: ' . json_encode($queue[$i]) . PHP_EOL;
            if ($gate_ip != '') {
               $ip = $gate_ip;
            } else {
               $ip = $queue[$i]['IP'];
            }
            if ($cycle_debug) echo date('H:i:s') . " Sending: $data to $ip" . PHP_EOL;
            $xiaomihome_module->sendMessage($data, $ip, $sock);
            SQLExec("DELETE FROM xiqueue WHERE ID=" . $queue[$i]['ID']);
            $tcv_count += 1;
        }
   }

   $buf = '';

   @$r = socket_recvfrom($sock, $buf, 1024, 0, $remote_ip, $remote_port);

   if ($buf != '') {
      //if ($cycle_debug) echo date('H:i:s') . " Received message ($buf) from $remote_ip" . PHP_EOL;

      $url = BASE_URL . '/ajax/xiaomihome.html?op=process';
      $url .= '&message=' . urlencode($buf) . '&ip=' . urlencode($remote_ip);
      $url .= '&log_debmes=' . urlencode($debmes_debug) . '&log_gw_heartbeat=' . urlencode($gw_heartbeat_debug);

      getURLBackground($url, 0);

      $latest_data_received = time();
      $rcv_count += 1;
   }

   if (time() - $latest_data_received > 60) {
      // 1 minute timeout reconnect
      if ($cycle_debug) echo date('H:i:s') . ' Xiaomi data timeout... Try reconnect' . PHP_EOL;
      if ($debmes_debug) DebMes(' Xiaomi data timeout... Try reconnect', 'xiaomi');
      socket_close($sock);
      $latest_data_received = time();
      xiaomi_socket_connect();
   }

   if ($cycle_debug) {
      if ((time() - $latest_report) >= $report_period) {
         $latest_report = time();
         echo date('H:i:s') . " Received messages count = $rcv_count, sent messages count = $tcv_count" . PHP_EOL;
      }
   }

   if (file_exists('./reboot') || isset($_GET['onetime'])) {
      $db->Disconnect();
      socket_close($sock);
      echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME ' . basename(__FILE__) . PHP_EOL;
      exit;
   }
}

echo date('H:i:s') . ' Unexpected close of cycle' . PHP_EOL;

DebMes('Unexpected close of cycle: ' . basename(__FILE__));