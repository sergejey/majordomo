<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }

  if (!$out['NO_CANCEL']) {
   global $no_cancel;
   $out['NO_CANCEL']=$no_cancel;
  }

  $table_name='security_rules';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'OBJECT_TYPE' (char)
   global $object_type;
   $rec['OBJECT_TYPE']=trim($object_type);
  //updating 'OBJECT_ID' (int)
   if (IsSet($this->object_id)) {
    $rec['OBJECT_ID']=$this->object_id;
   } else {
   global $object_id;
   $rec['OBJECT_ID']=(int)$object_id;
   }
  //updating 'TERMINALS' (varchar)
   global $terminals_list;
   if (is_array($terminals_list)) {
    $rec['TERMINALS']=implode(',', $terminals_list);
   } else {
    $rec['TERMINALS']='';
   }
   global $terminals_except;
   $rec['TERMINALS_EXCEPT']=(int)$terminals_except;
   global $terminals_limited;
   if (!$terminals_limited) {
    $rec['TERMINALS']='';
    $rec['TERMINALS_EXCEPT']=0;
   }

  //updating 'USERS' (varchar)
   global $users_list;
   if (is_array($users_list)) {
    $rec['USERS']=implode(',', $users_list);
   } else {
    $rec['USERS']='';
   }
   global $users_except;
   $rec['USERS_EXCEPT']=(int)$users_except;
   global $users_limited;
   if (!$users_limited) {
    $rec['USERS']='';
    $rec['USERS_EXCEPT']=0;
   }
  //updating 'TIMES' (varchar)
   global $times_list;
   if (is_array($times_list)) {
    $rec['TIMES']=implode(',', $times_list);
   } else {
    $rec['TIMES']='';
   }
   global $times_except;
   $rec['TIMES_EXCEPT']=(int)$times_except;
   global $times_limited;
   if (!$times_limited) {
    $rec['TIMES']='';
    $rec['TIMES_EXCEPT']=0;
   }

   global $condition_active;
   $rec['CONDITION_ACTIVE']=(int)$condition_active;

   global $condition_linked_object;
   $rec['CONDITION_LINKED_OBJECT']=$condition_linked_object;

   global $condition_linked_property;
   $rec['CONDITION_LINKED_PROPERTY']=$condition_linked_property;

   global $condition;
   $rec['CONDITION']=(int)$condition;

   global $condition_value;
   $rec['CONDITION_VALUE']=$condition_value;

  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    if ($rec['USERS']=='' && $rec['TIMES']=='' && $rec['TERMINALS']=='' && $rec['CONDITION_ACTIVE']==0) {
     SQLExec("DELETE FROM security_rules WHERE ID='".$rec['ID']."'");
     $this->redirect("?object_id=".$rec['OBJECT_ID']."&object_type=".$rec['OBJECT_TYPE']);
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

  if ($rec['TERMINALS']==='') {
   unset($rec['TERMINALS']);
  }
  if ($rec['USERS']==='') {
   unset($rec['USERS']);
  }
  if (!$rec['TIMES']) {
   unset($rec['TIMES']);
  }

  outHash($rec, $out);

  $terminals_list = getAllTerminals(-1, 'TITLE');
  $terminals_list[]=array('ID'=>'0', 'TITLE'=>'<i>Unknown</i>');
  $tmp=explode(',', $rec['TERMINALS']);
  $total=count($terminals_list);
  for($i=0;$i<$total;$i++) {
   if (in_array($terminals_list[$i]['ID'], $tmp)) {
    $terminals_list[$i]['SELECTED']=1;
   }
  }
  $out['TERMINALS_LIST']=$terminals_list;

  $users_list=SQLSelect("SELECT * FROM users ORDER BY NAME");
  $users_list[]=array('ID'=>'0', 'NAME'=>'<i>Anonymous</i>');
  $tmp=explode(',', $rec['USERS']);
  $total=count($users_list);
  for($i=0;$i<$total;$i++) {
   if (in_array($users_list[$i]['ID'], $tmp)) {
    $users_list[$i]['SELECTED']=1;
   }
  }
  $out['USERS_LIST']=$users_list;


  $times_list=array();
  for($i=0;$i<24;$i++) {
   $num=$i;
   if ($num<10) {
    $num='0'.$num;
   }
   $nump=$i+1;
   if ($nump<10) {
    $nump='0'.$nump;
   }
   $times_list[]=array('TITLE'=>$num.':00-'.$nump.':00');
  }

  $tmp=explode(',', $rec['TIMES']);
  $total=count($times_list);
  for($i=0;$i<$total;$i++) {
   if (in_array($times_list[$i]['TITLE'], $tmp)) {
    $times_list[$i]['SELECTED']=1;
   }
  }
  $out['TIMES_LIST']=$times_list;

?>