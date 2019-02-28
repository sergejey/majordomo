<?php

 //$this->callMethod('logicAction');

 $ot=$this->object_title;
 $description = $this->description;
 if (!$description) {
  $description = $ot;
 }
 $linked_room=$this->getProperty('linkedRoom');

 $value=(float)$this->getProperty('value');
 $minValue=(float)$this->getProperty('minValue');
 $maxValue=(float)$this->getProperty('maxValue');
 $is_normal=(int)$this->getProperty('normalValue');
 $directionTimeout=(int)$this->getProperty('directionTimeout');
 if (!$directionTimeout) {
  $directionTimeout=1*60*60;
 }

 if ($maxValue==0 && $minValue==0 && !$is_normal) {
  $this->setProperty('normalValue', 1);
 } elseif (($value>$maxValue || $value<$minValue) && $is_normal) {
  $this->setProperty('normalValue', 0);
  if ($this->getProperty('notify')) {
   //out of range notify
   say(LANG_DEVICES_NOTIFY_OUTOFRANGE. ' ('.$description.' '.$value.')', 2);
  }
 } elseif (($value<=$maxValue && $value>=$minValue) && !$is_normal) {
  $this->setProperty('normalValue', 1);
  if ($this->getProperty('notify')) {
   //back to normal notify
   say(LANG_DEVICES_NOTIFY_BACKTONORMAL. ' ('.$description.' '.$value.')', 2);
  }
 }


 $data1 = getHistoryValue($this->object_title.".value", time()-$directionTimeout);
 $direction = 0;
 if ($data1>$value) {
  $direction=-1;
 } elseif ($data1<$value) {
  $direction=1;
 }
 $currentDirection = $this->getProperty('direction');
 if ($currentDirection != $direction) {
  $this->setProperty('direction',$direction);
 }

 if ($linked_room && $this->getProperty('mainSensor')) {
  if ($this->class_title=='STempSensors') {
   sg($linked_room.'.temperature',$value);
  } elseif ($this->class_title=='SHumSensors') {
   sg($linked_room.'.humidity',$value);
  }
 }

$this->callMethod('statusUpdated');
/*
include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);
*/
