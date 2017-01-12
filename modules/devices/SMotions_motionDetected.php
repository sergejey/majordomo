<?php


 $this->setProperty('status', 1);

 $motion_timeout=20; // seconds timeout
 $ot=$this->object_title;
 setTimeout($ot.'_motion_timer', 'setGlobal("'.$ot.'.status", 0);', $motion_timeout);

 $this->callMethod('logicAction');

 $linked_room=$this->getProperty('linkedRoom');

 if (getGlobal('NobodyHomeMode.active')) {
  callMethod('NobodyHomeMode.deactivate');
 }
 ClearTimeOut("nobodyHome"); 
 SetTimeOut("nobodyHome","callMethod('NobodyHomeMode.activate');", 1*60*60);

 if ($linked_room) {
  callMethod($linked_room.'.onActivity', array('sensor'=>$ot));
 }