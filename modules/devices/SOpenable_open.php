<?php

if ($this->getProperty('support_level')) {
    $level_saved = $this->getProperty('levelSaved');
    if (!$level_saved) $level_saved=100;
    $this->callMethod('setLevel',array('value'=>$level_saved));
}

/*
include_once(dirname(__FILE__) . '/devices.class.php');

$dv = new devices();
if ($dv->isHomeBridgeAvailable()) {
    $payload = array();
    $payload['name'] = $this->object_title;
    $open_type = $this->getProperty('openType');
    if ($open_type == 'gates') {
        $payload['service'] = 'GarageDoorOpener';
    } elseif ($open_type == 'door') {
        $payload['service'] = 'Door';
    } elseif ($open_type == 'window') {
        $payload['service'] = 'Window';
    } elseif ($open_type == 'curtains') {
        $payload['service'] = 'WindowCovering';
    } elseif ($open_type == 'shutters') {
        $payload['service'] = 'WindowCovering';
    }
    if ($payload['service']) {
        if ($open_type == 'gates') {
            $payload['characteristic'] = 'TargetDoorState';
            $payload['value'] = "2";
            DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
            sg('HomeBridge.to_set', json_encode($payload));
        } elseif ($open_type == 'door' || $open_type == 'window' || $open_type == 'curtains'  || $open_type == 'shutters') {
            $payload['characteristic'] = 'TargetPosition';
            $payload['value'] = "0";
            DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
            sg('HomeBridge.to_set', json_encode($payload));
        }

    }
}
*/