<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='gpsdevices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'USER_ID' (select)
   if (IsSet($this->user_id)) {
    $rec['USER_ID']=$this->user_id;
   } else {
   global $user_id;
   $rec['USER_ID']=$user_id;
   }

  //updating 'DEVICEID' (varchar)
   global $deviceid;
   $rec['DEVICEID']=$deviceid;



  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }

    $out['OK']=1;

    if ($rec['DEVICEID']) {
     SQLExec("UPDATE gpslog SET DEVICE_ID='".$rec['ID']."' WHERE DEVICE_ID=0 AND DEVICEID='".DBSafe($rec['DEVICEID'])."'");
    }

   } else {
    $out['ERR']=1;
   }
  }
  //options for 'USER_ID' (select)
  $tmp=SQLSelect("SELECT ID, NAME FROM users ORDER BY NAME");
  $users_total=count($tmp);
  for($users_i=0;$users_i<$users_total;$users_i++) {
   $user_id_opt[$tmp[$users_i]['ID']]=$tmp[$users_i]['NAME'];
  }
  for($i=0;$i<$users_total;$i++) {
   if ($rec['USER_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['USER_ID_OPTIONS']=$tmp;
  if ($rec['UPDATED']!='') {
   $tmp=explode(' ', $rec['UPDATED']);
   $out['UPDATED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $updated_hours=$tmp2[0];
   $updated_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_minutes) {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_hours) {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title);
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