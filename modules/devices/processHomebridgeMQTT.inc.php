<?php
/*
    $params['PROPERTY']=$property;
    $params['NEW_VALUE']=(string)$value;
*/

$debug_sync = 0;

if ($debug_sync) {
    DebMes("MQTT ".$params['PROPERTY'].": " . $params['NEW_VALUE'], 'homebridge');
}

$data = json_decode($params['NEW_VALUE'], true);

if ($data['name']) {
    $device = SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '" . DBSafe($data['name']) . "'");
}

if ($params['PROPERTY'] == 'from_response' && gg('HomeBridge.mode') == 'list') {
    $devices = array();
    foreach ($data as $k => $v) {
        if (is_array($v['services'])) {
            $devices[] = $k;
        }
    }
    $total = count($devices);
    if ($total > 0) {
        if ($debug_sync) {
            DebMes("Got devices list", 'homebridge');
        }
        sg('HomeBridge.mode', '');
        $to_remove = array();
        for ($i = 0; $i < $total; $i++) {
            $device = SQLSelectOne("SELECT ID FROM devices WHERE LINKED_OBJECT LIKE '" . DBSafe($devices[$i]) . "'");
            if (!$device['ID']) {
                $to_remove[] = $devices[$i];
            }
        }
        $total = count($to_remove);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                $payload = array();
                $payload['name'] = $to_remove[$i];
                if ($debug_sync) {
                    DebMes("Homebridge: removing unknown device " . $payload['name'], 'homebridge');
                }
                sg('HomeBridge.to_remove', json_encode($payload));
            }
        } else {
            if ($debug_sync) {
                DebMes("Nothing to remove", 'homebridge');
            }
        }
    }
}

