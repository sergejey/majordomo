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
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['myblocks_qry'];
  } else {
   $session->data['myblocks_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_myblocks;
  if (!$sortby_myblocks) {
   $sortby_myblocks=$session->data['myblocks_sort'];
  } else {
   if ($session->data['myblocks_sort']==$sortby_myblocks) {
    if (Is_Integer(strpos($sortby_myblocks, ' DESC'))) {
     $sortby_myblocks=str_replace(' DESC', '', $sortby_myblocks);
    } else {
     $sortby_myblocks=$sortby_myblocks." DESC";
    }
   }
   $session->data['myblocks_sort']=$sortby_myblocks;
  }
  if (!$sortby_myblocks) $sortby_myblocks="ID DESC";
  $out['SORTBY']=$sortby_myblocks;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM myblocks WHERE $qry ORDER BY ".$sortby_myblocks);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>