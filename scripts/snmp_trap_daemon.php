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
         $device['TITLE'] = $peer;
         $device['HOST'] = $peer;
         $device['ID'] = SQLInsert('snmpdevices', $device);
      }
   }
   while($pkt !== false);
}
