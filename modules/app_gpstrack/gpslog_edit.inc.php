<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='gpslog';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'ADDED' (datetime)
   global $added_date;
   global $added_minutes;
   global $added_hours;
   $rec['ADDED']=toDBDate($added_date)." $added_hours:$added_minutes:00";
  //updating 'LAT' (float)
   global $lat;
   $rec['LAT']=(float)$lat;
  //updating 'LON' (float)
   global $lon;
   $rec['LON']=(float)$lon;
  //updating 'ALT' (float)
   global $alt;
   $rec['ALT']=(float)$alt;
  //updating 'PROVIDER' (varchar)
   global $provider;
   $rec['PROVIDER']=$provider;
  //updating 'SPEED' (float)
   global $speed;
   $rec['SPEED']=(float)$speed;
  //updating 'BATTLEVEL' (int)
   global $battlevel;
   $rec['BATTLEVEL']=(int)$battlevel;
  //updating 'CHARGING' (int)
   global $charging;
   $rec['CHARGING']=(int)$charging;
  //updating 'DEVICEID' (varchar)
   global $deviceid;
   $rec['DEVICEID']=$deviceid;
  //updating 'DEVICE_ID' (int)
   if (IsSet($this->device_id)) {
    $rec['DEVICE_ID']=$this->device_id;
   } else {
   global $device_id;
   $rec['DEVICE_ID']=(int)$device_id;
   }
  //updating 'LOCATION_ID' (int)
   if (IsSet($this->location_id)) {
    $rec['LOCATION_ID']=$this->location_id;
   } else {
   global $location_id;
   $rec['LOCATION_ID']=(int)$location_id;
   }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if ($rec['ADDED']!='') {
   $tmp=explode(' ', $rec['ADDED']);
   $out['ADDED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $added_hours=$tmp2[0];
   $added_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$added_minutes) {
    $out['ADDED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['ADDED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$added_hours) {
    $out['ADDED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['ADDED_HOURS'][]=array('TITLE'=>$title);
   }
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>