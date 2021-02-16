<?php
/*
* @version 0.1 (auto-set)
*/

  global $parent_id;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='classes';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($parent_id) {
   $rec['PARENT_ID']=(int)$parent_id;
  }
  if ($this->mode=='update') {
   $ok=1;
  if ($this->tab=='') {
   $rec['PARENT_ID']=(int)$parent_id;
  }
  // step: default
  if ($this->tab=='') {
  //updating 'TITLE' (varchar, required)
   $rec['TITLE']=gr('title','trim');
   $rec['TITLE']=str_replace(' ','',$rec['TITLE']);
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   global $description;
   $rec['DESCRIPTION']=$description;

   global $nolog;
   $rec['NOLOG']=(int)$nolog;
  }

  if ($this->tab=='template') {
   global $template;
   $rec['TEMPLATE']=$template.'';
  }


  // step: properties
  if ($this->tab=='properties') {
  }
  // step: methods
  if ($this->tab=='methods') {
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $this->updateTree_classes();
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if ($this->tab=='') {
   if ($rec['SUB_LIST']!='') {
    $parents=SQLSelect("SELECT ID, TITLE FROM $table_name WHERE ID!='".$rec['ID']."' AND ID NOT IN (".$rec['SUB_LIST'].") ORDER BY TITLE");
   } else {
    $parents=SQLSelect("SELECT ID, TITLE FROM $table_name WHERE ID!='".$rec['ID']."' ORDER BY TITLE");
   }
   $out['PARENTS']=$parents;
  }
  // step: default
  if ($this->tab=='') {
  }
  // step: properties
  if ($this->tab=='properties') {
  }
  // step: methods
  if ($this->tab=='methods') {
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

if ($out['TITLE']) {
    $this->owner->data['TITLE'] = $out['TITLE'];
}

  if ($rec['ID'] && $rec['PARENT_ID']) {
   $cr_class=array();
   $cr_class['PARENT_ID']=$rec['PARENT_ID'];
   $cr_class['TITLE']=$rec['TITLE'];
   $cr_class['ID']=$rec['ID'];
   $limit=30;
   while ($cr_class['PARENT_ID']!="0" && $limit>0) {
    $cr_class=SQLSelectOne("SELECT ID, PARENT_ID, TITLE FROM classes WHERE ID='".$cr_class['PARENT_ID']."'");
    $out['HISTORY_LIST'][]=$cr_class;
    $limit--;
   }
   $out['HISTORY_LIST']=array_reverse($out['HISTORY_LIST']);
  }

  if ($rec['ID']) {
	$subClasses=SQLSelect("SELECT ID, TITLE FROM classes WHERE PARENT_ID=".$rec['ID']." ORDER BY TITLE");
	if($subClasses && is_array($subClasses)) {
		$out['SUB_CLASSES'] = $subClasses;
	}
  }

