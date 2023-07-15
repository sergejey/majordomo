<?php

$payload = array();
$payload['name'] = $device1['LINKED_OBJECT'];
$payload['service_name'] = $device1['TITLE'];

$payload2 = array();
$payload2['name'] = $device1['LINKED_OBJECT'];
$payload2['service_name'] = $device1['TITLE'];

//DebMes("Homebridge Update ".$device1['LINKED_OBJECT']." (".$device1['TYPE']."): ".gg($device1['LINKED_OBJECT'] . '.status')." / ".gg($device1['LINKED_OBJECT'] . '.value'),'homebridge');

switch ($device1['TYPE']) {
    case 'relay':
        $load_type = gg($device1['LINKED_OBJECT'] . '.loadType');
        if ($load_type == 'light') $payload['service'] = 'Lightbulb';
        elseif ($load_type == 'vent') $payload['service'] = 'Fan';
        elseif ($load_type == 'switch') $payload['service'] = 'Switch';
        else                          $payload['service'] = 'Outlet';
        $payload['characteristic'] = 'On';
        if (gg($device1['LINKED_OBJECT'] . '.status')) {
            $payload['value'] = true;
        } else {
            $payload['value'] = false;
        }
        break;
    case 'sensor_temp':
        $payload['service'] = 'TemperatureSensor';
        $payload['characteristic'] = 'CurrentTemperature';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.value');
        break;
    case 'sensor_co2':
        $payload['service'] = 'CarbonDioxideSensor';
        $payload['characteristic'] = 'CarbonDioxideLevel';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.value');

        $max_level = gg($device1['LINKED_OBJECT'] . '.maxValue');
        if (!$max_level) {
            $max_level = 1200;
        }
        $payload2['service'] = 'CarbonDioxideSensor';
        $payload2['characteristic'] = 'CarbonDioxideDetected';
        if ($payload['value'] >= $max_level) {
            $payload2['value'] = "1";
        } else {
            $payload2['value'] = "0";
        }
        break;
    case 'sensor_humidity':
        $payload['service'] = 'HumiditySensor';
        $payload['characteristic'] = 'CurrentRelativeHumidity';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.value');
        break;
    case 'motion':
        $payload['service'] = 'MotionSensor';
        $payload['characteristic'] = 'MotionDetected';
        if (gg($device1['LINKED_OBJECT'] . '.status')) {
            $payload['value'] = true;
        } else {
            $payload['value'] = false;
        }
        break;
    case 'smoke':
        $payload['service'] = 'SmokeSensor';
        $payload['characteristic'] = 'SmokeDetected';
        if (gg($device1['LINKED_OBJECT'] . '.status')) {
            $payload['value'] = true;
        } else {
            $payload['value'] = false;
        }
        break;
    case 'leak':
        $payload['service'] = 'LeakSensor';
        $payload['characteristic'] = 'LeakDetected';
        if (gg($device1['LINKED_OBJECT'] . '.status')) {
            $payload['value'] = true;
        } else {
            $payload['value'] = false;
        }
        break;
    case 'sensor_light':
        $payload['service'] = 'LightSensor';
        $payload['characteristic'] = 'CurrentAmbientLightLevel';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.value');
        break;
    case 'openclose':
        $payload['service'] = 'ContactSensor';
        $payload['characteristic'] = 'ContactSensorState';
        $nc = gg($device1['LINKED_OBJECT'] . '.ncno') == 'nc';
        $payload['value'] = $nc ? 1 - gg($device1['LINKED_OBJECT'] . '.status') : gg($device1['LINKED_OBJECT'] . '.status');
        break;
    case 'openable':
        $open_type = gg($device1['LINKED_OBJECT'] . '.openType');
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
                if (gg($device1['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = "1";
                } else {
                    $payload['value'] = "0";
                }

                $payload['characteristic'] = 'CurrentDoorState';
                if ($debug_sync) {
                    DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
                }
                sg('HomeBridge.to_set', json_encode($payload));
                /*
                if ($debug_sync) {
                    DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
                }
                $payload['characteristic'] = 'TargetDoorState';
                */

                unset($payload['service']);
            } elseif ($open_type == 'door' || $open_type == 'window' || $open_type == 'curtains' || $open_type == 'shutters') {
                $payload['characteristic'] = 'CurrentPosition';
                if (gg($device1['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = "0";
                } else {
                    $payload['value'] = "100";
                }
                if ($debug_sync) {
                    DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
                }
                sg('HomeBridge.to_set', json_encode($payload));
                $payload['characteristic'] = 'TargetPosition';
                sg('HomeBridge.to_set', json_encode($payload));
                unset($payload['service']);
            }
        }
        break;
    case 'rgb':
        $payload['service'] = 'Lightbulb';
        sg('HomeBridge.to_add', json_encode($payload));

        $payload['characteristic'] = 'On';
        if (gg($device1['LINKED_OBJECT'] . '.status')) {
            $payload['value'] = true;
        } else {
            $payload['value'] = false;
        }
        sg('HomeBridge.to_set', json_encode($payload));

        $payload['characteristic'] = 'Hue';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.hue');
        sg('HomeBridge.to_set', json_encode($payload));

        $payload['characteristic'] = 'Saturation';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.saturation');
        sg('HomeBridge.to_set', json_encode($payload));

        $payload['characteristic'] = 'Brightness';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.brightness');
        sg('HomeBridge.to_set', json_encode($payload));
        unset($payload['service']);
        break;
    case 'ledlamp':
        $payload['service'] = 'Lightbulb';
        sg('HomeBridge.to_add', json_encode($payload));

        $payload['characteristic'] = 'On';
        if (gg($device1['LINKED_OBJECT'] . '.status')) {
            $payload['value'] = true;
        } else {
            $payload['value'] = false;
        }
        sg('HomeBridge.to_set', json_encode($payload));


        $payload['characteristic'] = 'Brightness';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.brightness');
        sg('HomeBridge.to_set', json_encode($payload));
        unset($payload['service']);
        break;

    case 'thermostat':
        $payload['characteristic'] = 'CurrentTemperature';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.value');
        sg('HomeBridge.to_set', json_encode($payload));

        $payload['characteristic'] = 'TargetTemperature';
        $payload['value'] = gg($device1['LINKED_OBJECT'] . '.currentTargetValue');
        sg('HomeBridge.to_set', json_encode($payload));
        $payload['characteristic'] = 'CurrentHeatingCoolingState'; //off = 0, heat = 1, and cool = 2, auto = 3
        if (!gg($device1['LINKED_OBJECT'] . '.disabled')) {
            if (gg($device1['LINKED_OBJECT'] . '.status')) {
                $payload['value'] = 1;
            } else {
                $payload['value'] = 2;
            }
        } else {
            $payload['value'] = 0;
        }
        break;
    /*
    case 'sensor_battery':
       $payload['service']='BatteryService';
       sg('HomeBridge.to_add',json_encode($payload));
       // Characteristic.BatteryLevel;
       // Characteristic.ChargingState; 0 - NOT_CHARGING, 1 - CHARGING, 2 - NOT_CHARGEABLE
       // Characteristic.StatusLowBattery;
       $payload['characteristic'] = 'BatteryLevel';
       $payload['value']=gg($device1['LINKED_OBJECT'].'.value');
       sg('HomeBridge.to_set',json_encode($payload));

       $payload['characteristic'] = 'ChargingState';
       $payload['value']=2;
       sg('HomeBridge.to_set',json_encode($payload));

       $payload['characteristic'] = 'StatusLowBattery';
       $payload['value']=gg($device1['LINKED_OBJECT'].'.normalValue') ? 0 : 1;
       sg('HomeBridge.to_set',json_encode($payload));

       break;
    */
    default:
        $addon_path = dirname(__FILE__) . '/addons/' . $device1['TYPE'] . '_homebridgeSendUpdate.php';
        if (file_exists($addon_path)) {
            require($addon_path);
        }
}
if (isset($payload['service'])) {
    $hmName = 'hmb:' . $payload['name'];
    $payload_encoded = json_encode($payload);
    $hmValue = md5($payload_encoded);
    if (checkFromCache($hmName) != $hmValue) {
        saveToCache($hmName, $hmValue);
        if ($debug_sync) {
            DebMes("MQTT to_set : " . $payload_encoded, 'homebridge');
        }
        sg('HomeBridge.to_set', $payload_encoded);
    }
}
if (isset($payload2['service'])) {
    $hmName = 'hmb:' . $payload2['name'];
    $payload2_encoded = json_encode($payload2);
    $hmValue = md5($payload2_encoded);
    if (checkFromCache($hmName) != $hmValue) {
        saveToCache($hmName, $hmValue);
        if ($debug_sync) {
            DebMes("MQTT to_set : " . $payload2_encoded, 'homebridge');
        }
        sg('HomeBridge.to_set', $payload2_encoded);
    }
}
