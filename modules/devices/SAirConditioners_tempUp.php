<?php

$status = $this->getProperty('status');
if (isset($params['value'])) {
    $threshold = $params['value'];
} else {
    $threshold = +0.5;
}

$targetTitle='currentTargetValue';
$targetTemperature = $this->getProperty($targetTitle);
$targetTemperature+=$threshold;
$this->setProperty($targetTitle,$targetTemperature);
