<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='modbusdevices';
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
  //updating 'IP' (varchar)
   global $host;
   $rec['HOST']=$host;
   if ($rec['HOST']=='') {
    $out['ERR_HOST']=1;
    $ok=0;
   }

   global $protocol;
   $rec['PROTOCOL']=$protocol;

   global $device_id;
   $rec['DEVICE_ID']=(int)$device_id;


  //updating 'REQUEST_TYPE' (select)
   global $request_type;
   $rec['REQUEST_TYPE']=$request_type;
   if ($rec['REQUEST_TYPE']=='') {
    $out['ERR_REQUEST_TYPE']=1;
    $ok=0;
   }
  //updating 'REQUEST_START' (int)
   global $request_start;
   $rec['REQUEST_START']=(int)$request_start;
   /*
   if (!$rec['REQUEST_START']) {
    $out['ERR_REQUEST_START']=1;
    $ok=0;
   }
   */
  //updating 'REQUEST_TOTAL' (int)
   global $request_total;
   $rec['REQUEST_TOTAL']=(int)$request_total;
   if (!$rec['REQUEST_TOTAL']) {
    $out['ERR_REQUEST_TOTAL']=1;
    $ok=0;
   }
  //updating 'RESPONSE_CONVERT' (select)
   global $response_convert;
   $rec['RESPONSE_CONVERT']=$response_convert;
  //updating 'DATA' (text)
   global $data;
   $rec['DATA']=$data;
  //updating 'CHECK_LATEST' (datetime)
  /*
   global $check_latest_date;
   global $check_latest_minutes;
   global $check_latest_hours;
   $rec['CHECK_LATEST']=toDBDate($check_latest_date)." $check_latest_hours:$check_latest_minutes:00";
  //updating 'CHECK_NEXT' (datetime)
   global $check_next_date;
   global $check_next_minutes;
   global $check_next_hours;
   $rec['CHECK_NEXT']=toDBDate($check_next_date)." $check_next_hours:$check_next_minutes:00";
   */
   $rec['CHECK_NEXT']=date('Y-m-d H:i:s');
  //updating 'POLLPERIOD' (int)
   global $pollperiod;
   $rec['POLLPERIOD']=(int)$pollperiod;
   if (!$rec['POLLPERIOD']) {
    $out['ERR_POLLPERIOD']=1;
    $ok=0;
   }

   $old_linked_object=$rec['LINKED_OBJECT'];
   $old_linked_property=$rec['LINKED_PROPERTY'];

  //updating 'LINKED_OBJECT' (varchar)
   global $linked_object;
   $rec['LINKED_OBJECT']=$linked_object;
  //updating 'LINKED_PROPERTY' (varchar)
   global $linked_property;
   $rec['LINKED_PROPERTY']=$linked_property;
  //updating 'LOG' (varchar)
  // global $log;
  // $rec['LOG']=$log;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;

    if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
     addLinkedProperty($rec['LINKED_OBJECT'], $rec['LINKED_PROPERTY'], $this->name);
    }
    if ($old_linked_object && $old_linked_object!=$rec['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$rec['LINKED_PROPERTY']) {
     removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
    }

       $this->poll_device($rec['ID']);


   } else {
    $out['ERR']=1;
   }
  }
  //options for 'REQUEST_TYPE' (select)
  $tmp=explode('|', DEF_REQUEST_TYPE_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['REQUEST_TYPE_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $request_type_opt[$value]=$title;
  }
  for($i=0;$i<count($out['REQUEST_TYPE_OPTIONS']);$i++) {
   if ($out['REQUEST_TYPE_OPTIONS'][$i]['VALUE']==$rec['REQUEST_TYPE']) {
    $out['REQUEST_TYPE_OPTIONS'][$i]['SELECTED']=1;
    $out['REQUEST_TYPE']=$out['REQUEST_TYPE_OPTIONS'][$i]['TITLE'];
    $rec['REQUEST_TYPE']=$out['REQUEST_TYPE_OPTIONS'][$i]['TITLE'];
   }
  }
  //options for 'RESPONSE_CONVERT' (select)
  $tmp=explode('|', DEF_RESPONSE_CONVERT_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['RESPONSE_CONVERT_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $response_convert_opt[$value]=$title;
  }
  for($i=0;$i<count($out['RESPONSE_CONVERT_OPTIONS']);$i++) {
   if ($out['RESPONSE_CONVERT_OPTIONS'][$i]['VALUE']==$rec['RESPONSE_CONVERT']) {
    $out['RESPONSE_CONVERT_OPTIONS'][$i]['SELECTED']=1;
    $out['RESPONSE_CONVERT']=$out['RESPONSE_CONVERT_OPTIONS'][$i]['TITLE'];
    $rec['RESPONSE_CONVERT']=$out['RESPONSE_CONVERT_OPTIONS'][$i]['TITLE'];
   }
  }
  if ($rec['CHECK_LATEST']!='') {
   $tmp=explode(' ', $rec['CHECK_LATEST']);
   $out['CHECK_LATEST_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $check_latest_hours=$tmp2[0];
   $check_latest_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$check_latest_minutes) {
    $out['CHECK_LATEST_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['CHECK_LATEST_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$check_latest_hours) {
    $out['CHECK_LATEST_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['CHECK_LATEST_HOURS'][]=array('TITLE'=>$title);
   }
  }
  if ($rec['CHECK_NEXT']!='') {
   $tmp=explode(' ', $rec['CHECK_NEXT']);
   $out['CHECK_NEXT_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $check_next_hours=$tmp2[0];
   $check_next_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$check_next_minutes) {
    $out['CHECK_NEXT_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['CHECK_NEXT_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$check_next_hours) {
    $out['CHECK_NEXT_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['CHECK_NEXT_HOURS'][]=array('TITLE'=>$title);
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

  $out['LOG']=nl2br($out['LOG']);

?>