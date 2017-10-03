<?php

$ot=$this->object_title;

$latestActivity=$this->getProperty('LatestActivity');
$this->setProperty('LatestActivity',time());
$this->setProperty('LatestActivityTime',date('H:i'));

if (!$this->getProperty('SomebodyHere')) {
    $this->setProperty('SomebodyHere',1);
}

if ($this->getProperty("IdleDelay")) {
    $activity_timeout=(int)$this->getProperty("IdleDelay");
} else {
    $activity_timeout=10*60;
}
clearTimeOut($ot."_activity_timeout");
setTimeOut($ot."_activity_timeout","callMethod('".$ot.".onIdle');",$activity_timeout);

$this->callMethodSafe("updateActivityStatus");

if (getGlobal('NobodyHomeMode.active')) {
    callMethodSafe('NobodyHomeMode.deactivate',array('sensor'=>$params['sensor'],'room'=>$ot));
}