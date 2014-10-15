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
  if (IsSet($this->uniq_id)) {
   $uniq_id=$this->uniq_id;
   $qry.=" AND UNIQ_ID='".$this->uniq_id."'";
  } else {
   global $uniq_id;
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
   $qry=$session->data['zwave_properties_qry'];
  } else {
   $session->data['zwave_properties_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_zwave_properties;
  if (!$sortby_zwave_properties) {
   $sortby_zwave_properties=$session->data['zwave_properties_sort'];
  } else {
   if ($session->data['zwave_properties_sort']==$sortby_zwave_properties) {
    if (Is_Integer(strpos($sortby_zwave_properties, ' DESC'))) {
     $sortby_zwave_properties=str_replace(' DESC', '', $sortby_zwave_properties);
    } else {
     $sortby_zwave_properties=$sortby_zwave_properties." DESC";
    }
   }
   $session->data['zwave_properties_sort']=$sortby_zwave_properties;
  }
  if (!$sortby_zwave_properties) $sortby_zwave_properties="TITLE";
  $out['SORTBY']=$sortby_zwave_properties;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM zwave_properties WHERE $qry ORDER BY ".$sortby_zwave_properties);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }



?>