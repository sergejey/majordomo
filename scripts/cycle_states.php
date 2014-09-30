<?php

chdir(dirname(__FILE__).'/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 

include_once("./load_settings.php");
include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

$checked_time=0;

if ($_GET['once']) {
 setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
 cycleBody();
 echo "OK";
} else {
 while(1) {
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";
   if (time()-$checked_time>5) {
    setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
    $checked_time=time();
    cycleBody();
   }
   if (file_exists('./reboot') || $_GET['onetime']) 
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
 }
 DebMes("Unexpected close of cycle: " . basename(__FILE__));
}

 function cycleBody() {
   // check main system states
   $objects = getObjectsByClass('systemStates');
   $total   = count($objects);
   for($i=0;$i<$total;$i++) 
   {
      $old_state = getGlobal($objects[$i]['TITLE'] . '.stateColor');
      callMethod($objects[$i]['TITLE'] . '.checkState');
      $new_state = getGlobal($objects[$i]['TITLE'] . '.stateColor');
  
      if ($new_state!=$old_state) 
      {
         echo $objects[$i]['TITLE'] . " state changed to " . $new_state . "\n";
         $params=array('STATE'=>$new_state);
         callMethod($objects[$i]['TITLE'] . '.stateChanged', $params);
      }
   }  
 }

?>