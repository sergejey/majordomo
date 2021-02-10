<?php

$ot = $this->object_title;
$ncno = $this->getProperty('ncno');

$this->setProperty('updated', time());

$this->callMethod('keepAlive');

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
        if (!$params['NEW_VALUE']) {
            $msg = $this->getProperty('notify_msg_opening');
            if (!$msg) $msg = $description . ' ' . LANG_DEVICES_STATUS_OPEN;
            saySafe($msg, 2);
        } else {
            $msg = $this->getProperty('notify_msg_closing');
            if (!$msg) $msg = $description . ' ' . LANG_DEVICES_STATUS_CLOSED;
            saySafe($msg, 2);
        }
    }
}


if ($this->getProperty('notify_nc')) {
    if (isset($params['NEW_VALUE'])) {
        if (!$params['NEW_VALUE']) {
            $msg = $this->getProperty('notify_msg_reminder');
            if (!$msg) $msg = LANG_REMINDER_INTRO." ". $description. " " . LANG_DEVICES_STATUS_OPEN . "!";
            setTimeout($ot . '_notify_timer_1', "saySafe('" . $msg ."', 5);", 10 * 60);
            setTimeout($ot . '_notify_timer_2', "saySafe('" . $msg ."', 5);", 20 * 60);
            setTimeout($ot . '_notify_timer_3', "saySafe('" . $msg ."', 5);", 30 * 60);
            setTimeout($ot . '_notify_timer_4', "saySafe('" . $msg ."', 5);", 60 * 60);
            setTimeout($ot . '_notify_timer_5', "saySafe('" . $msg ."', 5);", 120 * 60);
        } else {
            clearTimeOut($ot . '_notify_timer_1');
            clearTimeOut($ot . '_notify_timer_2');
            clearTimeOut($ot . '_notify_timer_3');
            clearTimeOut($ot . '_notify_timer_4');
            clearTimeOut($ot . '_notify_timer_5');
        }
    }
}


DebMes("Calling logicAction for $ot",'openable');
$this->callMethodSafe('logicAction');

startMeasure('statusUpdatedLinkedDevices');
include_once(dirname(__FILE__) . '/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $params['NEW_VALUE']);
endMeasure('statusUpdatedLinkedDevices');
