<?php
/*
* @version 0.1 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $rec=SQLSelectOne("SELECT * FROM tdwiki WHERE ID='$id'");
  // some action for fields if required
  outHash($rec, $out);
?>