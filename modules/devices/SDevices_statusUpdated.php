<?php

 $tm=time();
 $this->setProperty('updated', $tm);
 $this->setProperty('updatedText', date('H:i', $tm));

 $this->setProperty('alive', 1);

 $alive_timeout=2*24*60*60; // 2 days alive timeout

 $ot=$this->object_title;

 setTimeout($ot.'_alive_timer', 'setGlobal("'.$ot.'.alive", 0);', $alive_timeout);

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);