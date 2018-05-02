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

echo date("H:i:s") . " running " . basename(__FILE__) . "\n";
SQLExec("DELETE FROM safe_execs");

while (1)
{
   if (time() - $checked_time > 10)
   {
      $checked_time = time();
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   }

   $sqlQuery = "DELETE
                  FROM safe_execs
                 WHERE ADDED < '" . date('Y-m-d H:i:s', time() - 180) . "'";

   SQLExec($sqlQuery);

   $sqlQuery = "SELECT *
                  FROM safe_execs
                 WHERE EXCLUSIVE = 1
                 ORDER BY PRIORITY DESC, ID LIMIT 5";

   $safe_execs = SQLSelect($sqlQuery);
   $total = count($safe_execs);

   for ($i = 0; $i < $total; $i++)
   {
      if (IsWindowsOS()) {
       $command = utf2win($safe_execs[$i]['COMMAND']);
      } else {
       $command = $safe_execs[$i]['COMMAND'];
      }
      $sqlQuery = "DELETE
                     FROM safe_execs
                    WHERE ID = '" . $safe_execs[$i]['ID'] . "'";

      SQLExec($sqlQuery);

      echo date("H:i:s") . " Executing (exclusive): " . $command . "\n";
      DebMes("Executing (exclusive): " . $command);

      exec($command);
   }

   $sqlQuery = "SELECT *
                  FROM safe_execs
                 WHERE EXCLUSIVE = 0
                 ORDER BY PRIORITY DESC, ID";

   $safe_execs = SQLSelect($sqlQuery);
   $total = count($safe_execs);

   for ($i = 0; $i < $total; $i++)
   {
      if (IsWindowsOS()) {
       $command = utf2win($safe_execs[$i]['COMMAND']);
      } else {
       $command = $safe_execs[$i]['COMMAND'];
      }
      $sqlQuery = "DELETE
                     FROM safe_execs
                    WHERE ID = '" . $safe_execs[$i]['ID'] . "'";

      SQLExec($sqlQuery);

      echo date("H:i:s") . " Executing: " . $command . "\n";
      DebMes("Executing: " . $command);

      execInBackground($command);
   }

   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
