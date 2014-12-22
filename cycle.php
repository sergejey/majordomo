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
while(!$connected) 
{
   $connected = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
   sleep(5);
}

if (file_exists('./reboot')) {
 @unlink('./reboot');
}

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
 
include_once("./load_settings.php");

echo "CONNECTED TO DB\n";

include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

echo "Running startup maintenance\n";
include("./scripts/startup_maintenance.php");

getObject('ThisComputer')->raiseEvent("StartUp");

// 1 second sleep
sleep(1); 

// getting list of /scripts/cycle_*.php files to run each in separate thread
$cycles = array();
 
if ($lib_dir = @opendir("./scripts")) 
{
   while (($lib_file = readdir($lib_dir)) !== false) 
   {
      if ((preg_match("/^cycle_.+?\.php$/", $lib_file))) 
      {
         $cycles[]='./scripts/'.$lib_file;
      }
   }
   closedir($lib_dir);
}

$threads = new Threads;

if (defined('PATH_TO_PHP')) {
 $threads->phpPath = PATH_TO_PHP;
} else {
 if (substr(php_uname(), 0, 7) == "Windows") {
  $threads->phpPath = '..\server\php\php.exe';
 } else {
  $threads->phpPath = 'php';
 }
}

foreach($cycles as $path) 
{
   if (file_exists($path)) 
   {
      DebMes("Starting ".$path." ... ");
      echo "Starting ".$path." ... ";
   
      if ((preg_match("/_X/", $path))) 
      {
         //для начала убедимся, что мы в Линуксе. Иначе удаленный запуск этих скриптов не делаем
         if (substr(php_uname(), 0, 5) == "Linux") 
         {
            $display = '101';
      
            //Попробуем получить номер Дисплея из имени файла
            if ((preg_match("/_X(.+)_/", $path,$displays))) 
            {
               if (count($displays)>1) 
               {
                  $display = $displays[1];
               }
            }
      
            //запускаем Линуксовый поцесс на дисплее, номер которого в имени файла после _X
            $pipe_id = $threads->newXThread($path, $display); 
         }
      } 
      else 
      {
         $pipe_id = $threads->newThread($path);
      }
   
      $pipes[$pipe_id] = $path;
   
      echo "OK\n";
   }
}

echo "ALL CYCLES STARTED\n";

if (!is_array($restart_threads)) {
 $restart_threads=array(
                       'cycle_execs.php', 
                       'cycle_main.php', 
                       'cycle_ping.php', 
                       'cycle_rss.php', 
                       'cycle_scheduler.php', 
                       'cycle_states.php', 
                       'cycle_watchfolders.php', 
                       'cycle_webvars.php');
}

while (false !== ($result = $threads->iteration())) 
{
   if (!empty($result))  {
    //echo "Res: ".$result."\n---------------------\n";
    if (preg_match_all('/THREAD CLOSED:.+?(\.\/scripts\/cycle\_.+?\.php)/is', $result, $matches) && !file_exists('./reboot')) {
     $total_m=count($matches[1]);
     for($im=0;$im<$total_m;$im++) {
      $closed_thread=$matches[1][$im];
      foreach($restart_threads as $item) {
       if (preg_match('/'.$item.'/is', $closed_thread)) {
        //restart
        DebMes("RESTARTING: ".$closed_thread);
        echo "RESTARTING: ".$closed_thread."\n";
        registerError('cycle_stop', $closed_thread);
        $pipe_id = $threads->newThread($closed_thread);
       }
      }
     }
    }
   }
}


@unlink('./reboot');

// closing database connection
$db->Disconnect(); 

?>