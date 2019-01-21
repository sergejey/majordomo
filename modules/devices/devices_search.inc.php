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

$type=gr('type');
  if ($type!='') {
   $qry.=" AND devices.TYPE='".DBSafe($type)."'";
   $out['TYPE']=$type;
  }

$location_id=gr('location_id');
if ($location_id == 'manage_locations') {
    $this->redirect("?(panel:{action=locations})");
} elseif ($location_id) {
 $out['LOCATION_ID']=(int)$location_id;
 $qry.=" AND devices.LOCATION_ID=".$out['LOCATION_ID'];
}

$group_name=gr('group_name');
if ($group_name=='manage_groups') {
    $this->redirect("?view_mode=manage_groups");
} elseif ($group_name) {
    $object_names=getObjectsByProperty('group'.$group_name,1);
    if (!is_array($object_names)) {
        $object_names=array(0);
    }
    $total = count($object_names);
    for($i=0;$i<$total;$i++) {
        $object_names[$i]="'".$object_names[$i]."'";
    }
    $qry.=" AND devices.LINKED_OBJECT IN (".implode(',',$object_names).")";
    $out['GROUP_NAME']=$group_name;
}

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['devices_qry'];
  } else {
   $session->data['devices_qry']=$qry;
  }
  if (!$qry) $qry="1";

  $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices");
  $out['TOTAL']=(int)$tmp['TOTAL'];

  $loc_title='';
  $sortby_devices="locations.PRIORITY DESC, devices.LOCATION_ID, devices.TYPE, devices.TITLE";
  $out['SORTBY']=$sortby_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT devices.*, locations.TITLE as LOCATION_TITLE FROM devices LEFT JOIN locations ON devices.LOCATION_ID=locations.ID WHERE $qry ORDER BY ".$sortby_devices);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
       if ($res[$i]['LOCATION_TITLE']!=$loc_title) {
           $res[$i]['NEW_LOCATION']=1;
           $loc_title=$res[$i]['LOCATION_TITLE'];
       }
    if ($res[$i]['LINKED_OBJECT']) {
     $processed=$this->processDevice($res[$i]['ID']);
     $res[$i]['HTML']=$processed['HTML'];
    }
    $res[$i]['TYPE_TITLE']=$this->device_types[$res[$i]['TYPE']]['TITLE'];
       $linked=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices_linked WHERE (DEVICE1_ID=".$res[$i]['ID']." OR DEVICE2_ID=".$res[$i]['ID'].")");
       if ($linked['TOTAL']) {
           $res[$i]['LINKED']=$linked['TOTAL'];
       }
   }
   $out['RESULT']=$res;
  }

$types=array();
foreach($this->device_types as $k=>$v) {
 if ($v['TITLE']) {
  $type_rec=array('NAME'=>$k,'TITLE'=>$v['TITLE']);
  $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices WHERE TYPE='".$k."'");
     $type_rec['TOTAL']=(int)$tmp['TOTAL'];
     if ($type_rec['TOTAL']>0) {
         $types[]=$type_rec;
     }
 }
}
usort($types, function($a,$b) {
    return strcmp($a["TITLE"], $b["TITLE"]);
});
$out['TYPES']=$types;

$locations=SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE+0");
$total = count($locations);
for ($i = 0; $i < $total; $i++) {
  $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices WHERE LOCATION_ID='".$locations[$i]['ID']."'");
  $locations[$i]['TOTAL']=(int)$tmp['TOTAL'];
}
$out['LOCATIONS']=$locations;
//var_dump($this->getWatchedProperties(0));exit;

$groups = SQLSelect("SELECT * FROM devices_groups ORDER BY TITLE");
$out['GROUPS']=$groups;