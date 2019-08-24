<?php
$ot = $this->object_title;
$this->setProperty('updated', time());
$this->setProperty('updatedText', date('H:i', $tm));
if ($this->getProperty('alive') == 0) {
 $this->setProperty('alive', 1);
}
$sql=' select *, locations.TITLE TITLELOC from objects, locations, devices where objects.LOCATION_ID=locations.ID and objects.id='.$this->id.' and devices.LINKED_OBJECT=objects.TITLE ';
$loc=SQLSelectOne($sql)['TITLELOC'];
//say ($this->getProperty('notify_status'), 2);

if ($this->getProperty('notify_status')) {
    if (isset($params['NEW_VALUE'])) {
        if ($params['NEW_VALUE']==1) 
{
saySafe(LANG_ALERT.' '.mb_strtolower(LANG_SENSOR_ALARM).' '.mb_strtolower(LANG_DEVICES_LEAK_SENSOR).' '.LANG_IN.' '.mb_strtolower($loc) , 2); 
//say('111', 2); 
}
else 
{saySafe(LANG_DEVICES_LEAK_SENSOR.' '.LANG_IN.' '.mb_strtolower($loc).' ' .LANG_SENSOR_NORMAL, 2); 
}
}
}

if ($this->getProperty('notify_eliminated')) {
    if (isset($params['NEW_VALUE'])) {
        if ($params['NEW_VALUE']==1) 
{
$status=$this->getproperty('status');
while ($status<>0){ 
saySafe(LANG_ALERT.' '.mb_strtolower(LANG_SENSOR_ALARM).' '.mb_strtolower(LANG_DEVICES_LEAK_SENSOR).' '.LANG_IN.' '.mb_strtolower($loc) , 2); 
sleep (60);  
$status=$this->getproperty('status');
}

}
else 
{saySafe(LANG_DEVICES_LEAK_SENSOR.' '.LANG_IN.' '.mb_strtolower($loc).' ' .LANG_SENSOR_NORMAL, 2); }
}
}

$alive_timeout = (int)$this->getProperty('aliveTimeout')*60*60;
if (!$alive_timeout) {
    $alive_timeout = 2*24*60*60; // 2 days alive timeout by default
}
setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);
$this->callMethodSafe('logicAction');
include_once(DIR_MODULES . 'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($ot, $this->getProperty('status'));
