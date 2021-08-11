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

echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';

while (1)
{
   if (time() - $checked_time > 30)
   {
      $checked_time = time();
      echo date("H:i:s") . " Cycle " . basename(__FILE__) . ' is running ';
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
