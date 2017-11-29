<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

//groupEco
$objects=getObjectsByProperty('groupEco','=',1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    callMethodSafe($objects[$i].'.turnOff');
}