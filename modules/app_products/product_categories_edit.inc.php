<?php
/*
* @version 0.1 (wizard)
*/
  global $parent_id;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='product_categories';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($parent_id) {
   $rec['PARENT_ID']=(int)$parent_id;
  }
  if ($this->mode=='update') {
   $ok=1;
  if ($this->tab=='') {
   $rec['PARENT_ID']=(int)$parent_id;
  }
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'PRIORITY' (int)
   global $priority;
   $rec['PRIORITY']=(int)$priority;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $this->updateTree_product_categories();
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if ($this->tab=='') {
   if ($rec['SUB_LIST']!='') {
    $parents=SQLSelect("SELECT ID, TITLE FROM $table_name WHERE ID!='".$rec['ID']."' AND ID NOT IN (".$rec['SUB_LIST'].") ORDER BY TITLE");
   } else {
    $parents=SQLSelect("SELECT ID, TITLE FROM $table_name WHERE ID!='".$rec['ID']."' ORDER BY TITLE");
   }
   $out['PARENTS']=$parents;
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