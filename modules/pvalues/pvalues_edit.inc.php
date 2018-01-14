<?php
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='pvalues';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'Property ID' (int, required)
   global $property_id;
   $rec['PROPERTY_ID']=(int)$property_id;
   /*
   if (!$rec['PROPERTY_ID']) {
    $out['ERR_PROPERTY_ID']=1;
    $ok=0;
   }
   */
  //updating 'Object ID' (int, required)
   global $object_id;
   $rec['OBJECT_ID']=(int)$object_id;
   /*
   if (!$rec['OBJECT_ID']) {
    $out['ERR_OBJECT_ID']=1;
    $ok=0;
   }
   */
  //updating 'Value' (varchar)
   global $value;
   $rec['VALUE']=$value.'';
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
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>