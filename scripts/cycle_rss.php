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

include_once(DIR_MODULES . 'rss_channels/rss_channels.class.php');

$rss_ch = new rss_channels();

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$checked_time = 0;

while (1)
{
   if (time() - $checked_time > 10)
   {
      $checked_time = time();
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
    
      //updating RSS channels
      $sqlQuery = "SELECT ID, TITLE
                     FROM rss_channels
                    WHERE NEXT_UPDATE <= NOW()
                    LIMIT 1";

      $to_update = SQLSelect($sqlQuery);
      $total = count($to_update);

      for ($i = 0; $i < $total; $i++)
         $rss_ch->updateChannel($to_update[$i]['ID']);
   }
   
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
