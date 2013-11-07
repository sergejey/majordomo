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

  global $location_id;
  if ($location_id) {
   $qry.=" AND LOCATION_ID='".(int)$location_id."'";
   $out['LOCATION_ID']=(int)$location_id;
  }


  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  if (IsSet($this->location_id)) {
   $location_id=$this->location_id;
   $qry.=" AND LOCATION_ID='".$this->location_id."'";
  } else {
   global $location_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['zwave_devices_qry'];
  } else {
   $session->data['zwave_devices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_zwave_devices;
  if (!$sortby_zwave_devices) {
   $sortby_zwave_devices=$session->data['zwave_devices_sort'];
  } else {
   if ($session->data['zwave_devices_sort']==$sortby_zwave_devices) {
    if (Is_Integer(strpos($sortby_zwave_devices, ' DESC'))) {
     $sortby_zwave_devices=str_replace(' DESC', '', $sortby_zwave_devices);
    } else {
     $sortby_zwave_devices=$sortby_zwave_devices." DESC";
    }
   }
   $session->data['zwave_devices_sort']=$sortby_zwave_devices;
  }
  $sortby_zwave_devices="NODE_ID, INSTANCE_ID, TITLE";
  $out['SORTBY']=$sortby_zwave_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM zwave_devices WHERE $qry ORDER BY ".$sortby_zwave_devices);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['LATEST_UPDATE']);
    $res[$i]['LATEST_UPDATE']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }

  $out['LOCATIONS']=SQLSelect("SELECT * FROM locations ORDER BY TITLE");

?>