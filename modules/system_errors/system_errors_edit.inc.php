<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='system_errors';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating 'CODE' (varchar, required)
   global $code;
   $rec['CODE']=$code;
   if ($rec['CODE']=='') {
    $out['ERR_CODE']=1;
    $ok=0;
   }
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'DETAILS' (text)
   global $details;
   $rec['DETAILS']=$details;
  //updating 'ACTIVE' (int)
   global $active;
   $rec['ACTIVE']=(int)$active;
  }
  // step: default
  if ($this->tab=='') {
  //updating 'KEEP_HISTORY' (int)
   global $keep_history;
   $rec['KEEP_HISTORY']=(int)$keep_history;
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
	$this->redirect("?action=system_errors");
   } else {
    $out['ERR']=1;
	$this->redirect("?action=system_errors");
   }
  }
  // step: default
  if ($this->tab=='') {
  if ($rec['LATEST_UPDATE']!='') {
   $tmp=explode(' ', $rec['LATEST_UPDATE']);
   $out['LATEST_UPDATE_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $latest_update_hours=$tmp2[0];
   $latest_update_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$latest_update_minutes) {
    $out['LATEST_UPDATE_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['LATEST_UPDATE_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$latest_update_hours) {
    $out['LATEST_UPDATE_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['LATEST_UPDATE_HOURS'][]=array('TITLE'=>$title);
   }
  }
  }
  // step: history
  if ($this->tab=='history') {
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

  // step: history
  if ($this->tab=='history') {
   global $event_id;
   global $op;
   if ($op=='clear') {
    $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='".$rec['ID']."'");
    $rec['ACTIVE']=0;
    SQLUpdate('system_errors', $rec);
    SQLExec("DELETE FROM system_errors_data WHERE ERROR_ID='".$rec['ID']."'");
    $this->redirect("?id=".$rec['ID']."&tab=history&view_mode=".$this->view_mode);
   }
   if ($event_id) {
    $event=SQLSelectOne("SELECT * FROM system_errors_data WHERE ID='".(int)$event_id."'");
    foreach($event as $k=>$v) {
     $out['EVENT_'.$k]=$v;
    }
   }
   $history=SQLSelect("SELECT d. ID, d.ADDED, d.COMMENTS,e.CODE FROM system_errors_data d join system_errors e on d.ERROR_ID = e.ID WHERE d.ERROR_ID='".$rec['ID']."' ORDER BY d.ADDED DESC LIMIT 100");
   if ($history[0]['ID']) {
    $out['HISTORY']=$history;
   }
  }


?>