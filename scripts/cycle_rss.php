<?php

 chdir(dirname(__FILE__).'/../');

 include_once("./config.php");
 include_once("./lib/loader.php");
 include_once("./lib/threads.php");

 set_time_limit(0);

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();

 include_once(DIR_MODULES.'rss_channels/rss_channels.class.php');
 $rss_ch=new rss_channels();

 while(1) {

  echo date("H:i:s")." running ".basename(__FILE__)."\n";

  if (!$updated_time || (time()-$updated_time)>1*60*60) {
   //Log activity every hour
   DebMes("Cycle running OK: ".basename(__FILE__));
   $updated_time=time();
  }


  //updating RSS channels
  $to_update=SQLSelect("SELECT ID, TITLE FROM rss_channels WHERE NEXT_UPDATE<=NOW() LIMIT 1");
  $total=count($to_update);
  for($i=0;$i<$total;$i++) {
   $rss_ch->updateChannel($to_update[$i]['ID']);
  }

  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }

  sleep(1);


 }
 DebMes("Unexpected close of cycle: ".basename(__FILE__));

?>