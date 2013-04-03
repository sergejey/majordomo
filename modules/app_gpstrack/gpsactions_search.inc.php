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
  if (IsSet($this->location_id)) {
   $location_id=$this->location_id;
   $qry.=" AND LOCATION_ID='".$this->location_id."'";
  } else {
   global $location_id;
  }
  if (IsSet($this->user_id)) {
   $user_id=$this->user_id;
   $qry.=" AND USER_ID='".$this->user_id."'";
  } else {
   global $user_id;
  }
  if (IsSet($this->script_id)) {
   $script_id=$this->script_id;
   $qry.=" AND SCRIPT_ID='".$this->script_id."'";
  } else {
   global $script_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['gpsactions_qry'];
  } else {
   $session->data['gpsactions_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_gpsactions;
  if (!$sortby_gpsactions) {
   $sortby_gpsactions=$session->data['gpsactions_sort'];
  } else {
   if ($session->data['gpsactions_sort']==$sortby_gpsactions) {
    if (Is_Integer(strpos($sortby_gpsactions, ' DESC'))) {
     $sortby_gpsactions=str_replace(' DESC', '', $sortby_gpsactions);
    } else {
     $sortby_gpsactions=$sortby_gpsactions." DESC";
    }
   }
   $session->data['gpsactions_sort']=$sortby_gpsactions;
  }
  $sortby_gpsactions="gpsactions.LOCATION_ID, gpsactions.USER_ID, ACTION_TYPE";
  $out['SORTBY']=$sortby_gpsactions;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT gpsactions.*, gpslocations.TITLE as LOCATION_TITLE, users.NAME as USER_NAME, scripts.TITLE as SCRIPT_TITLE FROM gpsactions LEFT JOIN gpslocations ON gpslocations.ID=gpsactions.LOCATION_ID LEFT JOIN users ON gpsactions.USER_ID=users.ID LEFT JOIN scripts ON scripts.ID=gpsactions.SCRIPT_ID WHERE $qry ORDER BY ".$sortby_gpsactions);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['EXECUTED']);
    $res[$i]['EXECUTED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }
?>