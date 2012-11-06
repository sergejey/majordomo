<?

 chdir(dirname(__FILE__).'/../');

 include_once("./config.php");
 include_once("./lib/loader.php");
 include_once("./lib/threads.php");

 set_time_limit(0);

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();

 include_once(DIR_MODULES.'watchfolders/watchfolders.class.php');
 $watchfolders=new watchfolders();


 while(1) {

  echo date("H:i:s")." running ".basename(__FILE__)."\n";

  if (!$updated_time || (time()-$updated_time)>1*60*60) {
   //Log activity every hour
   DebMes("Cycle running OK: ".basename(__FILE__));
   $updated_time=time();
  }


  $watchfolders->checkAllFolders(); // checking all watching folders

  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }

  sleep(1);


 }

 DebMes("Unexpected close of cycle: ".basename(__FILE__));

?>