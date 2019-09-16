<?php

$ot = $this->object_title;

$this->setProperty('updated', time());
$this->setProperty('updatedText', date('H:i', $tm));
if ($this->getProperty('alive') == 0) {
 $this->setProperty('alive', 1);
}

$alive_timeout = (int)$this->getProperty('aliveTimeout')*60*60;
if (!$alive_timeout) {
    $alive_timeout = 2*24*60*60; // 2 days alive timeout by default
}

setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);

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
$dv=new devices();
$dv->checkLinkedDevicesAction($ot, $this->getProperty('status'));