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

echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

$last_backup=time();
//$last_backup=0;

$timeout=15*60; // 15 minutes
$filename  = ROOT . '/database_backup/db.sql';

while(1) 
{
   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);

   if ((time()-$last_backup)>$timeout) {
    echo "Running db save...";
    if (file_exists($filename)) {
     rename($filename, $filename.'.prev');
    }
    exec(PATH_TO_MYSQLDUMP." --user=".DB_USER." --password=".DB_PASSWORD." --no-create-db --add-drop-table --databases ".DB_NAME.">".$filename);
    $last_backup=time();
    echo "OK\n";
   }
 
   if (file_exists('./reboot') || $_GET['onetime']) 
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

?>