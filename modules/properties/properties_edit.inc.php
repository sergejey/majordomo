<?
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

  //updating 'Description' (text)
   global $description;
   $rec['DESCRIPTION']=$description;
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
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

  global $overwrite;
  if ($overwrite) {
   $tmp=SQLSelectOne("SELECT * FROM properties WHERE ID='".(int)$overwrite."'");
   unset($tmp['ID']);
   foreach($tmp as $k=>$v) {
    $out[$k]=htmlspecialchars($v);
   }
  }

?>