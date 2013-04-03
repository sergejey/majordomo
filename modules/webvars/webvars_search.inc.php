<?php
/*
* @version 0.2 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters

  global $status;
  if ($status!="") {
   $qry.=" AND STATUS=".(int)$status;
   $out['STATUS']=$status;
  }

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['webvars_qry'];
  } else {
   $session->data['webvars_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['webvars_sort'];
  } else {
   if ($session->data['webvars_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['webvars_sort']=$sortby;
  }
  $sortby="ID DESC";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM webvars WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   //paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    if ($this->action=='admin') {
     $res[$i]['LATEST_VALUE']=htmlspecialchars($res[$i]['LATEST_VALUE']);
    }
   }
   $out['RESULT']=$res;
  }
?>