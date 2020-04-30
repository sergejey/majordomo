<?php

$status = $this->getProperty('status');
$level = $this->getProperty('level');
$levelSaved = $this->getProperty('levelSaved');
$linked_room = $this->getProperty('linkedRoom');

if ($this->getProperty('setMaxTurnOn')) {
    $levelSaved = 100;
}

//DebMes("DimmerStatusUpdated: Status $status; Level $level; LevelSaved $levelSaved",'dimming');
if ($status > 0 && !$level && $levelSaved) {
    $this->setProperty('level', $levelSaved);
} else {
    $this->callMethod('logicAction');
    include_once(DIR_MODULES . 'devices/devices.class.php');
    $dv = new devices();
    $dv->checkLinkedDevicesAction($this->object_title, $level);
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
