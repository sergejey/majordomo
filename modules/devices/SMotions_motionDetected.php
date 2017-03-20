<?php

 if (isset($params['VALUE']) && !$params['VALUE']) {
  $this->setProperty('status', 0);
  return;
 }

 $nobodyhome=getGlobal('NobodyHomeMode.active');

 if ($nobodyhome && $this->getProperty('ignoreModeChange')) {
  return;
 }

 $this->setProperty('status', 1);

 $motion_timeout=20; // seconds timeout
 $ot=$this->object_title;
 setTimeout($ot.'_motion_timer', 'setGlobal("'.$ot.'.status", 0);', $motion_timeout);

 $this->callMethod('logicAction');

 $linked_room=$this->getProperty('linkedRoom');

 if ($nobodyhome) {
  callMethod('NobodyHomeMode.deactivate', array('sensor'=>$ot, 'room'=>$linked_room));
 }
 ClearTimeOut("nobodyHome"); 
 SetTimeOut("nobodyHome","callMethod('NobodyHomeMode.activate');", 1*60*60);

 if ($linked_room) {
  callMethod($linked_room.'.onActivity', array('sensor'=>$ot));
 }

/*
include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title);
*/
