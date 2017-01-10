<?php


 $this->setProperty('status', 1);

 $motion_timeout=20; // seconds timeout
 $ot=$this->object_title;
 setTimeout($ot.'_motion_timer', 'setGlobal("'.$ot.'.status", 0);', $motion_timeout);

 $this->callMethod('logicAction');

 $linked_room=$this->getProperty('linkedRoom');

 if ($linked_room) {
  callMethod($linked_room.'.onActivity', array('sensor'=>$ot));
 }