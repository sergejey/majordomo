<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

$checked_time = 0;

setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
$cycleVarName='ThisComputer.'.str_replace('.php', '', basename(__FILE__)).'Run';

$objects = getObjectsByClass('systemStates');
$total = count($objects);

if ($_GET['once'])
{
   $last_run = getGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run');

   if ((time() - $last_run) > 5 * 60)
   {
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
      for ($i = 0; $i < $total; $i++)
      {
         callMethod($objects[$i]['TITLE'] . '.checkState');
      }
   }

   echo "OK";
}
else
{
   echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

   while (1)
   {
      if (time() - $checked_time > 10)
      {
         setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
         $checked_time = time();
         // saveToCache("MJD:$cycleVarName", $checked_time);

         for ($i = 0; $i < $total; $i++)
         {
            callMethod($objects[$i]['TITLE'] . '.checkState');
         }
      }

      if (file_exists('./reboot') || IsSet($_GET['onetime']))
      {
         exit;
      }

      sleep(1);
   }

   DebMes("Unexpected close of cycle: " . basename(__FILE__));
}
