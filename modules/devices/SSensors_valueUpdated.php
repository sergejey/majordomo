<?php

 $this->callMethod('statusUpdated');
 $this->callMethod('logicAction');

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

 @include_once(ROOT.'languages/devices_'.SETTINGS_SITE_LANGUAGE.'.php');
 @include_once(ROOT.'languages/devices_default'.'.php');

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

 if ($linked_room && $this->getProperty('mainSensor')) {
  if ($this->class_title=='STempSensors') {
   sg($linked_room.'.temperature',$value);
  } elseif ($this->class_title=='SHumSensors') {
   sg($linked_room.'.humidity',$value);
  }
 }

include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);