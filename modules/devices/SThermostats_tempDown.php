<?php
$status = $this->getProperty('status');
if (isset($params['value'])) {
    $increment = $params['value'];
} else {
    $increment = $this->getProperty('increment');
    if ($increment != '') {
        $increment = (float)$increment * (-1);
    } else {
        $increment = -0.5;
    }
}

$targetTitle = 'currentTargetValue';
$targetTemperature = $this->getProperty($targetTitle);
$targetTemperature += $increment;
$this->setProperty($targetTitle, $targetTemperature);
$this->callMethod('valueUpdated');
