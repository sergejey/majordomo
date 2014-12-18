<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='rss_channels';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'URL' (url)
   global $url;
   $rec['URL']=$url;
   if (!preg_match('/^(http|https):\/\/.+/', $rec['URL'])) {
    $ok=0;
    $out['ERR_URL']=1;
   }
  //updating 'UPDATE_EVERY' (int)
   global $update_every;
   $rec['UPDATE_EVERY']=(int)$update_every;
  //updating 'SCRIPT_ID' (int)
   global $script_id;
   $rec['SCRIPT_ID']=(int)$script_id;
  }
  //UPDATING RECORD
   if ($ok) {

    $rec['LAST_UPDATE']=date('Y-m-d H:i:s');
    $rec['NEXT_UPDATE']=date('Y-m-d H:i:s');

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
  // step: default
  if ($this->tab=='') {

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