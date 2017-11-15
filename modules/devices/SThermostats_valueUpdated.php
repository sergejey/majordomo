<?php

$status = $this->getProperty('status');
$currentTemperature = $this->getProperty('value');
$threshold = (float)$this->getProperty('threshold');
if ($threshold == 0) {
    $threshold = 0.25;
}
if ($status) {
    $targetTemperature = $this->getProperty('normalTargetValue');
} else {
    $targetTemperature = $this->getProperty('ecoTargetValue');
}


$this->setProperty('currentTargetValue',$targetTemperature);

$need_action = 0;

if ($currentTemperature > ($targetTemperature+$threshold)) {
    $need_action = 1;
    $this->setProperty('relay_status',0);
} elseif ($currentTemperature < ($targetTemperature-$threshold)) {
    $need_action = 1;
    $this->setProperty('relay_status',1);
}
//echo "current: $currentTemperature target: $targetTemperature action: $need_action <br/>";

if ($need_action) {
    include_once(DIR_MODULES.'devices/devices.class.php');
    $dv=new devices();
    $dv->checkLinkedDevicesAction($this->object_title, $currentTemperature);
    $this->callMethod('logicAction');
}