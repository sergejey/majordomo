<?php
/**
 * Timer Cycle script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.4
 */

chdir(dirname(__FILE__));

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

$connected = 0;

while (!$connected)
{
   echo "Connecting to database..." . PHP_EOL;
   $connected = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
   sleep(5);
}

if (file_exists('./reboot'))
   unlink('./reboot');

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

echo "CONNECTED TO DB" . PHP_EOL;

echo "Running startup maintenance" . PHP_EOL;

//restoring database backup (if was saving periodically)
$filename  = ROOT . 'database_backup/db.sql';
if (file_exists($filename))
{
   echo "Running: mysql -u " . DB_USER . " -p" . DB_PASSWORD . " " . DB_NAME . " <" . $filename . PHP_EOL;
   $mysql_path = (substr(php_uname(), 0, 7) == "Windows") ? SERVER_ROOT . "/server/mysql/bin/mysql" : 'mysql';
   $mysqlParam = " -u " . DB_USER;
   if (DB_PASSWORD != '') $mysqlParam .= " -p" . DB_PASSWORD;
   $mysqlParam .= " " . DB_NAME . " <" . $filename;
   exec($mysql_path . $mysqlParam);
}

//reinstalling modules
/*
        $source=ROOT.'modules';
        if ($dir = @opendir($source)) { 
          while (($file = readdir($dir)) !== false) { 
           if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) { // && !file_exists($source."/".$file."/installed")
            //echo "Removing file ".ROOT."modules/".$file."/installed"."\n";
            @unlink(ROOT."modules/".$file."/installed");
           }
          }
         }
*/

echo "Checking modules.\n";
// continue startup
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();


//removing cached data
echo "Clearing the cache.\n";
SQLExec("TRUNCATE TABLE `cached_values`");

$run_from_start = 1;
include("./scripts/startup_maintenance.php");
$run_from_start = 0;

setGlobal('ThisComputer.started_time', time());
getObject('ThisComputer')->raiseEvent("StartUp");

// 1 second sleep
sleep(1);

// getting list of /scripts/cycle_*.php files to run each in separate thread
$cycles = array();

if (is_dir("./scripts"))
{
   if ($lib_dir = opendir("./scripts"))
   {
      while (($lib_file = readdir($lib_dir)) !== false)
      {
         if ((preg_match("/^cycle_.+?\.php$/", $lib_file)))
            $cycles[] = './scripts/' . $lib_file;
      }
      closedir($lib_dir);
   }
}

$threads = new Threads;

if (defined('PATH_TO_PHP'))
   $threads->phpPath = PATH_TO_PHP;
else
   $threads->phpPath = IsWindowsOS() ? '..\server\php\php.exe' : 'php';

foreach ($cycles as $path)
{
   if (file_exists($path))
   {
      DebMes("Starting " . $path . " ... ");
      echo "Starting " . $path . " ... ";
      
      if ((preg_match("/_X/", $path)))
      {
         if (!IsWindowsOS())
         {
            $display = '101';
            if ((preg_match("/_X(.+)_/", $path, $displays)))
            {
               if (count($displays) > 1)
               {
                  $display = $displays[1];
               }
            }
            $pipe_id = $threads->newXThread($path, $display);
         }
      }
      else
      {
         $pipe_id = $threads->newThread($path);
      }
      
      $pipes[$pipe_id] = $path;
      
      echo "OK" . PHP_EOL;
   }
}

echo "ALL CYCLES STARTED" . PHP_EOL;

if (!is_array($restart_threads))
{
   $restart_threads = array(
                         'cycle_execs.php',
                         'cycle_main.php',
                         'cycle_ping.php',
                         'cycle_scheduler.php',
                         'cycle_states.php',
                         'cycle_webvars.php');

}

 if (!defined('DISABLE_WEBSOCKETS') || DISABLE_WEBSOCKETS==0) {
  $restart_threads[]='cycle_websockets.php';
 }

$last_restart=array();


while (false !== ($result = $threads->iteration()))
{
   if (!empty($result))
   {
      //echo "Res: " . $result . PHP_EOL . "---------------------" . PHP_EOL;
      $closePattern = '/THREAD CLOSED:.+?(\.\/scripts\/cycle\_.+?\.php)/is';
      if (preg_match_all($closePattern, $result, $matches) && !file_exists('./reboot'))
      {
         $total_m = count($matches[1]);
         
         for ($im = 0; $im < $total_m; $im++)
         {
            $closed_thread = $matches[1][$im];
            
            foreach ($restart_threads as $item)
            {
               if (preg_match('/' . $item . '/is', $closed_thread) && (!$last_restart[$closed_thread] || (time()-$last_restart[$closed_thread])>30))
               {
                  //restart
                  $last_restart[$closed_thread]=time();
                  DebMes("RESTARTING: " . $closed_thread);
                  echo "RESTARTING: " . $closed_thread . PHP_EOL;
                  if (!preg_match('/websockets/is', $closed_thread)) {
                   registerError('cycle_stop', $closed_thread);
                  }
                  $pipe_id = $threads->newThread($closed_thread);
               }
            }
         }
      }
   }
}


 unlink('./reboot');

 // closing database connection
 $db->Disconnect();

