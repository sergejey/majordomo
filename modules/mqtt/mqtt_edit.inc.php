<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='mqtt';
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
  //updating 'LOCATION_ID' (select)
   if (IsSet($this->location_id)) {
    $rec['LOCATION_ID']=$this->location_id;
   } else {
   global $location_id;
   $rec['LOCATION_ID']=$location_id;
   }
  //updating 'PATH' (varchar, required)
   global $path;
   $rec['PATH']=$path;
   if ($rec['PATH']=='') {
    $out['ERR_PATH']=1;
    $ok=0;
   }

   $old_linked_object=$rec['LINKED_OBJECT'];
   $old_linked_property=$rec['LINKED_PROPERTY'];


  //updating 'LINKED_OBJECT' (varchar)
   global $linked_object;
   $rec['LINKED_OBJECT']=$linked_object;
  //updating 'LINKED_PROPERTY' (varchar)
   global $linked_property;
   $rec['LINKED_PROPERTY']=$linked_property;


  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }

    if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
     addLinkedProperty($rec['LINKED_OBJECT'], $rec['LINKED_PROPERTY'], $this->name);
    }
    if ($old_linked_object && $old_linked_object!=$rec['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$rec['LINKED_PROPERTY']) {
     removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
    }

    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }

   global $new_value;
   if ($new_value) {
    $rec['VALUE']=$new_value;
    SQLUpdate('mqtt', $rec);
    $this->setProperty($rec['ID'], $new_value, 1);
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
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>