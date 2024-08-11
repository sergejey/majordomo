<?php

$this->callMethod('keepAlive');

$disabled = $this->getProperty('disabled');
if ($disabled) {
    return;
}

$status = $this->getProperty('status');
$currentTemperature = $this->getProperty('value');
$ncno = $this->getProperty('ncno');
$threshold = (float)$this->getProperty('threshold');
if ($threshold == 0) {
    $threshold = 0.25;
}
if ($status) {
    $targetTemperature = $this->getProperty('normalTargetValue');
} else {
    $targetTemperature = $this->getProperty('ecoTargetValue');
}


$this->setProperty('currentTargetValue', $targetTemperature);

//$need_action = 0;
$currentRelayStatus = $this->getProperty('relay_status');
$targetRelayStatus = $currentRelayStatus;

if ($currentTemperature > ($targetTemperature + $threshold)) { // temperature too high
    if ($ncno == 'no') {
        $targetRelayStatus = 1; // turn on (cooling on)
    } else {
        $targetRelayStatus = 0; // turn off (heating off)
    }
} elseif ($currentTemperature < ($targetTemperature - $threshold)) { // temperature too low
    if ($ncno == 'no') {
        $targetRelayStatus = 0; // turn off (cooling off)
    } else {
        $targetRelayStatus = 1; // turn on (heating on)
    }
}

if ($targetRelayStatus != $currentRelayStatus) {
    $this->setProperty('relay_status', $targetRelayStatus);
}

//echo "current: $currentTemperature target: $targetTemperature action: $need_action <br/>";

include_once(dirname(__FILE__) . '/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($this->object_title, $params);
$this->callMethod('logicAction');

