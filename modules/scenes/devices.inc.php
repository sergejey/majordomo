<?php

$top = gr('top', 'int');
$out['TOP']=$top;
$left = gr('left', 'int');
$out['LEFT']=$left;

$device_id = gr('device_id', 'int');
$search = gr('search', 'trim');
$type = gr('type', 'trim');
$location_id = gr('location_id', 'trim');


$scene_id = $rec['ID'];

if ($this->mode == 'add_device') {
    $device_rec=SQLSelectOne("SELECT * FROM devices WHERE ID=".$device_id);
    if ($device_rec['ID']) {
        $element = array();
        $element['SCENE_ID']=$scene_id;
        $element['TYPE']='device';
        $element['DEVICE_ID']=$device_id;
        $element['TITLE']=$device_rec['TITLE'];
        if ($top && $left) {
            $element['TOP']=$top;
            $element['LEFT']=$left;
        } else {
            $element['TOP']=50;
            $element['LEFT']=50;
        }
        $element['ID']=SQLInsert('elements',$element);
        $this->redirect("?id=".$scene_id."&view_mode=".$this->view_mode."&tab=".$this->tab."&top=".($element['TOP']+20)."&left=".$left."&search=".urlencode($search)."&type=".$type."&location_id=".$location_id);
    }
}
if ($this->mode == 'delete_device') {
    $element = SQLSelectOne("SELECT * FROM elements WHERE SCENE_ID=" . $scene_id . " AND TYPE='device' AND DEVICE_ID=" . $device_id);
    if ($element['ID']) {
        $this->delete_elements($element['ID']);
    }
    $this->redirect("?id=".$scene_id."&view_mode=".$this->view_mode."&tab=".$this->tab."&top=".$top."&left=".$left."&search=".urlencode($search)."&type=".$type."&location_id=".$location_id);
}

$elements = SQLSelect("SELECT DEVICE_ID FROM elements WHERE SCENE_ID=" . $scene_id . " AND TYPE='device'");
$added_ids = array_map('current', $elements);
$added_ids[] = 0;

$added_devices = SQLSelect("SELECT ID,TITLE FROM devices WHERE ID IN (" . implode(',', $added_ids) . ") ORDER BY TITLE");
$out['ADDED_DEVICES'] = $added_devices;

$qry = "1";


if ($search) {
    $qry .= " AND devices.TITLE LIKE '%" . DBSafe($search) . "%'";
    $out['SEARCH'] = htmlspecialchars($search);
    $out['SEARCH_URL'] = urlencode($search);
}
if ($type) {
    $qry .= " AND devices.TYPE='" . $type . "'";
    $out['TYPE'] = $type;
}
if ($location_id) {
    $qry .= " AND devices.LOCATION_ID='" . $location_id . "'";
    $out['LOCATION_ID'] = $location_id;
}

$other_devices = SQLSelect("SELECT ID,TITLE FROM devices WHERE ID NOT IN (" . implode(',', $added_ids) . ") AND $qry ORDER BY TITLE");
$out['OTHER_DEVICES'] = $other_devices;

$out['TYPES'] = SQLSelect("SELECT DISTINCT(TYPE) FROM devices ORDER BY TYPE");

$out['LOCATIONS'] = SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE+0");