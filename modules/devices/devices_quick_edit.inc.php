<?php

if ($this->mode=='update') {
    //dprint($_REQUEST);
    $location_ids=array();
    foreach($_REQUEST as $k=>$v) {
        if (preg_match('/location(\d+)/',$k,$m)) {
            $location_rec=SQLSelectOne("SELECT * FROM locations WHERE ID=".$m[1]);
            if ($location_rec['ID']) {
                $location_rec['TITLE']=gr($k);
                SQLUpdate('locations',$location_rec);
            } else {
                $location_rec=array();
                $location_rec['TITLE']=gr($k);
                $location_rec['ID']=SQLInsert('locations',$location_rec);
            }
            $location_ids[$m[1]]=$location_rec['ID'];
        }
        if (preg_match('/device(\d+)/',$k,$m)) {
            $device=SQLSelectOne("SELECT * FROM devices WHERE ID=".$m[1]);
            if ($device['ID'] && $device['TITLE']!=gr($k)) {
                $device['TITLE']=gr($k);
                SQLUpdate('devices',$device);
                $object_rec = SQLSelectOne("SELECT * FROM objects WHERE TITLE='" . DBSafe($device['LINKED_OBJECT'])."'");
                if ($object_rec['ID']) {
                    $object_rec['DESCRIPTION'] = $device['TITLE'];
                    SQLUpdate('objects',$object_rec);
                }
            }
        }
    }
    //dprint($_REQUEST);
    $devices_added=0;
    foreach($_REQUEST as $k=>$v) {
        if (preg_match('/newdevice(\d+)_l(\d+)/',$k,$m)) {
            $device_title = gr('newdevice'.$m[1].'_l'.$m[2]);
            $device_type= gr('newtype'.$m[1].'_l'.$m[2]);
            $location_id=$location_ids[$m[2]];
            if ($device_title && $device_type && $location_id) {
                $rec=array();
                $rec['TITLE']=$device_title;
                $rec['LOCATION_ID']=$location_id;
                $rec['TYPE']=$device_type;
                $rec['ID']=SQLInsert('devices',$rec);

                $location_title = getRoomObjectByLocation($rec['LOCATION_ID'], 1);

                $type_details = $this->getTypeDetails($rec['TYPE']);
                $prefix = ucfirst($rec['TYPE']);
                $new_object_title = $prefix . $this->getNewObjectIndex($type_details['CLASS'], $prefix);
                $object_id = addClassObject($type_details['CLASS'], $new_object_title, 'sdevice' . $rec['ID']);
                $rec['LINKED_OBJECT'] = $new_object_title;
                SQLUpdate('devices', $rec);

                $object_id = addClassObject($type_details['CLASS'], $rec['LINKED_OBJECT'], 'sdevice' . $rec['ID']);
                $class_id = current(SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($type_details['CLASS']) . "'"));

                $object_rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . $object_id);
                $object_rec['DESCRIPTION'] = $rec['TITLE'];
                $object_rec['LOCATION_ID'] = $rec['LOCATION_ID'];
                SQLUpdate('objects', $object_rec);

                if ($location_title) {
                    setGlobal($object_rec['TITLE'] . '.linkedRoom', $location_title);
                }

                if ($rec['TYPE'] == 'sensor_temp') {
                    setGlobal($object_rec['TITLE'] . '.minValue', 16);
                    setGlobal($object_rec['TITLE'] . '.maxValue', 25);
                }
                if ($rec['TYPE'] == 'sensor_humidity') {
                    setGlobal($object_rec['TITLE'] . '.minValue', 30);
                    setGlobal($object_rec['TITLE'] . '.maxValue', 60);
                }
                $devices_added++;
            }
        }
    }
    
    addToOperationsQueue('connect_sync_devices', 'required');
    $this->homebridgeSync();

    $this->redirect("?");
}

$locations = SQLSelect("SELECT * FROM locations ORDER BY TITLE");
$total = count($locations);
for($i=0;$i<$total;$i++) {
    $devices=SQLSelect("SELECT * FROM devices WHERE LOCATION_ID=".$locations[$i]['ID']." ORDER BY TITLE");
    $locations[$i]['DEVICES']=$devices;
}
$out['LOCATIONS'] = $locations;

$types = array();
foreach ($this->device_types as $k => $v) {
    if ($v['TITLE']) {
        $types[] = array('NAME' => $k, 'TITLE' => $v['TITLE']);
    }
}

usort($types, function ($a, $b) {
    return strcmp($a['TITLE'], $b['TITLE']);
});
$out['TYPES'] = $types;

$types_options='<option value="">'.LANG_TYPE;
foreach($out['TYPES'] as $type) {
    $types_options.='<option value="'.$type['NAME'].'">'.$type['TITLE'];
}
$out['TYPES_OPTIONS']=$types_options;