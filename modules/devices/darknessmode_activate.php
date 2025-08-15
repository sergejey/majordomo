<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

$is_eco_mode = getGlobal('EconomMode.active');

//groupSunset
$objects = getObjectsByProperty('groupSunset', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {

    $obj = getObject($objects[$i]);
    if (is_object($obj) && $obj->device_id && !checkAccess('prop_groupSunset', $obj->device_id)) continue;
    if ($is_eco_mode && getGlobal($objects[$i] . '.groupEcoOn')) continue; // skip if EconomMode is active

    callMethodSafe($objects[$i] . '.turnOn', array('source' => 'DarknessMode'));
    usleep(50000);

}