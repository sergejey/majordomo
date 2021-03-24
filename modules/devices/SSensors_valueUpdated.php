<?php

$ot = $this->object_title;
$description = $this->description;
if (!$description) {
  $description = $ot;
}

$directionTimeout = (int)$this->getProperty('directionTimeout');
if (!$directionTimeout) {
  $directionTimeout = 1*60*60;
}

$value = (float)$this->getProperty('value');
$minValue = $this->getProperty('minValue');
if (is_numeric($minValue)) {
  $minValue = (float)$minValue;
}
$maxValue = $this->getProperty('maxValue');
if (is_numeric($maxValue)) {
  $maxValue = (float)$maxValue;
}
$is_normal = (int)$this->getProperty('normalValue');

$data1 = getHistoryValue($this->object_title . '.value', time() - $directionTimeout);
$direction = 0;
if ($data1 > $value) {
  $direction = -1;
} elseif ($data1 < $value) {
  $direction = 1;
}
if ($this->getProperty('direction') != $direction) {
  $this->setProperty('direction', $direction);
}

$is_blocked=(int)$this->getProperty('blocked');
if ($is_blocked) {
  return;
}

$alert_timer_title = $ot.'_alert';
if (!is_float($maxValue) && !is_float($minValue) && !$is_normal) {
  $this->setProperty('normalValue', 1);
} elseif (((is_float($maxValue) && ($value > $maxValue)) || (is_float($minValue) && ($value < $minValue))) && $is_normal) {
  $this->setProperty('normalValue', 0);
  if ($this->getProperty('notify')) {
    //out of range notify
    $this->callMethod('alert');
  }
} elseif (((($value <= $maxValue) || !is_float($maxValue)) && (($value >= $minValue) || !is_float($minValue))) && !$is_normal) {
  $this->setProperty('normalValue', 1);
  clearTimeOut($alert_timer_title);
  if ($this->getProperty('notify')) {
    //back to normal notify
    say(LANG_DEVICES_NOTIFY_BACKTONORMAL . ' (' . $description . ' ' . $value . ')', 2);
  }
}

$linked_room = $this->getProperty('linkedRoom');
if ($linked_room && $this->getProperty('mainSensor')) {
  if ($this->class_title == 'STempSensors') {
    setGlobal($linked_room . '.temperature', $value);
  } elseif ($this->class_title == 'SHumSensors') {
    setGlobal($linked_room . '.humidity', $value);
  } elseif ($this->class_title == 'SLightSensors') {
    setGlobal($linked_room . '.light', $value);
  }
}

$this->callMethodSafe('keepAlive');
$this->callMethod('statusUpdated');
/*
include_once(dirname(__FILE__).'/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $value);
*/
