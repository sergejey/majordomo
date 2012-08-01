<?
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='events';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'Event Type' (char, required)
   global $event_type;
   $rec['EVENT_TYPE']=trim($event_type);
   if ($rec['EVENT_TYPE']=='') {
    $out['ERR_EVENT_TYPE']=1;
    $ok=0;
   }
  //updating 'TERMINAL_FROM' (varchar)
   global $terminal_from;
   $rec['TERMINAL_FROM']=$terminal_from;
  //updating 'TERMINAL_TO' (varchar)
   global $terminal_to;
   $rec['TERMINAL_TO']=$terminal_to;
  //updating 'USER_FROM' (varchar)
   global $user_from;
   $rec['USER_FROM']=$user_from;
  //updating 'USER_TO' (varchar)
   global $user_to;
   $rec['USER_TO']=$user_to;
  //updating 'WINDOW' (varchar)
   global $window;
   $rec['WINDOW']=$window;
  //updating 'DETAILS' (text)
   global $details;
   $rec['DETAILS']=$details;
  //updating 'ADDED' (datetime)
   global $added_date;
   global $added_minutes;
   global $added_hours;
   $rec['ADDED']=toDBDate($added_date)." $added_hours:$added_minutes:00";
  //updating 'EXPIRE' (datetime)
   global $expire_date;
   global $expire_minutes;
   global $expire_hours;
   $rec['EXPIRE']=toDBDate($expire_date)." $expire_hours:$expire_minutes:00";
  //updating 'PROCESSED' (int)
   global $processed;
   $rec['PROCESSED']=(int)$processed;
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
  if ($rec['ADDED']!='') {
   $tmp=explode(' ', $rec['ADDED']);
   $out['ADDED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $added_hours=$tmp2[0];
   $added_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$added_minutes) {
    $out['ADDED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['ADDED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$added_hours) {
    $out['ADDED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['ADDED_HOURS'][]=array('TITLE'=>$title);
   }
  }
  if ($rec['EXPIRE']!='') {
   $tmp=explode(' ', $rec['EXPIRE']);
   $out['EXPIRE_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $expire_hours=$tmp2[0];
   $expire_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$expire_minutes) {
    $out['EXPIRE_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['EXPIRE_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$expire_hours) {
    $out['EXPIRE_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['EXPIRE_HOURS'][]=array('TITLE'=>$title);
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