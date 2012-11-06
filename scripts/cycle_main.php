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

 $timerClass=SQLSelectOne("SELECT * FROM classes WHERE TITLE LIKE 'timer'");
 $o_qry=1;
 if ($timerClass['SUB_LIST']!='') {
  $o_qry.=" AND (CLASS_ID IN (".$timerClass['SUB_LIST'].") OR CLASS_ID=".$timerClass['ID'].")";
 } else {
  $o_qry.=" AND 0";
 }

 $old_minute=date('i');
 $old_hour=date('h');
 $old_date=date('Y-m-d');

 while(1) {

  echo date("H:i:s")." running ".basename(__FILE__)."\n";

  if (!$updated_time || (time()-$updated_time)>1*60*60) {
   //Log activity every hour
   DebMes("Cycle running OK: ".basename(__FILE__));
   $updated_time=time();
  }


  $m=date('i');
  $h=date('h');
  $dt=date('Y-m-d');
  if ($m!=$old_minute) {
   echo "new minute\n";
   $objects=SQLSelect("SELECT ID, TITLE FROM objects WHERE $o_qry");
   $total=count($objects);
   for($i=0;$i<$total;$i++) {
    echo $objects[$i]['TITLE']."->onNewMinute\n";
    getObject($objects[$i]['TITLE'])->raiseEvent("onNewMinute");
    getObject($objects[$i]['TITLE'])->setProperty("time", date('Y-m-d H:i:s'));
   }
   $old_minute=$m;
  }
  if ($h!=$old_hour) {
   echo "new hour\n";
   $old_hour=$h;
   $objects=SQLSelect("SELECT ID, TITLE FROM objects WHERE $o_qry");
   $total=count($objects);
   for($i=0;$i<$total;$i++) {
    getObject($objects[$i]['TITLE'])->raiseEvent("onNewHour");
   }
  }
  if ($dt!=$old_date) {
   echo "new day\n";
   $old_date=$dt;
  }


  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }

  sleep(1);


 }

 DebMes("Unexpected close of cycle: ".basename(__FILE__));

?>