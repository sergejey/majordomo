<?php

$this->callMethod('keepAlive');

$currentTemperature = $this->getProperty('value');

//if ($need_action) {
    include_once(dirname(__FILE__).'/devices.class.php');
    $dv=new devices();
    $dv->checkLinkedDevicesAction($this->object_title, $currentTemperature);
    $this->callMethod('logicAction');
//}
