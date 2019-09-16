<?php

$ot = $this->object_title;
$linked_room = $this->getProperty('linkedRoom');

if ($this->class_title != 'SMotions' || $params['NEW_VALUE']) {
    $this->setProperty('updated', time());
    $this->callMethodSafe('setUpdatedText');
}
if ($this->getProperty('alive') == 0) {
    $this->setProperty('alive', 1);
}


$alive_timeout = (int)$this->getProperty('aliveTimeout') * 60 * 60;
if (!$alive_timeout) {
    $alive_timeout = 2 * 24 * 60 * 60; // 2 days alive timeout by default
}

setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);

//$need_call_logic_action = 1;

$is_blocked = (int)$this->getProperty('blocked');
if ($is_blocked) {
    return;
}

if ($this->class_title == 'SMotions' && $params['NEW_VALUE'] && !timeOutExists($ot . '_motion_timer_status')) {
    $this->callMethodSafe('motionDetected', array('statusUpdated' => 1));
} elseif ($this->class_title == 'SButtons' && $params['NEW_VALUE'] && !timeOutExists($ot . '_pressed_status')) {
    $this->callMethodSafe('pressed', array('statusUpdated' => 1));
}

if ($params['NEW_VALUE'] && $linked_room && $this->getProperty('isActivity')) {
    $nobodyhome_timeout = 1 * 60 * 60;
    if (defined('SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT')) {
        $nobodyhome_timeout = SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT * 60;
    }
    if ($nobodyhome_timeout) {
        setTimeOut("nobodyHome", "callMethodSafe('NobodyHomeMode.activate');", $nobodyhome_timeout);
    }
    if ($linked_room) {
        callMethodSafe($linked_room . '.onActivity', array('sensor' => $ot));
    } else {
        if (getGlobal('NobodyHomeMode.active')) {
            callMethodSafe('NobodyHomeMode.deactivate', array('sensor' => $ot, 'room' => $linked_room));
        }
    }
}

$this->callMethod('logicAction');
include_once(DIR_MODULES . 'devices/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $params['NEW_VALUE']);

