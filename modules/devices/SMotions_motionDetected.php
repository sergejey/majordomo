<?php

$ot = $this->object_title;

$this->callMethodSafe('keepAlive');

if (!isset($params['statusUpdated'])) {
    setTimeout($ot . '_motion_timer_status', '', 3);
}

if (isset($params['VALUE']) && !$params['VALUE'] && !isset($params['statusUpdated'])) {
    $this->setProperty('status', 0);
    return;
}

$motion_timeout = $this->getProperty('timeout'); // seconds timeout
if (!$motion_timeout) {
    $motion_timeout = 20; // timeout by default
}
$nobodysHome = getGlobal('NobodyHomeMode.active');

if (!isset($params['statusUpdated'])) {
    $this->setProperty('status', 1);
}
setTimeout($ot . '_motion_timer', 'setGlobal("' . $ot . '.status", 0);', $motion_timeout);

if ($nobodysHome && $this->getProperty('ignoreNobodysHome')) {
    return;
}

//$this->callMethod('logicAction');
$nobodyhome_timeout = 1 * 60 * 60;
if (defined('SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT')) {
    $nobodyhome_timeout = SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT * 60;
}

$resetNobodysHome=$this->getProperty('resetNobodysHome');
if ($nobodyhome_timeout && !$resetNobodysHome) {
    setTimeOut('nobodyHome', "callMethodSafe('NobodyHomeMode.activate');", $nobodyhome_timeout);
} elseif ($resetNobodysHome) {
    clearTimeout('nobodyHome');
}

$is_blocked=(int)$this->getProperty('blocked');
if ($is_blocked) {
    return;
}

$linked_room = $this->getProperty('linkedRoom');
if ($linked_room) {
    callMethodSafe($linked_room . '.onActivity', array('sensor' => $ot));
} elseif ($nobodysHome) {
    callMethodSafe('NobodyHomeMode.deactivate', array('sensor' => $ot, 'room' => $linked_room));
}

/*
include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title);
*/
