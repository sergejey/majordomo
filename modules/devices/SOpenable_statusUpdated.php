<?php

$ot = $this->object_title;
$ncno = $this->getProperty('ncno');

$this->setProperty('updated', time());
$this->callMethodSafe('setUpdatedText');

$this->callMethod('keepAlive');

$description = $this->description;
if (!$description) {
    $description = $ot;
}
if ($this->getProperty('notify_status')) {
    if (isset($params['NEW_VALUE'])) {
        if (!$params['NEW_VALUE'])
            saySafe($description . ' ' . LANG_DEVICES_STATUS_OPEN, 2);
        else
            saySafe($description . ' ' . LANG_DEVICES_STATUS_CLOSED, 2);
    }
}


if ($this->getProperty('notify_nc')) {
    if (isset($params['NEW_VALUE'])) {
        if (!$params['NEW_VALUE']) {
            setTimeout($ot . '_notify_timer_1', "saySafe('" . LANG_REMINDER_INTRO." ". $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 10 * 60);
            setTimeout($ot . '_notify_timer_2', "saySafe('" . LANG_REMINDER_INTRO." ". $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 20 * 60);
            setTimeout($ot . '_notify_timer_3', "saySafe('" . LANG_REMINDER_INTRO." ". $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 30 * 60);
            setTimeout($ot . '_notify_timer_4', "saySafe('" . LANG_REMINDER_INTRO." ". $description . " " . LANG_DEVICES_STATUS_OPEN . "!', 5);", 60 * 60);
        } else {
            clearTimeOut($ot . '_notify_timer_1');
            clearTimeOut($ot . '_notify_timer_2');
            clearTimeOut($ot . '_notify_timer_3');
            clearTimeOut($ot . '_notify_timer_4');
        }
    }
}


$this->callMethodSafe('logicAction');

startMeasure('statusUpdatedLinkedDevices');
include_once(DIR_MODULES . 'devices/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $params['NEW_VALUE']);
endMeasure('statusUpdatedLinkedDevices');
