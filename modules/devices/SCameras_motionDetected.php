<?php

<<<<<<< HEAD
$ot=$this->object_title;
if (!isset($params['statusUpdated'])) {
 setTimeout($ot.'_motion_timer_status', '', 3);
}

 if (isset($params['VALUE']) && !$params['VALUE'] && !isset($params['statusUpdated'])) {
  $this->setProperty('status', 0);
  return;
 }

 $motion_timeout=20; // seconds timeout
 $nobodysHome=getGlobal('NobodyHomeMode.active');

 if (!isset($params['statusUpdated'])) {
  $this->setProperty('status', 1);
 }
 setTimeout($ot.'_motion_timer', 'setGlobal("'.$ot.'.status", 0);', $motion_timeout);

 if ($nobodysHome && $this->getProperty('ignoreNobodysHome')) {
  return;
 }

 $this->callMethod('logicAction');
 $linked_room=$this->getProperty('linkedRoom');
 if ($nobodysHome) {
  callMethodSafe('NobodyHomeMode.deactivate', array('sensor'=>$ot, 'room'=>$linked_room));
 }
 ClearTimeOut("nobodyHome"); 
 SetTimeOut("nobodyHome","callMethodSafe('NobodyHomeMode.activate');", 1*60*60);

 if ($linked_room) {
  callMethodSafe($linked_room.'.onActivity', array('sensor'=>$ot));
 }

/*
include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title);
*/
=======
$ot = $this->object_title;

$this->setProperty('status', 1);
$this->setProperty('activeHTML', $this->getProperty('previewHTML'));

$motion_timeout=20; // seconds timeout
setTimeout($ot.'_motion_timer', 'setGlobal("'.$ot.'.status", 0);setGlobal("'.$ot.'.activeHTML", '');', $motion_timeout);

$this->callMethod('logicAction');
>>>>>>> 291423a6794307cbb9cae969cf85cf317d841614
