<?
/**
* Timer Cycle script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.4
*/

 include_once("./config.php");
 include_once("./lib/loader.php");

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

 $old_minute=date('i');
 $old_hour=date('h');
 $old_date=date('Y-m-d');


 getObject('ThisComputer')->raiseEvent("StartUp");

   $timerClass=SQLSelectOne("SELECT * FROM classes WHERE TITLE LIKE 'timer'");
   $o_qry=1;
   if ($timerClass['SUB_LIST']!='') {
    $o_qry.=" AND (CLASS_ID IN (".$timerClass['SUB_LIST'].") OR CLASS_ID=".$timerClass['ID'].")";
   } else {
    $o_qry.=" AND 0";
   }

 $long_delay_limit=1*60; //1 minute delay (sleeped ?)
 $tm=getObject("ThisComputer")->getProperty("checked"); // should be taken from database instead
 if (!$tm) {
  $tm=time();
 }
 $start_time=time();

 include_once(DIR_MODULES.'rss_channels/rss_channels.class.php');
 $rss_ch=new rss_channels();

 include_once(DIR_MODULES.'pinghosts/pinghosts.class.php');
 $pinghosts=new pinghosts();

 include_once(DIR_MODULES.'webvars/webvars.class.php');
 $webvars=new webvars();


 include_once(DIR_MODULES.'watchfolders/watchfolders.class.php');
 $watchfolders=new watchfolders();

 if (defined('ONEWIRE_SERVER')) {
  include_once(DIR_MODULES.'onewire/onewire.class.php');
  $onw=new onewire();
 }



 while (1) {
  if ((time()-$tm)>$long_delay_limit) {
   // where am I ?
   $sleeptime=time()-$tm;
   echo "waked up (sleeped: $sleeptime)\n";
   getObject('ThisComputer')->raiseEvent("WakedUp", array('sleeptime'=>$sleeptime));
  }

  $tm=time();
  getObject("ThisComputer")->setProperty("checked", $tm);
  
  $tmp=SQLSelect("SELECT 1 as WORKS"); // checking mysql connection

  echo date('Y-m-d H:i:s')."\n";

  SQLExec("DELETE FROM safe_execs WHERE ADDED<'".date('Y-m-d H:i:s', time()-180)."'");

  $safe_execs=SQLSelect("SELECT * FROM safe_execs WHERE EXCLUSIVE=1 ORDER BY PRIORITY DESC, ID LIMIT 1");
  $total=count($safe_execs);
  for($i=0;$i<$total;$i++) {
   $command=utf2win($safe_execs[$i]['COMMAND']);
   SQLExec("DELETE FROM safe_execs WHERE ID='".$safe_execs[$i]['ID']."'");
   echo "Executing (exclusive): ".$command."\n";
   DebMes("Executing (exclusive): ".$command);
   exec($command);
  }


  $safe_execs=SQLSelect("SELECT * FROM safe_execs WHERE EXCLUSIVE=0 ORDER BY PRIORITY DESC, ID");
  $total=count($safe_execs);
  for($i=0;$i<$total;$i++) {
   $command=utf2win($safe_execs[$i]['COMMAND']);
   SQLExec("DELETE FROM safe_execs WHERE ID='".$safe_execs[$i]['ID']."'");
   echo "Executing: ".$command."\n";
   DebMes("Executing: ".$command);
   execInBackground($command);
  }



  runScheduledJobs();

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

  //updating RSS channels
  $to_update=SQLSelect("SELECT ID, TITLE FROM rss_channels WHERE NEXT_UPDATE<=NOW() LIMIT 1");
  $total=count($to_update);
  for($i=0;$i<$total;$i++) {
   $rss_ch->updateChannel($to_update[$i]['ID']);
  }

  $pinghosts->checkAllHosts(1); // checking all hosts

  $webvars->checkAllVars(); // check all web vars

  $watchfolders->checkAllFolders(); // checking all watching folders

  if (defined('ONEWIRE_SERVER')) {
   $onw->updateStarred(); // check starred 1wire properties
   $onw->updateDevices(); // check all 1wire devices
  }


  if (file_exists('./reboot') || ((time()-$start_time)>7*24*60*60)) {
   $db->Disconnect();
   @unlink('./reboot');
   sleep(5);
   exit;
  }

  sleep(1); // 1 second sleep

 }

 $db->Disconnect(); // closing database connection


?>