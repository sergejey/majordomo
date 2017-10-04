<?php

 if (!isset($params['statusUpdated'])) {
  $this->setProperty('status', 1);
 }

 $this->callMethod('statusUpdated');
 $this->callMethod('logicAction');

 $linked_room=$this->getProperty('linkedRoom');
 if ($linked_room) {
  callMethodSafe($linked_room.'.onActivity', array('sensor'=>$ot));
 }

 include_once(DIR_MODULES.'devices/devices.class.php');
 $dv=new devices();
 $dv->checkLinkedDevicesAction($this->object_title, $this->getProperty('status'));