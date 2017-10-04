<?php

$this->callMethod('statusUpdated');
$this->callMethod('logicAction');

$ot=$this->object_title;
$linked_room=$this->getProperty('linkedRoom');

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);