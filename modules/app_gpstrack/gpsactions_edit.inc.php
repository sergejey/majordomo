<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='gpsactions';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'LOCATION_ID' (select)
   if (IsSet($this->location_id)) {
    $rec['LOCATION_ID']=$this->location_id;
   } else {
   global $location_id;
   $rec['LOCATION_ID']=$location_id;
   }
  //updating 'USER_ID' (select)
   if (IsSet($this->user_id)) {
    $rec['USER_ID']=$this->user_id;
   } else {
   global $user_id;
   $rec['USER_ID']=$user_id;
   }
  //updating 'ACTION_TYPE' (select)
   global $action_type;
   $rec['ACTION_TYPE']=$action_type;
  //updating 'SCRIPT_ID' (select)
   if (IsSet($this->script_id)) {
    $rec['SCRIPT_ID']=$this->script_id;
   } else {
   global $script_id;
   $rec['SCRIPT_ID']=$script_id;
   }
  //updating 'CODE' (text)
   global $code;
   $rec['CODE']=$code;
/*
  //updating 'LOG' (text)
   global $log;
   $rec['LOG']=$log;
  //updating 'EXECUTED' (datetime)
   global $executed_date;
   global $executed_minutes;
   global $executed_hours;
   $rec['EXECUTED']=toDBDate($executed_date)." $executed_hours:$executed_minutes:00";
*/
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
  //options for 'USER_ID' (select)
  $tmp=SQLSelect("SELECT ID, NAME FROM users ORDER BY NAME");
  $users_total=count($tmp);
  for($users_i=0;$users_i<$users_total;$users_i++) {
   $user_id_opt[$tmp[$users_i]['ID']]=$tmp[$users_i]['NAME'];
  }
  for($i=0;$i<$users_total;$i++) {
   if ($rec['USER_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['USER_ID_OPTIONS']=$tmp;
  //options for 'ACTION_TYPE' (select)
  $tmp=explode('|', DEF_ACTION_TYPE_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['ACTION_TYPE_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $action_type_opt[$value]=$title;
  }

   $actTypeCnt = count($out['ACTION_TYPE_OPTIONS']);
   for ($i = 0; $i < $actTypeCnt; $i++)
   {
      if ($out['ACTION_TYPE_OPTIONS'][$i]['VALUE'] == $rec['ACTION_TYPE'])
      {
         $out['ACTION_TYPE_OPTIONS'][$i]['SELECTED'] = 1;
         $out['ACTION_TYPE'] = $out['ACTION_TYPE_OPTIONS'][$i]['TITLE'];
         $rec['ACTION_TYPE'] = $out['ACTION_TYPE_OPTIONS'][$i]['TITLE'];
      }
   }
  //options for 'SCRIPT_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
  $scripts_total=count($tmp);
  for($scripts_i=0;$scripts_i<$scripts_total;$scripts_i++) {
   $script_id_opt[$tmp[$scripts_i]['ID']]=$tmp[$scripts_i]['TITLE'];
  }
  for($i=0;$i<$scripts_total;$i++) {
   if ($rec['SCRIPT_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['SCRIPT_ID_OPTIONS']=$tmp;
  if ($rec['EXECUTED']!='') {
   $tmp=explode(' ', $rec['EXECUTED']);
   $out['EXECUTED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $executed_hours=$tmp2[0];
   $executed_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$executed_minutes) {
    $out['EXECUTED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['EXECUTED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$executed_hours) {
    $out['EXECUTED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['EXECUTED_HOURS'][]=array('TITLE'=>$title);
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