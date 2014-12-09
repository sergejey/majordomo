<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $rec=SQLSelectOne("SELECT * FROM system_errors WHERE ID='$id'");
  // some action for fields if required
  $rec['DETAILS']=nl2br($rec['DETAILS']);
  $tmp=explode(' ', $rec['LATEST_UPDATE']);
  $rec['LATEST_UPDATE']=fromDBDate($tmp[0])." ".$tmp[1];
  outHash($rec, $out);
?>