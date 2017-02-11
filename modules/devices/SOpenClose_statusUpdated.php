<?php

 $this->callMethod('logicAction');

 if ($this->getProperty('isActivity')) {
  $linked_room=$this->getProperty('linkedRoom');
  if (getGlobal('NobodyHomeMode.active')) {
   callMethod('NobodyHomeMode.deactivate');
  }
  ClearTimeOut("nobodyHome"); 
  SetTimeOut("nobodyHome","callMethod('NobodyHomeMode.activate');", 1*60*60);
  if ($linked_room) {
   callMethod($linked_room.'.onActivity', array('sensor'=>$ot));
  }  
 }

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $this->getProperty('status'));