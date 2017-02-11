<?php

 $this->setProperty('status', 1);
 $this->callMethod('statusUpdated');
 $this->callMethod('logicAction');

 include_once(DIR_MODULES.'devices/devices.class.php');
 $dv=new devices();
 $dv->checkLinkedDevicesAction($this->object_title, $this->getProperty('status'));