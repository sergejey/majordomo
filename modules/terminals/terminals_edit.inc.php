<?php
/*
* @version 0.3 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='terminals';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'NAME' (varchar, required)
   global $name;
   $rec['NAME']=$name;
   if ($rec['NAME']=='') {
    $out['ERR_NAME']=1;
    $ok=0;
   }
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;

   global $canplay;
   $rec['CANPLAY']=(int)$canplay;

   global $majordroid_api;
   $rec['MAJORDROID_API']=(int)$majordroid_api;

   global $player_type;
   $rec['PLAYER_TYPE']=$player_type;

   global $player_port;
   $rec['PLAYER_PORT']=$player_port;
   global $player_username;
   $rec['PLAYER_USERNAME']=$player_username;
   global $player_password;
   $rec['PLAYER_PASSWORD']=$player_password;

   global $linked_object;
   $rec['LINKED_OBJECT']=$linked_object;

   global $level_linked_property;
   $rec['LEVEL_LINKED_PROPERTY']=$level_linked_property;

   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   global $host;
   $rec['HOST']=$host;
   if (!$rec['HOST']) {
    $out['ERR_HOST']=1;
    $ok=0;
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
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>