<?php
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='methods';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'Object ID' (int)
   if (IsSet($this->object_id)) {
    $rec['OBJECT_ID']=$this->object_id;
   } else {
   global $object_id;
   $rec['OBJECT_ID']=(int)$object_id;
   }
  //updating 'Class ID' (int, required)
   if (IsSet($this->class_id)) {
    $rec['CLASS_ID']=$this->class_id;
   } else {
   global $class_id;
   $rec['CLASS_ID']=(int)$class_id;
   /*
   if (!$rec['CLASS_ID']) {
    $out['ERR_CLASS_ID']=1;
    $ok=0;
   }
   */
   }
  //updating 'Titile' (varchar, required)
   $rec['TITLE']=gr('title','trim');
   $rec['TITLE']=str_replace(' ','',$rec['TITLE']);
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   global $call_parent;
   $rec['CALL_PARENT']=(int)$call_parent;

  //updating 'Description' (text)
   global $description;
   $rec['DESCRIPTION']=$description;
  //updating 'Code' (text)
   global $code;
   $rec['CODE']=$code;

   global $run_type;

       if ($run_type=='script') {
        global $script_id;
        $rec['SCRIPT_ID']=$script_id;
       } else {
        $rec['SCRIPT_ID']=0;
       }


   if ($rec['CODE']!='' && $run_type=='code') {
    //echo $content;
    $errors=php_syntax_error($code);
    if ($errors) {
     $out['ERR_CODE']=1;
     $out['ERRORS']=nl2br($errors);
     $ok=0;
    }
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

  global $overwrite;
  if ($overwrite) {
   $tmp=SQLSelectOne("SELECT * FROM methods WHERE ID='".(int)$overwrite."'");
   unset($tmp['ID']);
   foreach($tmp as $k=>$v) {
    $out[$k]=htmlspecialchars($v);
   }
  }

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");


?>