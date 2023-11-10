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
include_once(DIR_MODULES . 'telegram/telegram.class.php');
echo date("H:i:s") . " Running " . basename(__FILE__) . PHP_EOL;
$telegram_module = new telegram();
setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
echo date("H:i:s") . " Init module " . PHP_EOL;
$telegram_module->init();
$latest_check=time();
while (1)
{
    $time = time();
  // if ($latest_check + 30 < time())
   if ($latest_check + 50 < $time)
   {
       $latest_check = $time;
       setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $time, 1);
   }
   
   $res = $telegram_module->processCycle();
   if ($res == -1){
      $db->Disconnect();
      exit;
   }
   
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
 
?>
