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
   $qry=$session->data['snmpproperties_qry'];
  } else {
   $session->data['snmpproperties_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_snmpproperties;
  if (!$sortby_snmpproperties) {
   $sortby_snmpproperties=$session->data['snmpproperties_sort'];
  } else {
   if ($session->data['snmpproperties_sort']==$sortby_snmpproperties) {
    if (Is_Integer(strpos($sortby_snmpproperties, ' DESC'))) {
     $sortby_snmpproperties=str_replace(' DESC', '', $sortby_snmpproperties);
    } else {
     $sortby_snmpproperties=$sortby_snmpproperties." DESC";
    }
   }
   $session->data['snmpproperties_sort']=$sortby_snmpproperties;
  }
  if (!$sortby_snmpproperties) $sortby_snmpproperties="TITLE";
  $out['SORTBY']=$sortby_snmpproperties;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM snmpproperties WHERE $qry ORDER BY ".$sortby_snmpproperties);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>