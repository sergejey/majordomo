<?php

$ot = $this->object_title;
$this->callMethod('keepAlive');

$status = $this->getProperty('status');
$targetTemperature = $this->getProperty('currentTargetValue');
if ($status) {
    $oldTargetTitle = 'normalTargetValue';
} else {
    $oldTargetTitle = 'ecoTargetValue';
}
$oldTargetTemperature = $this->getProperty($oldTargetTitle);
if ($oldTargetTemperature != $targetTemperature) {
    $this->setProperty($oldTargetTitle, $targetTemperature);
}

$disabled = $this->getProperty('disabled');
if ($disabled) {
    return;
}

$currentTemperature = $this->getProperty('value');
$ncno = $this->getProperty('ncno');
$threshold = (float)$this->getProperty('threshold');
if ($threshold == 0) {
    $threshold = 0.25;
}

$openableSensors = $this->getProperty('openableSensors');
if ($openableSensors != '') {
    $timeOutTitle = $ot . '_checkwindows';
    if (!timeOutExists($timeOutTitle)) {
        setTimeOut($timeOutTitle, 'callMethod("' . $ot . '.checkWindows");', 1 * 60);
    }
    $windowIsOpen = $this->getProperty('windowIsOpen');
} else {
    $windowIsOpen = false;
}

$readonly = $this->getProperty('relay_readonly');
if (!$readonly) {
    $currentRelayStatus = $this->getProperty('relay_status');
    if ($windowIsOpen) {
        $targetRelayStatus = 0;
    } else {
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
    }
    if ($targetRelayStatus != $currentRelayStatus) {
        $this->setProperty('relay_status', $targetRelayStatus);
    }
}

include_once(dirname(__FILE__) . '/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($this->object_title, $params);
$this->callMethod('logicAction');

