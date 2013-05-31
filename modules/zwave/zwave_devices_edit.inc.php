<?
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='zwave_devices';
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
   /*
  //updating 'STATUS' (int)
   global $status;
   $rec['STATUS']=(int)$status;
  //updating 'AUTO_POLL' (int)
   global $auto_poll;
   $rec['AUTO_POLL']=(int)$auto_poll;
  //updating 'LATEST_UPDATE' (datetime)
   global $latest_update_date;
   global $latest_update_minutes;
   global $latest_update_hours;
   $rec['LATEST_UPDATE']=toDBDate($latest_update_date)." $latest_update_hours:$latest_update_minutes:00";
   */
  //updating 'LOCATION_ID' (select)
   if (IsSet($this->location_id)) {
    $rec['LOCATION_ID']=$this->location_id;
   } else {
   global $location_id;
   $rec['LOCATION_ID']=$location_id;
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
  if ($rec['LATEST_UPDATE']!='') {
   $tmp=explode(' ', $rec['LATEST_UPDATE']);
   $out['LATEST_UPDATE_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $latest_update_hours=$tmp2[0];
   $latest_update_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$latest_update_minutes) {
    $out['LATEST_UPDATE_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['LATEST_UPDATE_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$latest_update_hours) {
    $out['LATEST_UPDATE_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['LATEST_UPDATE_HOURS'][]=array('TITLE'=>$title);
   }
  }
  //options for 'LOCATION_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE");
  $locations_total=count($tmp);
  for($locations_i=0;$locations_i<$locations_total;$locations_i++) {
   $location_id_opt[$tmp[$locations_i]['ID']]=$tmp[$locations_i]['TITLE'];
  }
  for($i=0;$i<count($tmp);$i++) {
   if ($rec['LOCATION_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['LOCATION_ID_OPTIONS']=$tmp;

  if ($rec['ID']) {
   $this->pollDevice($rec['ID']);
   $rec=SQLSelectOne("SELECT * FROM zwave_devices WHERE ID='".$rec['ID']."'");

   $properties=SQLSelect("SELECT * FROM zwave_properties WHERE DEVICE_ID='".$rec['ID']."'");
   if ($properties) {
    if ($this->mode=='update') {
     $total=count($properties);
     for($i=0;$i<$total;$i++) {
      global ${'linked_object'.$properties[$i]['ID']};
      global ${'linked_property'.$properties[$i]['ID']};
      if (${'linked_object'.$properties[$i]['ID']} && ${'linked_property'.$properties[$i]['ID']}) {
       $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
       $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
       SQLUpdate('zwave_properties', $properties[$i]);
      }
     }
    }
    $out['PROPERTIES']=$properties;
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