<?php

include_once(dirname(__FILE__).'/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $params);