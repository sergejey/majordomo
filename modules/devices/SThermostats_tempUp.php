<?php
$status = $this->getProperty('status');
if (isset($params['value'])) {
    $threshold = $params['value'];
} else {
    $threshold = 0.5;
}
if ($status) {
    $targetTitle='normalTargetValue';
} else {
    $targetTitle='ecoTargetValue';
}
$targetTemperature = $this->getProperty($targetTitle);
$targetTemperature+=$threshold;
$this->setProperty($targetTitle,$targetTemperature);

$this->callMethod('valueUpdated');
