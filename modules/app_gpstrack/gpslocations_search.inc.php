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
   $qry=$session->data['gpslocations_qry'];
  } else {
   $session->data['gpslocations_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_gpslocations;
  if (!$sortby_gpslocations) {
   $sortby_gpslocations=$session->data['gpslocations_sort'];
  } else {
   if ($session->data['gpslocations_sort']==$sortby_gpslocations) {
    if (Is_Integer(strpos($sortby_gpslocations, ' DESC'))) {
     $sortby_gpslocations=str_replace(' DESC', '', $sortby_gpslocations);
    } else {
     $sortby_gpslocations=$sortby_gpslocations." DESC";
    }
   }
   $session->data['gpslocations_sort']=$sortby_gpslocations;
  }
  if (!$sortby_gpslocations) $sortby_gpslocations="TITLE";
  $out['SORTBY']=$sortby_gpslocations;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM gpslocations WHERE $qry ORDER BY ".$sortby_gpslocations);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>