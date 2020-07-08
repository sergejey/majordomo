<?php

$ot = $this->object_title;
if (!isset($params['statusUpdated'])) {
  setTimeout($ot . '_pressed_status', '', 3);
  $this->setProperty('status', 1);
}

//$this->callMethod('statusUpdated');
//$this->callMethod('logicAction');

$linked_room=$this->getProperty('linkedRoom');
if ($linked_room && $this->getProperty('isActivity')) {
  callMethodSafe($linked_room . '.onActivity', array('sensor' => $ot));
}

/*
include_once(DIR_MODULES . 'devices/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $this->getProperty('status'));
*/