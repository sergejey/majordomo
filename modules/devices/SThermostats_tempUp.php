<?php

if (isset($params['value'])) {
    $increment = $params['value'];
} else {
    $increment = $this->getProperty('increment');
    if ($increment != '') {
        $increment = (float)$increment;
    } else {
        $increment = 0.5;
    }
}

$targetTitle = 'currentTargetValue';
$targetTemperature = $this->getProperty($targetTitle);
$targetTemperature += $increment;
$this->setProperty($targetTitle, $targetTemperature);
$this->callMethod('valueUpdated');
