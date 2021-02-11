<?php

$ot = $this->object_title;
$linked_room = $this->getProperty('linkedRoom');

$this->setProperty('updated', time());

if ($linked_room && $this->getProperty('isActivity')) {
    if (getGlobal('NobodyHomeMode.active')) {
        callMethodSafe('NobodyHomeMode.deactivate');
    }
    $nobodyhome_timeout = 1 * 60 * 60;
    if (defined('SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT')) {
        $nobodyhome_timeout = SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT * 60;
    }
    if ($nobodyhome_timeout) {
        setTimeOut('nobodyHome', "callMethodSafe('NobodyHomeMode.activate');", $nobodyhome_timeout);
    }
    if ($linked_room) {
        callMethodSafe($linked_room . '.onActivity', array('sensor'=>$ot));
    }
}

$this->callMethod('valueUpdated');
