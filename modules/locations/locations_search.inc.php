<?php
/*
* @version 0.1 (auto-set)
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
   $qry=$session->data['locations_qry'];
  } else {
   $session->data['locations_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['locations_sort'];
  } else {
   if ($session->data['locations_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['locations_sort']=$sortby;
  }
  if (!$sortby) $sortby="TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM locations WHERE $qry ORDER BY PRIORITY DESC, TITLE");
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>