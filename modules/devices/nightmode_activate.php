<?php
if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;
//groupNight
$objects = getObjectsByProperty('groupNight', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    $currentStatus = gg($objects[$i] . '.status');
    if ($currentStatus) {
        usleep(50000);
        callMethodSafe($objects[$i] . '.turnOff', array('source' => 'NightMode'));
    }
}