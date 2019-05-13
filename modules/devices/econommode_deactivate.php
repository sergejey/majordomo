<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

//groupEcoOn
$objects = getObjectsByProperty('groupEcoOn', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    usleep(50000);
    callMethodSafe($objects[$i] . '.turnOn', array('source' => 'EconomMode'));
    //sleep(1);
}