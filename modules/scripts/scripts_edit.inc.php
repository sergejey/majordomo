<?php
/*
* @version 0.2 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='scripts';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar)
   global $title;
   $rec['TITLE']=$title;
  //updating 'CODE' (text)
   global $type;
   $rec['TYPE']=$type;

   global $category_id;
   $rec['CATEGORY_ID']=$category_id;

   global $code;

   if ($rec['TYPE']==1) {
    global $xml;
    $rec['XML']=$xml;
    global $blockly_code;
    $code=$blockly_code;
   }


   //echo $code;exit;

   $rec['CODE']=$code;

   if ($rec['CODE']!='') {
    //echo $content;
    $errors=php_syntax_error($rec['CODE']);
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

    global $edit_run;
    if ($edit_run) {
     $this->runScript($rec['ID']);
    }


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

  $out['CATEGORIES']=SQLSelect("SELECT * FROM script_categories ORDER BY TITLE");

?>