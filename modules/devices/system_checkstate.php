<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

$devices=SQLSelect("SELECT ID, TITLE, TYPE, LINKED_OBJECT FROM devices ORDER BY TYPE, TITLE");
$total = count($devices);
for($idv=0;$idv<$total;$idv++) {
    if (($devices[$idv]['TYPE'] == 'motion' ||
        $devices[$idv]['TYPE'] == 'openclose' ||
        $devices[$idv]['TYPE'] == 'leak' ||
        $devices[$idv]['TYPE'] == 'smoke' ||
        $devices[$idv]['TYPE'] == 'counter' ||
        preg_match('/^sensor/',$devices[$idv]['TYPE']) ||
        $this->device_types[$devices[$idv]['TYPE']]['PARENT_CLASS'] == 'SSensors' ||
        (int)gg($devices[$idv]['LINKED_OBJECT'] . '.aliveTimeout')>0
    ) && gg($devices[$idv]['LINKED_OBJECT'] . '.alive') === '0') {
        $yellow_state=1;
        $details[]=$devices[$idv]['TITLE'].' '.LANG_DEVICES_NOT_UPDATING;
    }
}

/*
$classes_to_check=array('SMotions','SOpenClose','SSensors');
$res_objects=array();
foreach($classes_to_check as $class_name) {
   $objects=getObjectsByClass($class_name);
    foreach($objects as $obj) {
        $res_objects[]=$obj;
    }
}

$fails=array();
$total = count($res_objects);
for ($i = 0; $i < $total; $i++) {
    $alive=getGlobal($res_objects[$i]['TITLE'].'.alive');
    if (!$alive) {
        $object_rec=SQLSelectOne("SELECT DESCRIPTION FROM objects WHERE ID=".$res_objects[$i]['ID']);
        $yellow_state=1;
        $details[]=$res_objects[$i]['TITLE'].' ('.processTitle($object_rec['DESCRIPTION']).') '.LANG_DEVICES_NOT_UPDATING;
    }
}
*/