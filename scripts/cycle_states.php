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

$checked_time = 0;

if ($_GET['once'])
{
   $last_run = getGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run');

   if ((time() - $last_run) > 5 * 60)
   {
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
      cycleBody();
   }

   echo "OK";
}
else
{
   echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

   while (1)
   {
      if (time() - $checked_time > 5)
      {
         setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
         $checked_time = time();
         cycleBody();
      }

      if (file_exists('./reboot') || IsSet($_GET['onetime']))
      {
         $db->Disconnect();
         exit;
      }

      sleep(1);
   }

   DebMes("Unexpected close of cycle: " . basename(__FILE__));
}

/**
 * Summary of cycleBody
 * @return void
 */
function cycleBody()
{
   // check main system states
   $objects = getObjectsByClass('systemStates');
   $total = count($objects);

   for ($i = 0; $i < $total; $i++)
   {
      $oldState = getGlobal($objects[$i]['TITLE'] . '.stateColor');
      callMethod($objects[$i]['TITLE'] . '.checkState');
      $newState = getGlobal($objects[$i]['TITLE'] . '.stateColor');

      if ($newState != $oldState)
      {
         echo $objects[$i]['TITLE'] . " state changed to " . $newState . PHP_EOL;

         $params = array('STATE' => $newState);
         callMethod($objects[$i]['TITLE'] . '.stateChanged', $params);
      }
   }
}
