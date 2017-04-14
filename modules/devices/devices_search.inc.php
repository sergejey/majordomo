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

  global $type;
  if ($type!='') {
   $qry.=" AND devices.TYPE='".DBSafe($type)."'";
   $out['TYPE']=$type;
  }

global $location_id;
if ($location_id) {
 $out['LOCATION_ID']=(int)$location_id;
 $qry.=" AND devices.LOCATION_ID=".$out['LOCATION_ID'];
}

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['devices_qry'];
  } else {
   $session->data['devices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_devices="ID DESC";
  $out['SORTBY']=$sortby_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT devices.*, locations.TITLE as LOCATION_TITLE FROM devices LEFT JOIN locations ON devices.LOCATION_ID=locations.ID WHERE $qry ORDER BY ".$sortby_devices);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    if ($res[$i]['LINKED_OBJECT']) {
     $processed=$this->processDevice($res[$i]['ID']);
     $res[$i]['HTML']=$processed['HTML'];
    }
    $res[$i]['TYPE_TITLE']=$this->device_types[$res[$i]['TYPE']]['TITLE'];
       $linked=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices_linked WHERE DEVICE1_ID=".$res[$i]['ID']);
       if ($linked['TOTAL']) {
           $res[$i]['LINKED']=$linked['TOTAL'];
       }
   }
   $out['RESULT']=$res;
  }

$types=array();
foreach($this->device_types as $k=>$v) {
 if ($v['TITLE']) {
  $types[]=array('NAME'=>$k,'TITLE'=>$v['TITLE']);
 }
}
$out['TYPES']=$types;
$out['LOCATIONS']=SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE");
//var_dump($this->getWatchedProperties(0));exit;