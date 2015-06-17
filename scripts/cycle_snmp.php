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

include_once(DIR_MODULES . 'snmpdevices/snmpdevices.class.php');

$snmpdevices = new snmpdevices();

$tmp = SQLSelectOne("SELECT ID FROM snmpdevices LIMIT 1");

if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   
   // check all web vars
   $snmpdevices->readAll();

   if (file_exists('./reboot') || $_GET['onetime'])
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
