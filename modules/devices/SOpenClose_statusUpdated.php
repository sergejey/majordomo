<?php

startMeasure('statusUpdated');
$ot = $this->object_title;
$ncno = $this->getProperty('ncno');

$this->setProperty('updated', time());
$this->callMethodSafe('setUpdatedText');
if ($this->getProperty('alive') == 0) {
 $this->setProperty('alive', 1);
}
$alive_timeout = (int)$this->getProperty('aliveTimeout') * 60 * 60;
if (!$alive_timeout) {
    $alive_timeout = 2 * 24 * 60 * 60; // 2 days alive timeout by default
}

setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);

$is_blocked=(int)$this->getProperty('blocked');
if ($is_blocked) {
    return;
}

if ($this->getProperty('isActivity')) {
    $linked_room = $this->getProperty('linkedRoom');
    if (getGlobal('NobodyHomeMode.active')) {
        callMethodSafe('NobodyHomeMode.deactivate', array('sensor' => $ot, 'room' => $linked_room));
    }
    $nobodyhome_timeout = 1 * 60 * 60;
    if (defined('SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT')) {
        $nobodyhome_timeout = SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT * 60;
    }
    if ($nobodyhome_timeout) {
        setTimeOut('nobodyHome', "callMethodSafe('NobodyHomeMode.activate');", $nobodyhome_timeout);
    }
    if ($linked_room) {
        callMethodSafe($linked_room . '.onActivity', array('sensor' => $ot));
    }
}

$description = $this->description;
if (!$description) {
    $description = $ot;
}
if ($this->getProperty('notify_status')) {
    if (isset($params['NEW_VALUE'])) {
        if (($params['NEW_VALUE'] && $ncno == 'no') || (!$params['NEW_VALUE'] && $ncno == 'nc'))
            saySafe($description . ' ' . LANG_DEVICES_STATUS_OPEN, 2);
        else
            saySafe($description . ' ' . LANG_DEVICES_STATUS_CLOSED, 2);
    }
}
if ($this->getProperty('notify_nc')) {
    if (isset($params['NEW_VALUE'])) {
        if (($params['NEW_VALUE'] && $ncno == 'no') || (!$params['NEW_VALUE'] && $ncno == 'nc')) {
            setTimeout($ot . '_notify_timer_1', "saySafe('" . $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 5 * 60);
            setTimeout($ot . '_notify_timer_2', "saySafe('" . $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 10 * 60);
            setTimeout($ot . '_notify_timer_3', "saySafe('" . $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 15 * 60);
        } else {
            clearTimeOut($ot . '_notify_timer_1');
            clearTimeOut($ot . '_notify_timer_2');
            clearTimeOut($ot . '_notify_timer_3');
        }
    }
}


$this->callMethodSafe('logicAction');

startMeasure('statusUpdatedLinkedDevices');
include_once(DIR_MODULES . 'devices/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $params['NEW_VALUE']);
endMeasure('statusUpdatedLinkedDevices');


endMeasure('statusUpdated');