<?php

$ot = $this->object_title;
$status = $this->getProperty('status');

//DebMes("Updated currentTargetValue $ot - new value: " . $params['NEW_VALUE'], 'thermostat');

if ($status) {
    $targetTitle = 'normalTargetValue';
} else {
    $targetTitle = 'ecoTargetValue';
}
$this->setProperty($targetTitle, $params['NEW_VALUE']);

if ($this->getProperty('isConfirmationRequired') && isset($params['PROPERTY'])) {
    require(DIR_MODULES . 'devices/delivery_confirmation.inc.php');
}

$this->callMethod('valueUpdated');