<?php

$targetStateName = 'default';

$groupObject = $this->getProperty('groupObject');
if ($groupObject != '') {
    $groupState = $this->getProperty('groupState');
    if ($groupState != '') {
        $devices_states = json_decode($groupState, true);
    } else {
        $devices_states = array();
    }
    $devices = json_decode($groupObject, true);
    $total = count($devices);
    for ($i = 0; $i < $total; $i++) {
        $device = $devices[$i];
        $device_state = array();
        if ($device['type'] == 'dimmer') {
            $device_state['level'] = getGlobal($device['object'] . '.level');
            $device_state['status'] = getGlobal($device['object'] . '.status');
        } elseif ($device['type'] == 'thermostat') {
            $device_state['currentTargetValue'] = getGlobal($device['object'] . '.currentTargetValue');
            $device_state['disabled'] = getGlobal($device['object'] . '.disabled');
            $device_state['status'] = getGlobal($device['object'] . '.status');
        } elseif ($device['type'] == 'ac') {
            $device_state['currentTargetValue'] = getGlobal($device['object'] . '.currentTargetValue');
            $device_state['fanSpeed'] = getGlobal($device['object'] . '.fanSpeed');
            $device_state['thermostat'] = getGlobal($device['object'] . '.thermostat');
            $device_state['status'] = getGlobal($device['object'] . '.status');
        } elseif ($device['type'] == 'rgb') {
            $device_state['color'] = getGlobal($device['object'] . '.color');
            $device_state['status'] = getGlobal($device['object'] . '.status');
        } elseif ($device['type'] == 'openable') {
            $device_state['status'] = getGlobal($device['object'] . '.status');
        } else {
            $device_state['status'] = getGlobal($device['object'] . '.status');
        }
        $devices_states[$targetStateName][$device['object']] = $device_state;
    }
    $this->setProperty('groupState', json_encode($devices_states));
}