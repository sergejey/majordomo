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
   $qry=$session->data['knxdevices_qry'];
  } else {
   $session->data['knxdevices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_knxdevices;
  if (!$sortby_knxdevices) {
   $sortby_knxdevices=$session->data['knxdevices_sort'];
  } else {
   if ($session->data['knxdevices_sort']==$sortby_knxdevices) {
    if (Is_Integer(strpos($sortby_knxdevices, ' DESC'))) {
     $sortby_knxdevices=str_replace(' DESC', '', $sortby_knxdevices);
    } else {
     $sortby_knxdevices=$sortby_knxdevices." DESC";
    }
   }
   $session->data['knxdevices_sort']=$sortby_knxdevices;
  }
  if (!$sortby_knxdevices) $sortby_knxdevices="ID DESC";
  $out['SORTBY']=$sortby_knxdevices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM knxdevices WHERE $qry ORDER BY ".$sortby_knxdevices);
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