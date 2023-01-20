<?php

$batteryOperated = (int)$this->getProperty('batteryOperated');
$batteryLevel = (int)$this->getProperty('batteryLevel');

if ($batteryOperated && $batteryLevel <= 20) {
    $batteryWarning = 1;
} elseif ($batteryOperated && $batteryLevel <= 50) {
    $batteryWarning = 2;
} else {
    $batteryWarning = 0;
}

$this->setProperty('batteryWarning', $batteryWarning);

if ($batteryOperated && $batteryLevel>0) {
    $this->callMethod('keepAlive');
}