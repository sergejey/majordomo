<?php

$status = $this->getProperty('status');
$targetTemperature = $this->getProperty('currentTargetValue');

if ($status) {
    $targetTitle = 'normalTargetValue';
} else {
    $targetTitle = 'ecoTargetValue';
}

$newTargetTemperature = $this->getProperty($targetTitle);
if ($targetTemperature != $newTargetTemperature) {
    $this->setProperty('currentTargetValue', $newTargetTemperature);
}
