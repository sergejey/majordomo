<?php

$batteryOperated = (int)$this->getProperty('batteryOperated');
$batteryLevelRaw = $this->getProperty('batteryLevel');
$batteryLevel = ($batteryLevelRaw === '' || $batteryLevelRaw === false || $batteryLevelRaw === null)
    ? null
    : (int)$batteryLevelRaw;

if (!$batteryOperated || $batteryLevel === null) {
    $batteryWarning = 0;
} elseif ($batteryLevel <= 20) {
    $batteryWarning = 1;
} elseif ($batteryLevel <= 50) {
    $batteryWarning = 2;
} else {
    $batteryWarning = 0;
}

$this->setProperty('batteryWarning', $batteryWarning);

// Any battery report means the device is alive, even when the level is 0.
if ($batteryOperated) {
    $this->callMethod('keepAlive');
}

if (isModuleInstalled('homekit')) {
    $path = DIR_MODULES . 'homekit/homebridgeSendUpdate.inc.php';
    // send updated status to module HomeKit
    if (file_exists($path)) {
        $device1 = SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '" . $this->object_title . "'");
        require($path);
    }
}
