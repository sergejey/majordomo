<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

set_time_limit(0);

include_once(DIR_MODULES . 'snmpdevices/snmpdevices.class.php');
$snmpdevices = new snmpdevices();

function mjSnmpTrapAllowed($peer)
{
    if (defined('SNMP_TRAP_ALLOWED_HOSTS') && SNMP_TRAP_ALLOWED_HOSTS !== '') {
        foreach (explode(',', SNMP_TRAP_ALLOWED_HOSTS) as $host) {
            if (trim($host) === $peer) return true;
        }
    }
    if (defined('HOME_NETWORK') && HOME_NETWORK !== '') {
        $patterns = explode(',', HOME_NETWORK);
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if ($pattern === '') continue;
            $regex = str_replace('\*', '\d{1,3}', preg_quote($pattern, '/'));
            if (preg_match('/^' . $regex . '$/', $peer)) return true;
        }
    }
    return false;
}

$lastTrap = array();
$socket = stream_socket_server("udp://0.0.0.0:162", $errno, $errstr, STREAM_SERVER_BIND);

// If we could not bind successfully, let's throw an error
if (!$socket)
{
   die($errstr);
}
else
{
   do
   {
      $pkt = stream_socket_recvfrom($socket, 512, 0, $peer);

      if (preg_match('/:\d+$/', $peer, $m))
         $peer = str_replace($m[0], '', $peer);

       if (!mjSnmpTrapAllowed($peer)) {
          echo date('Y-m-d H:i:s') . ' ignored snmp trap from ' . $peer . PHP_EOL;
          continue;
       }
       if (isset($lastTrap[$peer]) && time() - $lastTrap[$peer] < 2) {
          continue;
       }
       $lastTrap[$peer] = time();
       echo date('Y-m-d H:i:s') . ' new snmp trap from ' . $peer . PHP_EOL;
       $sqlQuery = "SELECT ID
                      FROM snmpdevices
                     WHERE HOST LIKE '" . DBSafe($peer) . "'";

       $device = SQLSelectOne($sqlQuery);

       if ($device['ID'])
       {
          $snmpdevices->readDevice($device['ID']);
       }
       else
       {
          echo date('Y-m-d H:i:s') . ' unknown snmp device ' . $peer . ' (add it manually)' . PHP_EOL;
       }
   }
   while($pkt !== false);
}
