<?php

$ot = $this->object_title;
$description = $this->description;
if (!$description) {
    $description = $ot;
}

$value = (float)$this->getProperty('value');

$alert_timer_title = $ot.'_alert';

//say(LANG_DEVICES_NOTIFY_OUTOFRANGE . ' (' . $description . ' ' . $value . ')', 2);
say($value.' '.$description.' - '.LANG_DEVICES_NOTIFY_OUTOFRANGE, 2);

if ($this->getProperty('notify_eliminated')) {
    setTimeOut($alert_timer_title,'cm("'.$ot.'.alert");',60);
}