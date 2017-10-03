<?php

 $ot=$this->object_title;

 $tm=time();
 $this->setProperty('updated', $tm);
 $this->callMethod('setUpdatedText');
 $this->setProperty('alive', 1);

 $alive_timeout=(int)$this->getProperty('aliveTimeout')*60*60;
 if (!$alive_timeout) {
  $alive_timeout=2*24*60*60; // 2 days alive timeout by default
 }

 setTimeout($ot.'_alive_timer', 'setGlobal("'.$ot.'.alive", 0);', $alive_timeout);


 if ($this->getProperty('isActivity')) {
  $linked_room=$this->getProperty('linkedRoom');
  if (getGlobal('NobodyHomeMode.active')) {
   callMethodSafe('NobodyHomeMode.deactivate');
  }
  ClearTimeOut("nobodyHome"); 
  SetTimeOut("nobodyHome","callMethodSafe('NobodyHomeMode.activate');", 1*60*60);
  if ($linked_room) {
   callMethodSafe($linked_room.'.onActivity', array('sensor'=>$ot));
  }  
 }

$this->callMethodSafe('logicAction');

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $this->getProperty('status'));