<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$last_backup = time();
$timeout = 15 * 60; // 15 minutes

if (!is_dir(ROOT . 'database_backup')) {
   umask(0);
   mkdir(ROOT . 'database_backup', 0777);
}


$filename  = ROOT . 'database_backup/db.sql';

if (defined('PATH_TO_MYSQLDUMP'))
   $mysqlDumpPath = PATH_TO_MYSQLDUMP;

if ($mysqlDumpPath == '')
{
   if (substr(php_uname(), 0, 7) == "Windows")
      $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
   else
      $mysqlDumpPath = "/usr/bin/mysqldump";
}

$mysqlDumpParam = " -h " . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASSWORD;
$mysqlDumpParam .= " --no-create-db --add-drop-table --databases " . DB_NAME;

$backups_in_row=0;

while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

   if ((time() - $last_backup) > $timeout || file_exists('./reboot'))
   {
      echo "Running db save...";

      if (file_exists($filename))
         rename($filename, $filename . '.prev');

      exec($mysqlDumpPath . $mysqlDumpParam . " > " . $filename.'.tmp');
      rename($filename.'.tmp', $filename);

      $last_backup = time();
      $backups_in_row++;

      if ($backups_in_row >= 4 && is_dir('/tmp/mysql')) {
       safe_exec('cp -rf /tmp/mysql/* /var/lib/mysql');
       $backups_in_row = 0;
      }

      echo "OK\n";
   }

   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
