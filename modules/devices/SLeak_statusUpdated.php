<?php

$ot = $this->object_title;

$tm = time();
$this->callMethodSafe('keepAlive');
$this->setProperty('updated', $tm);
$this->setProperty('updatedText', date('H:i', $tm));

$this->callMethod('keepAlive');

$is_blocked=(int)$this->getProperty('blocked');
if ($is_blocked) {
    return;
}

$alert_timer_title = $ot.'_alert';
if (isset($params['NEW_VALUE']) ) {
    if ($params['NEW_VALUE']) {
        $this->callMethod('alert');
    } else {
        clearTimeOut($alert_timer_title);
        say(LANG_DEVICES_NOTIFY_BACKTONORMAL.': '.$this->description,100);
    }
}

$this->callMethodSafe('logicAction');

include_once(DIR_MODULES . 'devices/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($ot, $this->getProperty('status'));