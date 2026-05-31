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
if(isModuleInstalled('homekit')){
	$path = DIR_MODULES . 'homekit/homebridgeSendUpdate.inc.php';
	// send updated status to module HomeKit
	if (file_exists($path)) {
		$device1 = SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '" . $this->object_title . "'");
		require($path);
    }
}