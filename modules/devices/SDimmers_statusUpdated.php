<?php

$status = $this->getProperty('status');
$level = $this->getProperty('level');
$levelSaved = $this->getProperty('levelSaved');
if ($this->getProperty('setMaxTurnOn')) {
    $levelSaved = 100;
}
if ($status > 0 && !$level && $levelSaved) {
    $this->setProperty('level', $levelSaved);
} else {
    $this->callMethod('logicAction');
    include_once(DIR_MODULES . 'devices/devices.class.php');
    $dv = new devices();
    $dv->checkLinkedDevicesAction($this->object_title, $level);
}