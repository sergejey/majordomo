<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

//groupSunrise
$objects = getObjectsByProperty('groupSunrise', '=', 1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
  if (getGlobal($objects[$i] . '.status')) {
    usleep(50000);
    callMethodSafe($objects[$i] . '.turnOff', array('source' => 'DarknessMode'));
    //sleep(1);
  }
}