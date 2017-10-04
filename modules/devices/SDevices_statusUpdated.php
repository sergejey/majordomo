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

if ($this->class_title == 'SMotions' && $params['NEW_VALUE']) {
    $this->callMethodSafe('motionDetected',array('statusUpdated'=>1));
} elseif ($this->class_title == 'SButtons' && $params['NEW_VALUE']) {
    $this->callMethodSafe('pressed',array('statusUpdated'=>1));
}

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);
