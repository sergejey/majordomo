<?php

// The target temperature may be empty or hold a non numeric string: since PHP 8
// such a string in an arithmetic operation throws a TypeError, so both the step
// and the target value are cast to float.
//
// The sign is applied by the method itself, so a positive value passed in the
// parameters lowers the temperature as well.

$status = $this->getProperty('status');
if (isset($params['value'])) {
    $threshold = abs((float)$params['value']);
} else {
    $threshold = (float)$this->getProperty('tempStep');
    if (!$threshold) $threshold = 1;
}
$threshold = $threshold * (-1);
$targetTitle = 'currentTargetValue';
$targetTemperature = (float)$this->getProperty($targetTitle);
$targetTemperature += $threshold;
$this->setProperty($targetTitle, $targetTemperature);
