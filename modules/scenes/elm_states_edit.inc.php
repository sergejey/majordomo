<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='elm_states';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'ELEMENT_ID' (int)
   if (IsSet($this->element_id)) {
    $rec['ELEMENT_ID']=$this->element_id;
   } else {
   global $element_id;
   $rec['ELEMENT_ID']=(int)$element_id;
   }
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
      /*
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
      */
  //updating 'IMAGE' (varchar)
   global $image;
   $rec['IMAGE']=$image;
  //updating 'HTML' (text)
   global $html;
   $rec['HTML']=$html;
  //updating 'IS_DYNAMIC' (int)
   global $is_dynamic;
   $rec['IS_DYNAMIC']=(int)$is_dynamic;
  //updating 'LINKED_OBJECT' (varchar)
   global $linked_object;
   $rec['LINKED_OBJECT']=$linked_object.'';
  //updating 'LINKED_PROPERTY' (varchar)
   global $linked_property;
   $rec['LINKED_PROPERTY']=$linked_property.'';
  //updating 'CONDITION' (select)
   global $condition;
   $rec['CONDITION']=$condition;
  //updating 'CONDITION_VALUE' (varchar)
   global $condition_value;
   $rec['CONDITION_VALUE']=$condition_value.'';
  //updating 'SCRIPT_ID' (int)
   if (IsSet($this->script_id)) {
    $rec['SCRIPT_ID']=$this->script_id;
   } else {
   global $script_id;
   $rec['SCRIPT_ID']=(int)$script_id;
   }
  //updating 'SWITCH_SCENE' (int)
   global $switch_scene;
   $rec['SWITCH_SCENE']=(int)$switch_scene;
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
  //options for 'CONDITION' (select)
  $tmp=explode('|', DEF_CONDITION_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['CONDITION_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $condition_opt[$value]=$title;
  }

  $optionsConditionCnt = count($out['CONDITION_OPTIONS']);
  for ($i = 0; $i < $optionsConditionCnt;$i++)
  {
      if ($out['CONDITION_OPTIONS'][$i]['VALUE'] == $rec['CONDITION'])
      {
         $out['CONDITION_OPTIONS'][$i]['SELECTED'] = 1;
         $out['CONDITION'] = $out['CONDITION_OPTIONS'][$i]['TITLE'];
         $rec['CONDITION'] = $out['CONDITION_OPTIONS'][$i]['TITLE'];
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