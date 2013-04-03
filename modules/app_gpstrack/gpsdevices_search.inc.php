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
  if (IsSet($this->user_id)) {
   $user_id=$this->user_id;
   $qry.=" AND USER_ID='".$this->user_id."'";
  } else {
   global $user_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['gpsdevices_qry'];
  } else {
   $session->data['gpsdevices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_gpsdevices;
  if (!$sortby_gpsdevices) {
   $sortby_gpsdevices=$session->data['gpsdevices_sort'];
  } else {
   if ($session->data['gpsdevices_sort']==$sortby_gpsdevices) {
    if (Is_Integer(strpos($sortby_gpsdevices, ' DESC'))) {
     $sortby_gpsdevices=str_replace(' DESC', '', $sortby_gpsdevices);
    } else {
     $sortby_gpsdevices=$sortby_gpsdevices." DESC";
    }
   }
   $session->data['gpsdevices_sort']=$sortby_gpsdevices;
  }
  if (!$sortby_gpsdevices) $sortby_gpsdevices="TITLE";
  $out['SORTBY']=$sortby_gpsdevices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM gpsdevices WHERE $qry ORDER BY ".$sortby_gpsdevices);
  if ($res[0]['ID']) {
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