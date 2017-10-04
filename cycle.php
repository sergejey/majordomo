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
   if (function_exists('mysqli_connect')) {
    $connected = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
   } else {
    $connected = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
   }
   sleep(5);
}

if (file_exists('./reboot'))
   unlink('./reboot');

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

echo "CONNECTED TO DB" . PHP_EOL;

echo "Running startup maintenance" . PHP_EOL;

//restoring database backup (if was saving periodically)
$filename  = ROOT . 'database_backup/db.sql';
if (file_exists($filename))
{
   echo "Running: mysql restore from file: " . $filename . PHP_EOL;
   DebMes("Running: mysql restore from file: " . $filename);
   $mysql_path = (substr(php_uname(), 0, 7) == "Windows") ? SERVER_ROOT . "/server/mysql/bin/mysql" : 'mysql';
   $mysqlParam = " -u " . DB_USER;
   if (DB_PASSWORD != '') $mysqlParam .= " -p" . DB_PASSWORD;
   $mysqlParam .= " " . DB_NAME . " <" . $filename;
   exec($mysql_path . $mysqlParam);
}

include_once("./load_settings.php");


//reinstalling modules
/*
        $source=ROOT.'modules';
        if ($dir = @opendir($source)) {
          while (($file = readdir($dir)) !== false) {
           if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) { // && !file_exists($source."/".$file."/installed")
            @unlink(ROOT."modules/".$file."/installed");
           }
          }
         }
*/


echo "Checking modules.\n";

        //force check installed data
        $source=ROOT.'modules';
        if ($dir = @opendir($source)) { 
          while (($file = readdir($dir)) !== false) { 
           if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) {
            @unlink(ROOT."modules/".$file."/installed");
           }
          }
         }
         @unlink(ROOT."modules/control_modules/installed");

// continue startup
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();


//removing cached data
echo "Clearing the cache.\n";
SQLExec("TRUNCATE TABLE `cached_values`");

if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE==1) {
   // split data into multiple tables
   $phistory_values = SQLSelect("SELECT VALUE_ID, COUNT(*) as TOTAL FROM phistory GROUP BY VALUE_ID");
   $total = count($phistory_values);
   for($i=0;$i<$total;$i++) {
      $value_id=$phistory_values[$i]['VALUE_ID'];
      $total_data=$phistory_values[$i]['TOTAL'];
      DebMes("Processing data for value $value_id ($total_data) ... ");
      echo "Processing data for value $value_id ($total_data) ... ";
      $table_name = createHistoryTable($value_id);
      moveDataFromMainHistoryToTable($value_id);
      DebMes("Processing of $value_id finished.");
      echo "OK\n";
   }
} else {
  //combine data into single table
   $data=SQLSelect("SHOW TABLES;");
   $tables=array();
   foreach($data as $v) {
      foreach($v as $k=>$v2) {
         $tables[]=$v2;
      }
   }
   foreach($tables as $table) {
      if (preg_match('/phistory_value_(\d+)/',$table,$m)) {
         $value_id=$m[1];
         echo "Processing table: $table ($value_id) ...\n";
         DebMes("Processing data for value $value_id ($table) ... ");
         moveDataFromTableToMainHistory($value_id);
         DebMes("Processing of $value_id finished.");
         echo "OK\n";
      }
   }
}

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

      if (preg_match('/(cycle_.+?)\.php/is',$path,$m)) {
         $title = $m[1];
         if (getGlobal($title.'Disabled')) {
            DebMes("Cycle ".$title." disabled. Skipping.");
            continue;
         }
         if (getGlobal($title.'Control')!='') {
          setGlobal($title.'Control', '');
         }
      }


      DebMes("Starting " . $path . " ... ");
      echo "Starting " . $path . " ... \n";

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

$last_cycles_control_check=time();

$auto_restarts=array();

while (false !== ($result = $threads->iteration()))
{

   $already_started = array();
   if ((time()-$last_cycles_control_check)>=5) {
      $last_cycles_control_check=time();

      $to_start=array();
      $to_stop=array();
      $to_restart=array();
      $auto_restarts=array();

      $qry="1 AND (TITLE LIKE 'cycle%Run' OR TITLE LIKE 'cycle%Control')";
      $cycles=SQLSelect("SELECT properties.* FROM properties WHERE $qry ORDER BY TITLE");
      $total = count($cycles);

      $seen=array();
      for ($i = 0; $i < $total; $i++) {
         $title = $cycles[$i]['TITLE'];
         $title = preg_replace('/Run$/', '', $title);
         $title = preg_replace('/Control$/', '', $title);
         if (isset($seen[$title])) {
            continue;
         }
         $seen[$title]=1;
         $control=getGlobal($title.'Control');
         $auto_restart=getGlobal($title.'AutoRestart');
         if ($auto_restart) {
            $auto_restarts[]=$title;
         }
         if ($control!='') {
            if ($control=='stop') {
               $to_stop[]=$title;
            } elseif ($control=='start') {
               $to_start[]=$title;
            } elseif ($control=='restart') {
               $to_stop[]=$title;
               $to_start[]=$title;
            }
            setGlobal($title.'Control','');
         }

      }

      $some_closed=0;
      $is_running=array();
      foreach($threads->commandLines as $id=>$cmd) {
         if (preg_match('/(cycle_.+?)\.php/is',$cmd,$m)) {
            $title=$m[1];
            if (in_array($title,$to_stop) || in_array($title,$to_restart)) {
               DebMes("Closing service ".$title." (id: $id)");
               $threads->closeThread($id);
               $some_closed=1;
            } else {
               $is_running[]=$title;
            }
         }
      }

      if ($some_closed) {
         sleep(3);
      }

      foreach($to_start as $title) {
         if (!in_array($title,$is_running)) {
            $cmd='./scripts/'.$title.'.php';
            DebMes("Starting service ".$title.' ('.$cmd.')');
            $pipe_id = $threads->newThread($cmd);
         }
         $already_started[] = $title;
      }

   }

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
            $need_restart=0;
            if (preg_match('/(cycle_.+?)\.php/is',$closed_thread,$m)) {
               $title=$m[1];
               if (in_array($title,$already_started)) {
                  continue;
               }
               setGlobal($title.'Run','');
               if (in_array($title,$auto_restarts)) {
                  $need_restart=1;
               }
            }
            foreach ($restart_threads as $item)
            {
               if (preg_match('/' . $item . '/is', $closed_thread) && (!$last_restart[$closed_thread] || (time()-$last_restart[$closed_thread])>30))
               {
                  //restart
                  $need_restart=1;
                  $last_restart[$closed_thread]=time();
               }
            }
            if ($need_restart) {
               DebMes("AUTO-RECOVERY: " . $closed_thread);
               if (!preg_match('/websockets/is', $closed_thread)) {
                  registerError('cycle_stop', $closed_thread);
               }
               $pipe_id = $threads->newThread($closed_thread);
            }
         }
      }
   }
}

 unlink('./reboot');
 // closing database connection
 $db->Disconnect();
