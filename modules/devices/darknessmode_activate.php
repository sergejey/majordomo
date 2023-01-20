<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

//groupSunset
$objects = getObjectsByProperty('groupSunset', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {

    $obj = getObject($objects[$i]);
    if (is_object($obj) && $obj->device_id && !checkAccess('prop_groupSunset', $obj->device_id)) continue;

    callMethodSafe($objects[$i] . '.turnOn', array('source' => 'DarknessMode'));
    usleep(50000);

}