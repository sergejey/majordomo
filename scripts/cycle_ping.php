<?php

chdir(dirname(__FILE__) . '/../');

include_once './config.php';
include_once './lib/loader.php';
include_once './lib/threads.php';

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

include_once(DIR_MODULES . 'pinghosts/pinghosts.class.php');

$pinghosts = new pinghosts();

$checked_time = 0;
setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
$cycleVarName='ThisComputer.'.str_replace('.php', '', basename(__FILE__)).'Run';

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

while (1)
{
   if (time() - $checked_time > 10)
   {
      $checked_time = time();
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
      // saveToCache("MJD:$cycleVarName", $checked_time);
      // checking all hosts
      $pinghosts->checkAllHosts();
   }

   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      exit;
   }
   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
