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

  global $include_controller;
  if (!$include_controller) {
   $qry.=" AND zwave_devices.TITLE NOT LIKE 'Static PC Controller%'";
  }


  global $title;
  if ($title!='') {
   $qry.=" AND zwave_devices.TITLE LIKE '%".DBSafe($title)."%'";
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
  $sortby_zwave_devices="zwave_devices.NODE_ID, zwave_devices.INSTANCE_ID, zwave_devices.TITLE";
  $out['SORTBY']=$sortby_zwave_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT zwave_devices.*, locations.TITLE as LOCATION FROM zwave_devices LEFT JOIN locations ON zwave_devices.LOCATION_ID=locations.ID WHERE $qry ORDER BY ".$sortby_zwave_devices);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['LATEST_UPDATE']);
    $res[$i]['LATEST_UPDATE']=fromDBDate($tmp[0])." ".$tmp[1];
    $res[$i]['LINKED_PROPERTIES']='';
    $tmp=SQLSelect("SELECT * FROM zwave_properties WHERE DEVICE_ID='".$res[$i]['ID']."'");
    $total2=count($tmp);
    for($i2=0;$i2<$total2;$i2++) {
     if ($tmp[$i2]['LINKED_OBJECT'] && $tmp[$i2]['LINKED_PROPERTY']) {
      $object=(SQLSelectOne("SELECT ID, CLASS_ID FROM objects WHERE TITLE LIKE '".DBSafe($tmp[$i2]['LINKED_OBJECT'])."'"));
      $res[$i]['LINKED_PROPERTIES'].='<a href="/panel/class/'.$object['CLASS_ID'].'/object/'.$object['ID'].'/properties.html">'.$tmp[$i2]['LINKED_OBJECT'].'.'.$tmp[$i2]['LINKED_PROPERTY'].'</a>; ';
     }
    }

   }
   $out['RESULT']=$res;
  }

  $out['LOCATIONS']=SQLSelect("SELECT * FROM locations ORDER BY TITLE");

?>