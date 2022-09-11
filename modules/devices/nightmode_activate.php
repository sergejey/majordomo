<?php
if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES == 1) return;

//groupNight
$objects = getObjectsByProperty('groupNight', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    $currentStatus = gg($objects[$i] . '.status');
    if ($currentStatus) {

        $obj = getObject($objects[$i]);
        if (is_object($obj) && $obj->device_id && !checkAccess('prop_groupNight', $obj->device_id)) continue;

        callMethodSafe($objects[$i] . '.turnOff', array('source' => 'NightMode'));
        usleep(50000);

    }
}