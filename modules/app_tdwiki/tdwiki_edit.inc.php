<?php
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='tdwiki';

  if (!$this->name_id) {
   $tmp=SQLSelectOne("SELECT ID, NAME FROM tdwiki");
   $this->name_id=$tmp['NAME'];
  }

  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE NAME='".$this->name_id."'");

  if (!$rec['ID']) {
   $rec['NAME']=$this->name_id;
   $rec['CONTENT']=LoadFile(DIR_TEMPLATES.$this->name.'/default.html');
   $rec['ID']=SQLInsert($table_name, $rec);
  }


  if ($this->mode=='reset') {
   $rec['CONTENT']=LoadFile(DIR_TEMPLATES.$this->name.'/default.html');
   SQLUpdate($table_name, $rec);
   $this->redirect("?");
  }

  global $mode;

  if ($mode=='update') {
   global $content;
   //DebMes($content);
   $rec['CONTENT']=$content;
   SQLUpdate($table_name, $rec);
   echo "OK";
   exit;
  }

  //echo $rec['ID'];
  //echo $rec['CONTENT'];exit;

  outHash($rec, $out);

?>
