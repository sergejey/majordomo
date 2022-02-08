<?php

$ot = $this->object_title;
$alert_timer_title = $ot . '_alert';

$message = $this->getProperty('notify_msg_reminder');

if ($message) {
    say($message, 100);
} else {
    $description = $this->description;
    if (!$description) {
        $description = $ot;
    }
    $location_title = processTitle($this->getProperty('location_title'));
    if ($location_title) {
        $description .= ' ' . LANG_LOCATED_IN_ROOM . ' ' . $location_title;
    }

    say(LANG_DEVICES_SENSOR_ALERT . ': ' . $description, 100);
}

clearTimeOut($alert_timer_title);
if ($this->getProperty('notify_eliminated')) {
    setTimeOut($alert_timer_title, 'cm("' . $ot . '.alert");', 60);
}
