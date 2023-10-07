<?php

$this->setProperty('disabled', 1);
$this->setProperty('relay_status', 0); // turn off relay

$currentTemperature = $this->getProperty('value');
include_once(dirname(__FILE__).'/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $currentTemperature);
$this->callMethod('logicAction');