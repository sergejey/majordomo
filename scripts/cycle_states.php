<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

$checked_time = 0;

echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';

$objects = getObjectsByClass('systemStates');
if (is_array($objects) || $objects instanceof Countable) {
	$total = count($objects);
	echo $total . " objects by class systemStates." . "\n";
} else {
	$total = 0;
	echo "No systemStates objects found....". "\n";
	exit;
}

if ($_GET['once'])
{
	
   echo "Runing once.......";
   $last_run = getGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run');

   if ((time() - $last_run) > 5 * 60)
   {
      echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';
      for ($i = 0; $i < $total; $i++)
      {
         callMethod($objects[$i]['TITLE'] . '.checkState');
      }
   }

   echo "OK" . "\n";
   exit;
}
else
{
  echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';

   while (1)
   {
      if (time() - $checked_time > 30)
      {
         //setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
	 echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';
         $checked_time = time();
         // saveToCache("MJD:$cycleVarName", $checked_time);

         for ($i = 0; $i < $total; $i++)
         {
            callMethod($objects[$i]['TITLE'] . '.checkState');
         }
      }

	  if (isRebootRequired() || IsSet($_GET['onetime'])) {
        exit;
      }

      sleep(1);
   }

   DebMes("Unexpected close of cycle: " . basename(__FILE__));
}
