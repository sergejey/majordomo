<?
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
 $connected=0;
 while(!$connected) {
  $connected=@mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
  sleep(5);
 }

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database

// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total = count($settings);
for ($i = 0; $i < $total; $i ++)
        Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');
include_once (ROOT . 'languages/default.php');

if (defined('SETTINGS_SITE_TIMEZONE')) {
 ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
}

 echo "CONNECTED TO DB\n";

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();


 echo "Running startup maintenance\n";
 include("./scripts/startup_maintenance.php");


 getObject('ThisComputer')->raiseEvent("StartUp");


 sleep(1); // 1 second sleep


 // getting list of /scripts/cycle_*.php files to run each in separate thread
 $cycles=array();
 if ($lib_dir = @opendir("./scripts")) {
  while (($lib_file = readdir($lib_dir)) !== false) {
    if ((preg_match("/^cycle_.+?\.php$/", $lib_file))) {
     $cycles[]='./scripts/'.$lib_file;
    }
  }
  closedir($lib_dir);
 }


 $threads = new Threads;

 $threads->phpPath = '..\server\php\php.exe';

 foreach($cycles as $path) {
  if (file_exists($path)) {
   DebMes("Starting ".$path." ... ");
   echo "Starting ".$path." ... ";
   $pipe_id=$threads->newThread($path);
   $pipes[$pipe_id]=$path;
   echo "OK\n";
  }
 }

 echo "ALL CYCLES STARTED\n";

 while (false !== ($result = $threads->iteration())) {

     if (!empty($result)) {
         echo $result."\r\n";
     }
 }

 @unlink('./reboot');


 $db->Disconnect(); // closing database connection


?>