<?php

$this->setProperty('updated', time());
$status = $params['NEW_VALUE'];


$delay = (float)$this->getProperty('delay');
if ($delay > 1) {
    $delay_ms = 0;
} elseif ($delay > 0) {
    $delay_ms = 1000000 * $delay;
} else {
    $delay_ms = 50000;
}


$group_name = $this->getProperty('groupName');
$objects = getObjectsByProperty('group' . $group_name, 1);

$firstOne = true;
foreach ($objects as $object_title) {
    if (!$firstOne) {
        if ($delay_ms > 0) {
            usleep($delay_ms);
        } else {
            sleep($delay);
        }
    }
    $firstOne = false;
    if ($status) {
        callMethodSafe($object_title . '.turnOn', array('source' => $params['ORIGINAL_OBJECT_TITLE']));
    } else {
        callMethodSafe($object_title . '.turnOff', array('source' => $params['ORIGINAL_OBJECT_TITLE']));
    }
}