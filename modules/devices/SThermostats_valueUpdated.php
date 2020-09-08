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


$this->setProperty('currentTargetValue',$targetTemperature);

//$need_action = 0;

if ($currentTemperature > ($targetTemperature+$threshold)) { // temperature too high
    //$need_action = 1;
    if ($ncno == 'no') {
        $this->setProperty('relay_status',1); // turn on
    } else {
        $this->setProperty('relay_status',0); // turn off
    }
} elseif ($currentTemperature < ($targetTemperature-$threshold)) { // temperature too low
    //$need_action = 1;
    if ($ncno == 'no') {
        $this->setProperty('relay_status',0); // turn off
    } else {
        $this->setProperty('relay_status',1); // turn on
    }
}
//echo "current: $currentTemperature target: $targetTemperature action: $need_action <br/>";

//if ($need_action) {
    include_once(dirname(__FILE__).'/devices.class.php');
    $dv=new devices();
    $dv->checkLinkedDevicesAction($this->object_title, $currentTemperature);
    $this->callMethod('logicAction');
//}
