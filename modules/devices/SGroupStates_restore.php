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
        if (!isset($devices_states[$targetStateName][$device['object']])) continue;
        $device_state = $devices_states[$targetStateName][$device['object']];
        $processed = false;

        if ($device['type'] == 'dimmer') {
            $processed = true;
            if (isset($device_state['status'])) {
                if ($device_state['status']) {
                    callMethod($device['object'] . '.turnOn');
                } else {
                    callMethod($device['object'] . '.turnOff');
                }
            }
            if (isset($device_state['level']) && getGlobal($device['object'] . '.status')) {
                callMethod($device['object'] . '.setLevel', array('value' => $device_state['level']));
            }
        } elseif ($device['type'] == 'thermostat') {
            if (isset($device_state['disabled'])) {
                if ($device_state['disabled']) {
                    callMethod($device['object'] . '.disable');
                } else {
                    callMethod($device['object'] . '.enable');
                }
            }
            if (isset($device_state['status']) && !getGlobal($device['object'].'.disabled')) {
                if ($device_state['status']) {
                    callMethod($device['object'] . '.turnOn');
                } else {
                    callMethod($device['object'] . '.turnOff');
                }
            }
            if (isset($device_state['currentTargetValue']) && !getGlobal($device['object'].'.disabled')) {
                callMethod($device['object'] . '.setTargetTemperature', array('value' => $device_state['currentTargetValue']));
            }
        } elseif ($device['type'] == 'ac') {
            if (isset($device_state['status'])) {
                if ($device_state['status']) {
                    callMethod($device['object'] . '.turnOn');
                } else {
                    callMethod($device['object'] . '.turnOff');
                }
            }
            if (isset($device_state['currentTargetValue'])) {
                callMethod($device['object'] . '.setTargetTemperature', array('value' => $device_state['currentTargetValue']));
            }
            if (isset($device_state['fanSpeed'])) {
                callMethod($device['object'] . '.setFanSpeedMode', array('value' => $device_state['fanSpeed']));
            }
            if (isset($device_state['thermostat'])) {
                callMethod($device['object'] . '.setThermostatMode', array('value' => $device_state['thermostat']));
            }
        } elseif ($device['type'] == 'rgb') {
            if (isset($device_state['status'])) {
                if ($device_state['status']) {
                    callMethod($device['object'] . '.turnOn');
                } else {
                    callMethod($device['object'] . '.turnOff');
                }
            }
            if (isset($device_state['color']) && getGlobal($device['object'] . '.status')) {
                callMethod($device['object'] . '.setColor', array('value' => $device_state['color']));
            }
        } elseif ($device['type'] == 'openable') {
            if (isset($device_state['status'])) {
                if ($device_state['status'] == '1') {
                    callMethod($device['object'] . '.close');
                } else {
                    callMethod($device['object'] . '.open');
                }
            }
        }

        if (!$processed && isset($device_state['status'])) {
            if ($device_state['status']) {
                callMethod($device['object'] . '.turnOn');
            } else {
                callMethod($device['object'] . '.turnOff');
            }
        }
    }
}