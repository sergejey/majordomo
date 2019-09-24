<?php

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
