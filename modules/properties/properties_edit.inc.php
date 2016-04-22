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
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   global $keep_history;
   $rec['KEEP_HISTORY']=(int)$keep_history;

   global $onchange;
   $rec['ONCHANGE']=trim($onchange);

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

  if ($rec['CLASS_ID']) {
   //echo "zz";exit;
    include_once(DIR_MODULES.'objects/objects.class.php');
    $obj=new objects();
    $methods=$obj->getParentMethods($rec['CLASS_ID'], '', 1);
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