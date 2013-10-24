<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  if (IsSet($this->object_id)) {
   $object_id=$this->object_id;
   $qry.=" AND OBJECT_ID='".$this->object_id."'";
  } else {
   global $object_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['security_rules_qry'];
  } else {
   $session->data['security_rules_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_security_rules;
  if (!$sortby_security_rules) {
   $sortby_security_rules=$session->data['security_rules_sort'];
  } else {
   if ($session->data['security_rules_sort']==$sortby_security_rules) {
    if (Is_Integer(strpos($sortby_security_rules, ' DESC'))) {
     $sortby_security_rules=str_replace(' DESC', '', $sortby_security_rules);
    } else {
     $sortby_security_rules=$sortby_security_rules." DESC";
    }
   }
   $session->data['security_rules_sort']=$sortby_security_rules;
  }
  if (!$sortby_security_rules) $sortby_security_rules="ID DESC";
  $out['SORTBY']=$sortby_security_rules;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM security_rules WHERE $qry ORDER BY ".$sortby_security_rules);
  if ($this->single_rec) {
   $total_res=count($res);
   if (!$total_res && $this->action=='admin') {
    $this->redirect("?view_mode=edit_security_rules");
   } elseif ($total_res && $this->action=='admin') {
    $this->redirect("?view_mode=edit_security_rules&id=".$res[0]['ID']);
   } elseif ($total_res && $this->action!='admin') {
   }
  }
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>