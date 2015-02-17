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
  if (IsSet($this->device_id)) {
   $device_id=$this->device_id;
   $qry.=" AND DEVICE_ID='".$this->device_id."'";
  } else {
   global $device_id;
  }
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['knxproperties_qry'];
  } else {
   $session->data['knxproperties_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_knxproperties;
  if (!$sortby_knxproperties) {
   $sortby_knxproperties=$session->data['knxproperties_sort'];
  } else {
   if ($session->data['knxproperties_sort']==$sortby_knxproperties) {
    if (Is_Integer(strpos($sortby_knxproperties, ' DESC'))) {
     $sortby_knxproperties=str_replace(' DESC', '', $sortby_knxproperties);
    } else {
     $sortby_knxproperties=$sortby_knxproperties." DESC";
    }
   }
   $session->data['knxproperties_sort']=$sortby_knxproperties;
  }
  if (!$sortby_knxproperties) $sortby_knxproperties="ID DESC";
  $out['SORTBY']=$sortby_knxproperties;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM knxproperties WHERE $qry ORDER BY ".$sortby_knxproperties);
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