// reply to status request from HomeKit
if ($params['PROPERTY'] == 'from_get' && $device['ID']) {
    $payload = array();
    $payload['name'] = $device['LINKED_OBJECT'];
    $payload['service_name'] = processTitle($device['TITLE']);

    switch ($device['TYPE']) {
        case 'relay':
            $load_type = gg($device['LINKED_OBJECT'] . '.loadType');
            if ($load_type == 'light') $payload['service'] = 'Lightbulb';
            elseif ($load_type == 'vent') $payload['service'] = 'Fan';
            elseif ($load_type == 'switch') $payload['service'] = 'Switch';
            else                          $payload['service'] = 'Outlet';

            if ($data['characteristic'] == 'On') {
                $payload['characteristic'] = 'On';
                if (gg($device['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = true;
                } else {
                    $payload['value'] = false;
                }
            }
            break;
        case 'openable':
            $open_type = gg($device['LINKED_OBJECT'] . '.openType');
            if ($open_type == 'gates') {
                $payload['service'] = 'GarageDoorOpener';
                if ($data['characteristic']=='CurrentDoorState') {
                    $payload['value'] = (int)gg($device['LINKED_OBJECT'] . '.status');
                }
            } elseif ($open_type == 'door') {
                $payload['service'] = 'Door';
                if ($data['characteristic']=='CurrentPosition') {
                    $currentStatus = (int)gg($device['LINKED_OBJECT'] . '.status');
                    if ($currentStatus) {
                        $payload['value'] = 100;
                    } else {
                        $payload['value'] = 0;
                    }
                }
            } elseif ($open_type == 'window') {
                $payload['service'] = 'Window';
                if ($data['characteristic']=='CurrentPosition') {
                    $currentStatus = (int)gg($device['LINKED_OBJECT'] . '.status');
                    if ($currentStatus) {
                        $payload['value'] = 100;
                    } else {
                        $payload['value'] = 0;
                    }
                }
            } elseif ($open_type == 'curtains' || $open_type == 'shutters') {
                $payload['service'] = 'WindowCovering';
                if ($data['characteristic']=='CurrentPosition') {
                    $currentStatus = (int)gg($device['LINKED_OBJECT'] . '.status');
                    if ($currentStatus) {
                        $payload['value'] = 100;
                    } else {
                        $payload['value'] = 0;
                    }
                }
            }
            break;
        case 'sensor_temp':
            $payload['service'] = 'TemperatureSensor';
            if ($data['characteristic'] == 'CurrentTemperature') {
                $payload['characteristic'] = 'CurrentTemperature';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.value');
            }
            if ($data['characteristic'] == 'BatteryLevel') {
                $payload['value'] = 90;
            }
            break;
        case 'sensor_humidity':
            $payload['service'] = 'HumiditySensor';
            if ($data['characteristic'] == 'CurrentRelativeHumidity') {
                $payload['characteristic'] = 'CurrentRelativeHumidity';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.value');
            }
            if ($data['characteristic'] == 'BatteryLevel') {
                $payload['value'] = 90;
            }
            break;
        case 'motion':
            $payload['service'] = 'MotionSensor';
            if ($data['characteristic'] == 'MotionDetected') {
                $payload['characteristic'] = 'MotionDetected';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.status');
            }
            break;
        case 'sensor_light':
            $payload['service'] = 'LightSensor';
            if ($data['characteristic'] == 'CurrentAmbientLightLevel') {
                $payload['characteristic'] = 'CurrentAmbientLightLevel';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.value');
            }
            break;
        case 'openclose':
            $payload['service'] = 'ContactSensor';
            if ($data['characteristic'] == 'ContactSensorState') {
                $payload['characteristic'] = 'ContactSensorState';
                $nc = gg($device['LINKED_OBJECT'] . '.ncno') == 'nc';
                $payload['value'] = $nc ? 1 - gg($device['LINKED_OBJECT'] . '.status') : gg($device['LINKED_OBJECT'] . '.status');
            }
            break;
        case 'dimmer':
            $payload['service'] = 'Lightbulb';
            if ($data['characteristic'] == 'On') {
                $payload['characteristic'] = 'On';
                if (gg($device['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = true;
                } else {
                    $payload['value'] = false;
                }
            } elseif ($data['characteristic'] == 'Brightness') {
                $payload['characteristic'] = 'Brightness';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.level');
            }
            break;
        case 'rgb':
            $payload['service'] = 'Lightbulb';
            if ($data['characteristic'] == 'On') {
                $payload['characteristic'] = 'On';
                if (gg($device['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = true;
                } else {
                    $payload['value'] = false;
                }
            } elseif ($data['characteristic'] == 'Hue') {
                $payload['characteristic'] = 'Hue';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.hue');
            } elseif ($data['characteristic'] == 'Saturation') {
                $payload['characteristic'] = 'Saturation';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.saturation');
            } elseif ($data['characteristic'] == 'Brightness') {
                $payload['characteristic'] = 'Brightness';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.brightness');
            }
            break;
        case 'ledlamp':
            $payload['service'] = 'Lightbulb';
            if ($data['characteristic'] == 'On') {
                $payload['characteristic'] = 'On';
                if (gg($device['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = true;
                } else {
                    $payload['value'] = false;
                }
            } elseif ($data['characteristic'] == 'Brightness') {
                $payload['characteristic'] = 'Brightness';
                $payload['value'] = gg($device['LINKED_OBJECT'] . '.brightness');
            }
            break;

        /*
        case 'sensor_battery':
           $payload['service'] = 'BatteryService';
           if ($data['characteristic'] == 'BatteryLevel') {
              $payload['value'] = gg($device['LINKED_OBJECT'].'.value');
           }
           if ($data['characteristic'] == 'StatusLowBattery') {
              $payload['value'] = gg($device['LINKED_OBJECT'].'.normalValue') ? 0 : 1;
           }
           break;
        */
    }
    if (isset($payload['value'])) {
        sg('HomeBridge.to_set', json_encode($payload));
    }
}

// set status from HomeKit
if ($params['PROPERTY'] == 'from_set' && $device['ID']) {
    if ($debug_sync) {
        DebMes($device['TITLE'] . ' set ' . $data['characteristic'] . ' to ' . $data['value'], 'homebridge');
    }
    if (in_array($device['TYPE'], array('relay'))) {
        if ($data['characteristic'] == 'On') {
            if ($data['value']) {
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOn');
            } else {
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
    }
    if (in_array($device['TYPE'], array('openable'))) {
        if ($data['characteristic'] == 'TargetPosition') {
            $currentStatus = gg($device['LINKED_OBJECT'] . '.status');
            if ($data['value']>0 && $currentStatus) {
                callMethodSafe($device['LINKED_OBJECT'] . '.open');
            } elseif (!$currentStatus) {
                callMethodSafe($device['LINKED_OBJECT'] . '.close');
            }
        }
        if ($data['characteristic'] == 'TargetDoorState') {
            if ($data['value'] == 1) {
                callMethodSafe($device['LINKED_OBJECT'] . '.close');
            } elseif ($data['value'] == 0) {
                callMethodSafe($device['LINKED_OBJECT'] . '.open');
            }
        }
    }
    if (in_array($device['TYPE'], array('dimmer'))) {
        if ($data['characteristic'] == 'On') {
            if ($data['value']) {
                if (gg($device['LINKED_OBJECT'] . '.status') == 0) callMethodSafe($device['LINKED_OBJECT'] . '.turnOn');
            } else {
                if (gg($device['LINKED_OBJECT'] . '.status') == 1) callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
        if ($data['characteristic'] == 'Brightness') {
            if ($data['value']) {
                sg($device['LINKED_OBJECT'] . '.level', $data['value']);
            } else {
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
    }
    if (in_array($device['TYPE'], array('rgb'))) {
        if ($data['characteristic'] == 'On') {
            if ($data['value']) {
                if (gg($device['LINKED_OBJECT'] . '.status') == 0) callMethodSafe($device['LINKED_OBJECT'] . '.turnOn');
            } else {
                if (gg($device['LINKED_OBJECT'] . '.status') == 1) callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
        $colorChange = false;
        if ($data['characteristic'] == 'Brightness') {
            if ($data['value']) {
                sg($device['LINKED_OBJECT'] . '.brightness', $data['value']);
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOn');
            } else {
                sg($device['LINKED_OBJECT'] . '.brightness', 0);
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
        if ($data['characteristic'] == 'Hue') {
            sg($device['LINKED_OBJECT'] . '.hue', $data['value']);
            $colorChange = true;
        }
        if ($data['characteristic'] == 'Saturation') {
            sg($device['LINKED_OBJECT'] . '.saturation', $data['value']);
            $colorChange = true;
        }
        if ($colorChange) {
            $h = gg($device['LINKED_OBJECT'] . '.hue');
            $s = gg($device['LINKED_OBJECT'] . '.saturation');
            $b = gg($device['LINKED_OBJECT'] . '.lightness');
            $color = hsvToHex($h, $s, $b);
            sg($device['LINKED_OBJECT'] . '.color', $color);
            if ($color != '000000') {
                $this->setProperty('colorSaved', $color);
            }
        }
    }
    if (in_array($device['TYPE'], array('ledlamp'))) {
        if ($data['characteristic'] == 'On') {
            if ($data['value']) {
                if (gg($device['LINKED_OBJECT'] . '.status') == 0) callMethodSafe($device['LINKED_OBJECT'] . '.turnOn');
            } else {
                if (gg($device['LINKED_OBJECT'] . '.status') == 1) callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
        $colorChange = false;
        if ($data['characteristic'] == 'Brightness') {
            if ($data['value']) {
                sg($device['LINKED_OBJECT'] . '.brightness', $data['value']);
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOn');
            } else {
                sg($device['LINKED_OBJECT'] . '.brightness', 0);
                callMethodSafe($device['LINKED_OBJECT'] . '.turnOff');
            }
        }
    }

    if ($device['TYPE'] == 'button') {
        if ($data['characteristic'] == 'ProgrammableSwitchEvent' || $data['characteristic'] == 'On') {
            callMethodSafe($device['LINKED_OBJECT'] . '.pressed');
            if ($data['characteristic'] == 'On') {
                $payload = array();
                $payload['name'] = $device['LINKED_OBJECT'];
                $payload['service_name'] = processTitle($device['TITLE']);
                //$payload['service'] = 'Switch';
                $payload['characteristic'] = 'On';
                $payload['value'] = false;
                sg('HomeBridge.to_set', json_encode($payload));
            }
        }
    }
    if ($device['TYPE'] == 'thermostat') {
        if ($data['characteristic'] == 'TargetTemperature') {
            sg($device['LINKED_OBJECT'] . '.currentTargetValue', $data['value']);
            if (gg($device['LINKED_OBJECT'] . '.status')) {
                sg($device['LINKED_OBJECT'] . '.normalTargetValue', $data['value']);
            } else {
                sg($device['LINKED_OBJECT'] . '.ecoTargetValue', $data['value']);
            }
        }
        if ($data['characteristic'] == 'TargetHeatingCoolingState') {
            if ($data['value'] == 0) { // off
                sg($device['LINKED_OBJECT'] . '.disabled', 1);
            } elseif ($data['value'] == 1) { // heat
                sg($device['LINKED_OBJECT'] . '.disabled', 0);
                sg($device['LINKED_OBJECT'] . '.status', 1);
            } elseif ($data['value'] == 2) { // cool
                sg($device['LINKED_OBJECT'] . '.disabled', 0);
                sg($device['LINKED_OBJECT'] . '.status', 0);
            } elseif ($data['value'] == 3) { // auto
                sg($device['LINKED_OBJECT'] . '.disabled', 0);
            }
        }
    }
}

/*
HomeBridge.to_add
{"name": "flex_lamp", "service_name": "light", "service": "Switch"}

HomeBridge.from_set
{"name":"flex_lamp","service_name":"light","characteristic":"On","value":false}

HomeBridge.from_get
{"name":"flex_lamp","service_name":"light","characteristic":"On"}

HomeBridge.to_set
{"name":"flex_lamp","service_name":"light","characteristic":"On","value":false}

 */
