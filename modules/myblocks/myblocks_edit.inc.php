<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='myblocks';
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
  //updating 'CATEGORY_ID' (int)
   global $category_id;
   $rec['CATEGORY_ID']=(int)$category_id;
  //updating 'BLOCK_TYPE' (char)
   global $block_type;
   $rec['BLOCK_TYPE']=trim($block_type);
  //updating 'BLOCK_COLOR' (int)
   global $block_color;
   $rec['BLOCK_COLOR']=(int)$block_color;
  //updating 'SCRIPT_ID' (int)
   global $script_id;
   $rec['SCRIPT_ID']=(int)$script_id;
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

  $out['CATEGORIES']=SQLSelect("SELECT * FROM myblocks_categories ORDER BY TITLE");
  $out['SCRIPTS']=SQLSelect("SELECT * FROM scripts ORDER BY TITLE");

?>