<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

include_once(DIR_MODULES . 'knxdevices/knxdevices.class.php');

$knx = new knxdevices();
$knx->getConfig();

if (!$knx->config['API_ENABLE'])
{
   echo "KNX/EIB API is turned off";
   exit; // no API URL set
}

$connected = 0;

for ($i = 0; $i < 3; $i++)
{
   if ($knx->connect(0))
   {
      $connected = 1;
      break;
   }
   else
   {
      echo "Cannot connect to EIB/KNX API\n";
      sleep(3);
   }
}

if ($connected)
{
   $knx_data = new knxdevices();
   $knx_data->getConfig();

   if (!$knx_data->config['API_ENABLE'])
   {
      echo "KNX/EIB API is turned off";
      exit; // no API URL set
   }

   $connected = 0;
   
   for ($i = 0; $i < 3; $i++)
   {
      if ($knx_data->connect(0))
      {
         $connected = 1;
         break;
      }
      else
      {
         echo "Cannot connect to EIB/KNX API\n";
         sleep(3);
      }
   }
}

if (!$connected)
{
   exit;
}

echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

$buf = new EIBBuffer;
$status = $knx->connection->EIBOpenVBusmonitorText();

if ($status < 0)
{
   echo "Cannot start monitoring";
   $knx->disconnect();
   $db->Disconnect();
   exit;
}

//$status=$knx->connection->EIBOpenVBusmonitor();
//$knx->connection->EIBOpenVBusmonitorText();

$log = '';
$errors_total = 0;

while (1)
{
   $updated = array();

   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   $r = $knx->connection->EIBGetBusmonitorPacket($buf);

   if ($r > 0)
   {
      $errors_total = 0;
      $data = date('H:i:s') . ' ' . $buf->buffer;
      $log .= $data . "\n";
      $tmp = explode("\n", $log);
      $total = count($tmp);
      
      if ($total > 50)
      {
         $tmp = array_slice($tmp, -50, 50);
         $log = implode("\n", $tmp);
      }
      
      SaveFile(ROOT . 'cached/knx_monitor.txt', $log);

      if (preg_match('/from (.+?) to (.+?) hops/', $data, $m))
      {
         $from = $m[1];
         $from = str_replace('.', '/', $from);
         $updated[$from] = 1;

         $to = $m[2];
         $to = str_replace('.', '/', $to);
         $updated[$to] = 1;
      }

      foreach ($updated as $k => $v)
      {
         $knx_data->addressUpdated($k);
      }
   }
   else
   {
      $errors_total++;
      
      echo "Error: " . $knx->connection->GetLastError();
      
      sleep(10);
      
      $knx->disconnect();
      $knx_data->disconnect();
      $knx->connect(0);
      $knx_data->connect(0);
      $status = $knx->connection->EIBOpenVBusmonitorText();
   }

   if (file_exists('./reboot') || $_GET['onetime'])
   {
      $knx->disconnect();
      $knx_data->disconnect();
      $db->Disconnect();
      exit;
   }

   // sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
