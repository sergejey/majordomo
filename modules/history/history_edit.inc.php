<?php
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='history';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'Added' (datetime)
   global $added_date;
   global $added_minutes;
   global $added_hours;
   $rec['ADDED']=toDBDate($added_date)." $added_hours:$added_minutes:00";
  //updating 'Object ID' (int)
   if (IsSet($this->object_id)) {
    $rec['OBJECT_ID']=$this->object_id;
   } else {
   global $object_id;
   $rec['OBJECT_ID']=(int)$object_id;
   }
  //updating 'Method ID' (int)
   if (IsSet($this->method_id)) {
    $rec['METHOD_ID']=$this->method_id;
   } else {
   global $method_id;
   $rec['METHOD_ID']=(int)$method_id;
   }
  //updating 'Value ID' (int)
   if (IsSet($this->value_id)) {
    $rec['VALUE_ID']=$this->value_id;
   } else {
   global $value_id;
   $rec['VALUE_ID']=(int)$value_id;
   }
  //updating 'Old Value' (varchar)
   global $old_value;
   $rec['OLD_VALUE']=$old_value;
  //updating 'New Value' (varchar)
   global $new_value;
   $rec['NEW_VALUE']=$new_value;
  //updating 'Details' (text)
   global $details;
   $rec['DETAILS']=$details;
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