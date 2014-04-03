<?php
/*
* @version 0.1 (wizard)
*/

  global $parent_id;
  $out['PARENT_ID']=$parent_id;


  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='patterns';
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
  //updating 'PATTERN' (text)
   global $pattern;
   $rec['PATTERN']=$pattern;
  //updating 'SCRIPT_ID' (int)

   global $script;
   $rec['SCRIPT']=trim($script);

   global $run_type;

       if ($run_type=='script') {
        global $script_id;
        $rec['SCRIPT_ID']=$script_id;
       } else {
        $rec['SCRIPT_ID']=0;
       }



   if ($rec['SCRIPT']!='' && $run_type=='code') {
    //echo $content;
    $errors=php_syntax_error($rec['SCRIPT']);
    if ($errors) {
     $out['ERR_SCRIPT']=1;
     $out['ERRORS']=nl2br($errors);
     $ok=0;
    }
   }

  //updating 'TIME_LIMIT' (int, required)
   global $time_limit;
   $rec['TIME_LIMIT']=(int)$time_limit;
   /*
   if (!$rec['TIME_LIMIT']) {
    $out['ERR_TIME_LIMIT']=1;
    $ok=0;
   }
   */

   global $is_context;
   $rec['IS_CONTEXT']=(int)$is_context;

   global $is_common_context;
   $rec['IS_COMMON_CONTEXT']=(int)$is_common_context;

   global $matched_context_id;
   $rec['MATCHED_CONTEXT_ID']=(int)$matched_context_id;

   global $timeout;
   $rec['TIMEOUT']=(int)$timeout;

   global $timeout_context_id;
   $rec['TIMEOUT_CONTEXT_ID']=(int)$timeout_context_id;


   global $timeout_script;
   if ($timeout_script!='') {
    $rec['TIMEOUT_SCRIPT']=$timeout_script;
    $errors=php_syntax_error($rec['TIMEOUT_SCRIPT']);
    if ($errors) {
     $out['ERR_TIMEOUT_SCRIPT']=1;
     $out['ERRORS_TIMEOUT_SCRIPT']=nl2br($errors);
     $ok=0;
    }
   } else {
    $rec['TIMEOUT_SCRIPT']='';
   }


   $rec['PARENT_ID']=(int)$parent_id;


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

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
  $out['LOG']=nl2br($rec['LOG']);

  $out['CONTEXTS']=SQLSelect("SELECT ID, TITLE FROM patterns WHERE IS_CONTEXT=1 AND ID!=".(int)$rec['ID']." ORDER BY PARENT_ID, TITLE");
  
  if ($rec['ID']) {
   $out['CHILDREN']=SQLSelect("SELECT ID, TITLE FROM patterns WHERE PARENT_ID='".(int)$rec['ID']."'");
   $out['SAME_LEVEL']=SQLSelect("SELECT ID, TITLE FROM patterns WHERE PARENT_ID='".(int)$rec['PARENT_ID']."'");
  }


?>