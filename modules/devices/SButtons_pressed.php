<?php

$ot = $this->object_title;

$this->callMethodSafe('keepAlive');

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
include_once(dirname(__FILE__) . '/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $this->getProperty('status'));
*/
