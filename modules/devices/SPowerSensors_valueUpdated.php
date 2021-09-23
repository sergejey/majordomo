<?php

$ot = $this->object_title;

$this->callMethodSafe('keepAlive');
$status = $this->getProperty('status');
$currentValue = $this->getProperty('value');
$min_value = (float)$this->getProperty('minValue');

$loadTimeout = $this->getProperty('loadStatusTimeout');
$tmp = explode(',', $loadTimeout);
$tmp = array_map('trim', $tmp);
$onTimeout = (int)$tmp[0];
if ($tmp[1]) {
    $offTimeout = (int)$tmp[1];
} else {
    $offTimeout = $onTimeout;
}

if (!$min_value) {
    $min_value = 1;
}

$timerOn = $ot . '_turned_on';
$timerOff = $ot . '_turned_off';

if ($currentValue >= $min_value) {
    clearTimeout($timerOff); //
    if (!$status) {
        if (!timeOutExists($timerOn)) {
            setTimeout($timerOn, 'setGlobal("' . $ot . '.status", 1);callMethod("' . $ot . '.loadStatusChanged", array("status"=>1));', $onTimeout);
        }
    }
} elseif ($currentValue<$min_value) {
    clearTimeOut($timerOn); //
    if ($status) {
        if (!timeOutExists($timerOff)) {
            setTimeout($timerOff, 'setGlobal("' . $ot . '.status", 0);callMethod("' . $ot . '.loadStatusChanged", array("status"=>0));', $offTimeout);
        }
    }
}

//$this->callMethod('statusUpdated');