<?php

$ot = $this->object_title;
$timeOutTitle = $ot . '_checkwindows';
clearTimeOut($timeOutTitle);

$windowIsOpen = $this->getProperty('windowIsOpen');
$openableSensors = $this->getProperty('openableSensors');

if ($openableSensors != '') {
    $groupObject = $this->getProperty('openableSensors');
    $devices = json_decode($groupObject, true);
    $total = count($devices);
    $found_open_device = false;
    for ($i = 0; $i < $total; $i++) {
        $device = $devices[$i];
        $device_object = $device['object'];
        if ($device['type'] == 'openable') {
            $ncno = getGlobal($device_object . '.ncno');
        } elseif ($device['type'] == 'openclose') {
            $ncno = '';
        }
        if ($ncno == 'no') {
            $device_is_open = getGlobal($device_object . '.status');
        } else {
            $device_is_open = !getGlobal($device_object . '.status');
        }
        if ($device_is_open) {
            $found_open_device = true;
            break;
        }
    }
    setTimeOut($timeOutTitle, 'callMethod("' . $ot . '.checkWindows");', 1 * 15);
    if ($found_open_device) {
        if (!$windowIsOpen) {
            $this->setProperty('windowIsOpen', 1);
            $this->callMethod('valueUpdated');
        }
    } else {
        if ($windowIsOpen) {
            $this->setProperty('windowIsOpen', 0);
            $this->callMethod('valueUpdated');
        }
    }
} elseif ($windowIsOpen) {
    $this->setProperty('windowIsOpen', 0);
}