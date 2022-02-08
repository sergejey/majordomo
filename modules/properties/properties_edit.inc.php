<?php
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='properties';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'Class ID' (int, required)
   if (IsSet($this->class_id)) {
    $rec['CLASS_ID']=$this->class_id;
   } else {
   global $class_id;
   $rec['CLASS_ID']=(int)$class_id;
   /*
   if (!$rec['CLASS_ID']) {
    $out['ERR_CLASS_ID']=1;
    $ok=0;
   }
   */
   }
  //updating 'Titile' (varchar, required)
   $rec['TITLE']=gr('title','trim');
   $rec['TITLE']=str_replace(' ','',$rec['TITLE']);
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   if ($ok && $rec['CLASS_ID']) {
    include_once(DIR_MODULES.'classes/classes.class.php');
    $cl=new classes();
    $parent_properties=$cl->getParentProperties($rec['CLASS_ID']);
    $seen=array();
    foreach($parent_properties as $k=>$v) {
     $seen[strtoupper($v['TITLE'])]=1;
    }
    if (IsSet($seen[strtoupper($rec['TITLE'])])) {
     $ok=0;
     $out['ERR_TITLE']=1;
    }
   }

   global $keep_history;
   $rec['KEEP_HISTORY']=(int)$keep_history;

   global $onchange;
   $rec['ONCHANGE']=trim($onchange);

   global $data_key;
   $rec['DATA_KEY']=(int)$data_key;

   global $data_type;
   $rec['DATA_TYPE']=(int)$data_type;

   $rec['VALIDATION_TYPE']=gr('validation_type','int');
   $rec['VALIDATION_NUM_MIN']=gr('validation_num_min');
   $rec['VALIDATION_NUM_MAX']=gr('validation_num_max');
   if ($rec['VALIDATION_TYPE']==3) {
    $rec['VALIDATION_LIST']=gr('validation_list');
    if (!$rec['VALIDATION_LIST']) {
     $out['ERR_VALIDATION_LIST']=1;
     $ok=0;
    } else {
     $tmp=explode(',',$rec['VALIDATION_LIST']);
     $total = count($tmp);
     for($i=0;$i<$total;$i++) {
      $tmp[$i]=trim($tmp[$i]);
      $tmp[$i]=mb_strtolower($tmp[$i],'UTF-8');
     }
     $rec['VALIDATION_LIST']=implode(',',$tmp);
    }
   }
   if ($rec['VALIDATION_TYPE']==100) {
    $rec['VALIDATION_CODE']=gr('validation_code');
    $errors=php_syntax_error($rec['VALIDATION_CODE']);
    if ($errors) {
     $out['ERR_VALIDATION_CODE']=$errors;
     $ok=0;
    }
   }

  //updating 'Description' (text)
   global $description;
   $rec['DESCRIPTION']=$description;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
     if (!$rec['KEEP_HISTORY']) {
      $pvalues=SQLSelect("SELECT * FROM pvalues WHERE PROPERTY_ID='".$rec['ID']."'");
      $total=count($pvalues);
      for($i=0;$i<$total;$i++) {
       SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$pvalues[$i]['ID']."'");
      }
     }
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;

    if ($rec['CLASS_ID']) {
     $objects=getObjectsByClass($rec['CLASS_ID']);
     $total=count($objects);
     $replaces=array();
     for($i=0;$i<$total;$i++) {
      $property=SQLSelectOne("SELECT ID FROM properties WHERE TITLE LIKE '".DBSafe($rec['TITLE'])."' AND OBJECT_ID=".(int)$objects[$i]['ID']." AND CLASS_ID!=".(int)$rec['CLASS_ID']);
      if ($property['ID']) {
       $replaces[]=$property['ID'];
      }
     }
     $total=count($replaces);
     for($i=0;$i<$total;$i++) {
      SQLExec("UPDATE pvalues SET PROPERTY_ID=".(int)$rec['ID']." WHERE PROPERTY_ID=".(int)$replaces[$i]);
      SQLExec("DELETE FROM properties WHERE ID=".(int)$replaces[$i]);
     }

    }

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

  if ($this->class_id) {
   //echo "zz";exit;
    include_once(DIR_MODULES.'objects/objects.class.php');
    $obj=new objects();
    $methods=$obj->getParentMethods($this->class_id, '', 1);
    $out['METHODS']=$methods;

  }

  global $overwrite;
  if ($overwrite) {
   $tmp=SQLSelectOne("SELECT * FROM properties WHERE ID='".(int)$overwrite."'");
   unset($tmp['ID']);
   foreach($tmp as $k=>$v) {
    $out[$k]=htmlspecialchars($v);
   }
  }



?>