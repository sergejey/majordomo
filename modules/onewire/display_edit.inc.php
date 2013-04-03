<?php
/*
* @version 0.2 (wizard)
*/

  if ($this->mode=='setvalue') {
   global $prop_id;
   global $new_value;
   global $id;
   $this->setProperty($prop_id, $new_value);
   $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
  }

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
    
  $table_name='owdisplays';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

  if ($this->mode=='update') {
   global $title;
   $rec['TITLE']=$title;
	if (!$rec['ID']) {
		global $udid;
		$rec['UDID']=$udid;
	}
   global $rows;
   $rec['ROWS']=(int)$rows;
   global $cols;
   $rec['COLS']=(int)$cols;
   global $update_interval;
   $rec['UPDATE_INTERVAL']=(int)$update_interval;
   global $value;
   $rec['VALUE']=$value;
   $rec['UPDATE_LATEST']=1;
	$error = (count($rec) != count(array_filter($rec)));
	if($error) {
		$out['ERR']=1;
	} else {
	   $rec['UPDATE_NEXT']=time();
	   if ($rec['ID']) {
		SQLUpdate('owdisplays', $rec); // update
	   } else {
		$rec['ID']=SQLInsert('owdisplays', $rec); // adding new record
	   }
	   $out['OK']=1;
   }
  }
  
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  } else {
	$devices=SQLSelect("SELECT UDID FROM owdevices WHERE ID IN (SELECT DEVICE_ID FROM owproperties WHERE ((SYSNAME='type') AND (VALUE='DS2408')))");
	$out['DEVICES'] = $devices;
  }

  outHash($rec, $out);
  
  
?>