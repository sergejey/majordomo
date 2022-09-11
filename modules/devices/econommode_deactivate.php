<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES == 1) return;

//groupEcoOn
$objects = getObjectsByProperty('groupEcoOn', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {

    $obj = getObject($objects[$i]);
    if (is_object($obj) && $obj->device_id && !checkAccess('prop_groupEcoOn', $obj->device_id)) continue;

    callMethodSafe($objects[$i] . '.turnOn', array('source' => 'EconomMode'));
    usleep(50000);
}