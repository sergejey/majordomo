<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

include_once(DIR_MODULES . 'scripts/scripts.class.php');

$ctl = new control_modules();
$sc = new scripts();
$checked_time = 0;

echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';

while (1) {
   if ((time()-$checked_time)>30) {
      $checked_time = time();
      //setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
      // saveToCache("MJD:$cycleVarName", $checked_time);
     echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';
   }
   runScheduledJobs();
   $sc->checkScheduledScripts();
   if (isRebootRequired() || IsSet($_GET['onetime'])) {
      exit;
   }
   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
