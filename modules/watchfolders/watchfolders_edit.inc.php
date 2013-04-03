<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='watchfolders';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE/COMMENTS' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'FOLDER' (varchar)
   global $folder;
   $rec['FOLDER']=$folder;
   $rec['FOLDER']=str_replace('\\', '/', $rec['FOLDER']);
   $rec['FOLDER']=preg_replace('/[\/\\\]$/', '', $rec['FOLDER']);

   if (!$rec['FOLDER'] || !is_dir($rec['FOLDER'])) {
    $out['ERR_FOLDER']=1;
    $ok=0;
   }

   global $check_sub;
   $rec['CHECK_SUB']=(int)$check_sub;

  //updating 'CHECK_MASK' (varchar)
   global $check_mask;
   $rec['CHECK_MASK']=$check_mask;
  //updating 'CHECK_INTERVAL' (int)
   global $check_interval;
   $rec['CHECK_INTERVAL']=(int)$check_interval;
  //updating 'SCRIPT_ID' (int)
   if (IsSet($this->script_id)) {
    $rec['SCRIPT_ID']=$this->script_id;
   } else {
   global $script_id;
   $rec['SCRIPT_ID']=(int)$script_id;
   }
  //updating 'RUN SCRIPT' (select)
   global $script_type;
   $rec['SCRIPT_TYPE']=$script_type;
  //UPDATING RECORD
   if ($ok) {
    $rec['CHECK_RESULTS']='';
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
    $this->checkWatchFolder($rec['ID'], 1);
   } else {
    $out['ERR']=1;
   }
  }
  //options for 'RUN SCRIPT' (select)
  $tmp=explode('|', DEF_SCRIPT_TYPE_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['SCRIPT_TYPE_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $script_type_opt[$value]=$title;
  }
  for($i=0;$i<count($out['SCRIPT_TYPE_OPTIONS']);$i++) {
   if ($out['SCRIPT_TYPE_OPTIONS'][$i]['VALUE']==$rec['SCRIPT_TYPE']) {
    $out['SCRIPT_TYPE_OPTIONS'][$i]['SELECTED']=1;
    $out['SCRIPT_TYPE']=$out['SCRIPT_TYPE_OPTIONS'][$i]['TITLE'];
    $rec['SCRIPT_TYPE']=$out['SCRIPT_TYPE_OPTIONS'][$i]['TITLE'];
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

?>