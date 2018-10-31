<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

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
