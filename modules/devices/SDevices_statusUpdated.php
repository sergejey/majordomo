<?php

 $ot=$this->object_title;
 $linked_room=$this->getProperty('linkedRoom');

 $tm=time();
 $this->setProperty('updated', $tm);
 $this->callMethod('setUpdatedText');
 $this->setProperty('alive', 1);


 $alive_timeout=(int)$this->getProperty('aliveTimeout')*60*60;
 if (!$alive_timeout) {
  $alive_timeout=2*24*60*60; // 2 days alive timeout by default
 }

 setTimeout($ot.'_alive_timer', 'setGlobal("'.$ot.'.alive", 0);', $alive_timeout);

if ($this->class_title == 'SMotions' && $params['NEW_VALUE'] && !timeOutExists($ot.'_motion_timer_status')) {
    $this->callMethodSafe('motionDetected',array('statusUpdated'=>1));
} elseif ($this->class_title == 'SButtons' && $params['NEW_VALUE'] && !timeOutExists($ot.'_pressed_status')) {
    $this->callMethodSafe('pressed',array('statusUpdated'=>1));
}

if ($params['NEW_VALUE'] && $linked_room && $this->getProperty('isActivity')) {
 if (getGlobal('NobodyHomeMode.active')) {
  callMethodSafe('NobodyHomeMode.deactivate', array('sensor'=>$ot, 'room'=>$linked_room));
 }
 ClearTimeOut("nobodyHome");
 SetTimeOut("nobodyHome","callMethodSafe('NobodyHomeMode.activate');", 1*60*60);
 if ($linked_room) {
  callMethodSafe($linked_room.'.onActivity', array('sensor'=>$ot));
 }
}

$this->callMethod('logicAction');

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);
