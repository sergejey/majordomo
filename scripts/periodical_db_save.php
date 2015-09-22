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

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$last_backup = time();
$timeout = 15 * 60; // 15 minutes
$filename  = ROOT . '/database_backup/db.sql';

if (defined('PATH_TO_MYSQLDUMP'))
   $mysqlDumpPath = PATH_TO_MYSQLDUMP;

if ($mysqlDumpPath == '')
{
   if (substr(php_uname(), 0, 7) == "Windows")
      $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
   else
      $mysqlDumpPath = "/usr/bin/mysqldump";
}

$mysqlDumpParam = " --user=" . DB_USER . " --password=" . DB_PASSWORD;
$mysqlDumpParam .= " --no-create-db --add-drop-table --databases " . DB_NAME;

while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

   if ((time() - $last_backup) > $timeout || file_exists('./reboot'))
   {
      echo "Running db save...";
      
      if (file_exists($filename))
         rename($filename, $filename . '.prev');

      exec($mysqlDumpPath . $mysqlDumpParam . " > " . $filename);
    
      $last_backup = time();
      
      echo "OK\n";
   }
 
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
