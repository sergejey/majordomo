<?php
$ot=$this->object_title;
$linked_room = $this->getProperty('linkedRoom'); 
 
$tm=time();
$this->setProperty('updated', $tm);
$this->callMethod('setUpdatedText');
$this->setProperty('alive', 1);
 
$alive_timeout = (int)$this->getProperty('aliveTimeout') * 60 * 60;
if (!$alive_timeout) {
    $alive_timeout = 2 * 24 * 60 * 60; // 2 days alive timeout by default
}

setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);
 
if ($linked_room && $this->getProperty('isActivity')) {
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

$eco_mode = gg('EconomMode.active');
 
if ($eco_mode)
{
     $eco=(int)$this->getProperty('groupEco');
     $timeout_off=(int)$this->getProperty('autoOffEcoValue');
     if ($eco && $timeout_off) {
         $timeout_off = $timeout_off * 60;
         $status=(int)$this->getProperty('status');
         if ($status!=0)
            setTimeOut($ot.'_checkAutoOff','callMethod("'.$ot.'.checkAutoOff");',$timeout_off); 
         else
            ClearTimeOut($ot.'_checkAutoOff');
     }
}
