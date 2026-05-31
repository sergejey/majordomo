<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES == 1) return;

//groupSunrise
$objects = getObjectsByProperty('groupSunrise', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    if (getGlobal($objects[$i] . '.status')) {

        $obj = getObject($objects[$i]);
        if (is_object($obj) && $obj->device_id && !checkAccess('prop_groupSunrise', $obj->device_id)) continue;

        callMethodSafe($objects[$i] . '.turnOff', array('source' => 'DarknessMode'));
        usleep(50000);

    }
}