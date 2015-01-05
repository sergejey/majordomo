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
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['blockly_code_qry'];
  } else {
   $session->data['blockly_code_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_blockly_code;
  if (!$sortby_blockly_code) {
   $sortby_blockly_code=$session->data['blockly_code_sort'];
  } else {
   if ($session->data['blockly_code_sort']==$sortby_blockly_code) {
    if (Is_Integer(strpos($sortby_blockly_code, ' DESC'))) {
     $sortby_blockly_code=str_replace(' DESC', '', $sortby_blockly_code);
    } else {
     $sortby_blockly_code=$sortby_blockly_code." DESC";
    }
   }
   $session->data['blockly_code_sort']=$sortby_blockly_code;
  }
  if (!$sortby_blockly_code) $sortby_blockly_code="ID DESC";
  $out['SORTBY']=$sortby_blockly_code;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM blockly_code WHERE $qry ORDER BY ".$sortby_blockly_code);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['UPDATED']);
    $res[$i]['UPDATED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }
?>