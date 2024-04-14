<?php

$this->setProperty('disabled', 1);
$this->setProperty('relay_status', 0); // turn off relay

$params['NEW_VALUE'] = $params['OLD_VALUE'] = $this->getProperty('value');
include_once(dirname(__FILE__).'/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $params);
$this->callMethod('logicAction');