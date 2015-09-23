<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='calendar_events';
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
  //updating 'NOTES' (text)
   global $notes;
   $rec['NOTES']=$notes;
  //updating 'DUE' (date)
   global $due;
   $rec['DUE']=toDBDate($due);
  //updating 'ADDED' (datetime)
   global $added_date;
   global $added_minutes;
   global $added_hours;
   $rec['ADDED']=toDBDate($added_date)." $added_hours:$added_minutes:00";
  //updating 'IS_TASK' (int)
   global $is_task;
   $rec['IS_TASK']=(int)$is_task;
  //updating 'IS_NODATE' (int)
   global $is_nodate;
   $rec['IS_NODATE']=(int)$is_nodate;
  //updating 'IS_REPEATING' (int)
   global $is_repeating;
   $rec['IS_REPEATING']=(int)$is_repeating;
  //updating 'REPEAT_TYPE' (select)
   global $repeat_type;
   $rec['REPEAT_TYPE']=$repeat_type;
  //updating 'WEEK_DAYS' (varchar)
   global $week_days;
   $rec['WEEK_DAYS']=$week_days;
  //updating 'IS_REPEATING_AFTER' (int)
   global $is_repeating_after;
   $rec['IS_REPEATING_AFTER']=(int)$is_repeating_after;
  //updating 'REPEAT_IN' (int)
   global $repeat_in;
   $rec['REPEAT_IN']=(int)$repeat_in;
  //updating 'USER_ID' (select)
   global $user_id;
   $rec['USER_ID']=$user_id;
  //updating 'LOCATION_ID' (select)
   global $location_id;
   $rec['LOCATION_ID']=$location_id;
  //updating 'CALENDAR_CATEGORY_ID' (select)
   global $calendar_category_id;
   $rec['CALENDAR_CATEGORY_ID']=$calendar_category_id;
  //updating 'DONE_SCRIPT_ID' (int)
   global $done_script_id;
   $rec['DONE_SCRIPT_ID']=(int)$done_script_id;
  //updating 'DONE_CODE' (text)
   global $done_code;
   $rec['DONE_CODE']=$done_code;
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
   if ($rec['DUE']!='') {
    $rec['DUE']=fromDBDate($rec['DUE']);
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
  //options for 'REPEAT_TYPE' (select)
  $tmp=explode('|', DEF_REPEAT_TYPE_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['REPEAT_TYPE_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $repeat_type_opt[$value]=$title;
  }
  
   $rptOptionsCnt = count($out['REPEAT_TYPE_OPTIONS']);
   for ($i = 0; $i < $rptOptionsCnt; $i++)
   {
      if ($out['REPEAT_TYPE_OPTIONS'][$i]['VALUE'] == $rec['REPEAT_TYPE'])
      {
         $out['REPEAT_TYPE_OPTIONS'][$i]['SELECTED']=1;
         $out['REPEAT_TYPE']=$out['REPEAT_TYPE_OPTIONS'][$i]['TITLE'];
         $rec['REPEAT_TYPE']=$out['REPEAT_TYPE_OPTIONS'][$i]['TITLE'];
      }
   }

  //options for 'USER_ID' (select)
  $tmp=SQLSelect("SELECT ID, NAME FROM users ORDER BY NAME");
  
  $users_total = count($tmp);
  
  for ($users_i = 0; $users_i < $users_total; $users_i++)
  {
      $user_id_opt[$tmp[$users_i]['ID']] = $tmp[$users_i]['NAME'];
  }

  for ($i = 0; $i < $users_total; $i++)
  {
      if ($rec['USER_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }

  $out['USER_ID_OPTIONS']=$tmp;
  //options for 'LOCATION_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM gpslocations ORDER BY TITLE");
  $gpslocations_total=count($tmp);
  for($gpslocations_i=0;$gpslocations_i<$gpslocations_total;$gpslocations_i++) {
   $location_id_opt[$tmp[$gpslocations_i]['ID']]=$tmp[$gpslocations_i]['TITLE'];
  }

  for($i=0;$i < $gpslocations_total; $i++) {
   if ($rec['LOCATION_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['LOCATION_ID_OPTIONS']=$tmp;
  //options for 'CALENDAR_CATEGORY_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM calendar_categories ORDER BY TITLE");
  $calendar_categories_total=count($tmp);
  for($calendar_categories_i=0;$calendar_categories_i<$calendar_categories_total;$calendar_categories_i++) {
   $calendar_category_id_opt[$tmp[$calendar_categories_i]['ID']]=$tmp[$calendar_categories_i]['TITLE'];
  }
  for($i=0;$i< $calendar_categories_total;$i++) {
   if ($rec['CALENDAR_CATEGORY_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['CALENDAR_CATEGORY_ID_OPTIONS']=$tmp;
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>