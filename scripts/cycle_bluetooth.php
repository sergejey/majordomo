<?	
/*
* @version 0.4 (06.09.2011 bug fixed)
*/

 chdir(dirname(__FILE__).'/../');

 include_once("./config.php");
 include_once("./lib/loader.php");
 include_once("./lib/threads.php");

 set_time_limit(0);

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();

 if (!Defined('SETTINGS_BLUETOOTH_CYCLE') || SETTINGS_BLUETOOTH_CYCLE==0) {
  exit;
 }


 $bt_devices=array();
 
 //windows file
 $devices_file=SERVER_ROOT."/apps/bluetoothview/devices.txt";
 //linux command
 $bts_cmd = 'hcitool scan | grep ":"';

 $first_run=1;
 $skip_counter=0;
 while(1) {
  $skip_counter++;
  if ($skip_counter>=30) {
   $skip_counter=0;
   echo "Running bluetooth scanner\n";
   $data='';
   if (substr(php_uname(), 0, 7) == "Windows") {   
   // windows scanner
    @unlink($devices_file);   
    exec(SERVER_ROOT.'/apps/bluetoothview/bluetoothview.exe /stab '.$devices_file);   
    if (file_exists($devices_file)) {
     $data=(LoadFile($devices_file));   
     sleep(5);	
    }
   } else {
   //linux scanner
    ob_start();  passthru($bts_cmd); $bt_scan_arr = explode("\n", ob_get_contents()); ob_end_clean();   
	$lines=array();
    for ($i = 0; $i < count($bt_scan_arr) - 1; $i++) {
      $btstr = explode("\t", $bt_scan_arr[$i]);
      $btaddr[$i] = $btstr[1];
      $btname[$i] = rtrim($btstr[2]);
	  $lines[]=$i."\t".$btname[$i]."\t".$btaddr[$i];
    }
	$data=implode("\n",$lines);
   }
   $last_scan=time();
   if ($data) {
   $data=str_replace(chr(0), '', $data);
   $data=str_replace("\r", '', $data);
   $lines=explode("\n", $data);
   $total=count($lines);
   for($i=0;$i<$total;$i++) {
    $fields=explode("\t", $lines[$i]);
    $title=trim($fields[1]);
    $mac=trim($fields[2]);
    $user=array();
    if ($mac!='') {
     if (!$bt_devices[$mac]) { // && !$first_run
      //new device found
      echo date('Y/m/d H:i:s')." Device found: $mac\n";
      $rec=SQLSelectOne("SELECT * FROM btdevices WHERE MAC LIKE '".$mac."'");
      $rec['LAST_FOUND']=date('Y/m/d H:i:s');
      $rec['LOG']='Device found '.date('Y/m/d H:i:s')."\n".$rec['LOG'];
     
      if (!$rec['ID']) {
       $rec['FIRST_FOUND']=$rec['LAST_FOUND'];
       $rec['MAC']=strtolower($mac);
       if ($title!='') {
        $rec['TITLE']='Устройство: '.$title;
       } else {
        $rec['TITLE']='Новое устройство';
       }
       $new=1;
       SQLInsert('btdevices', $rec);
      } else {
       $new=0;
       if ($rec['USER_ID']) {
        $user=SQLSelectOne("SELECT * FROM users WHERE ID='".$rec['USER_ID']."'");
       }
       SQLUpdate('btdevices', $rec);
      }
      getObject('BlueDev')->raiseEvent("Found", array('mac'=>$mac, 'user'=>$user['NAME'],'new'=>$new));
     } else {
      $rec=SQLSelectOne("SELECT * FROM btdevices WHERE MAC LIKE '".$mac."'");
      $rec['LAST_FOUND']=date('Y/m/d H:i:s');
      if ($title!='') { // && $rec['TITLE']=='Новое устройство'
        $rec['TITLE']='Устройство: '.$title;
      }
      if ($rec['ID']) {
       SQLUpdate('btdevices', $rec);
      }
     }
     $bt_devices[$mac]=$last_scan;
    }
   }

   foreach($bt_devices as $k=>$v) {
    if ($v!=$last_scan) {
     //device removed
     echo date('Y/m/d H:i:s')." Device gone: $k\n";
     $user=array();
     $rec=SQLSelectOne("SELECT * FROM btdevices WHERE MAC LIKE '".$k."'");
     if ($rec['ID']) {
      $rec['LOG']='Device lost '.date('Y/m/d H:i:s')."\n".$rec['LOG'];
      SQLUpdate('btdevices', $rec);
       if ($rec['USER_ID']) {
        $user=SQLSelectOne("SELECT * FROM users WHERE ID='".$rec['USER_ID']."'");
       }
     }
     getObject('BlueDev')->raiseEvent("Lost", array('mac'=>$k, 'user'=>$user['NAME']));

     unset($bt_devices[$k]);
    }
   }

  }
  } else {
   echo "Running Bluetooth monitor.";
  }
  $first_run=0;
  
  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }
  
  
  sleep(1);
 }

 $db->Disconnect(); // closing database connection

?>