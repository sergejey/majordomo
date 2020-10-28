<?php

$status = $this->getProperty('status');
if (isset($params['value'])) {
    $threshold = $params['value'];
} else {
    $threshold = $this->getProperty('tempStep');
    if (!$threshold) $threshold=1;
}

$targetTitle='currentTargetValue';
$targetTemperature = $this->getProperty($targetTitle);
$targetTemperature+=$threshold;
$this->setProperty($targetTitle,$targetTemperature);
