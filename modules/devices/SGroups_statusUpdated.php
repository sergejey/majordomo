<?php

$this->setProperty('updated', time());
$status = $params['NEW_VALUE'];

$group_name = $this->getProperty('groupName');
$objects = getObjectsByProperty('group' . $group_name, 1);
foreach($objects as $object_title) {
  if (getGlobal($object_title . '.status') != $status) {
    usleep(50000);
    if ($status) {
      callMethodSafe($object_title . '.turnOn', array('source' => $params['ORIGINAL_OBJECT_TITLE']));
    } else {
      callMethodSafe($object_title . '.turnOff', array('source' => $params['ORIGINAL_OBJECT_TITLE']));
    }
    //sleep(1);
  }
}