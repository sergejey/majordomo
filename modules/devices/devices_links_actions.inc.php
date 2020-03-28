<?php

startMeasure('homebridge_update');

$debug_sync = 0;

//DebMes("homebridgesync for ".$device1['TITLE'],'homebridge');

if (!$device1['SYSTEM_DEVICE'] && $this->isHomeBridgeAvailable()) {
    // send updated status to HomeKit
    $payload = array();
    $payload['name'] = $device1['LINKED_OBJECT'];
    $payload['service_name'] = $device1['TITLE'];

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
                } elseif ($open_type == 'door' || $open_type == 'window' || $open_type == 'curtains'  || $open_type == 'shutters') {
                    $payload['characteristic'] = 'CurrentPosition';
                    if (gg($device1['LINKED_OBJECT'] . '.status')) {
                        $payload['value'] = "100";
                    } else {
                        $payload['value'] = "0";
                    }
                    if ($debug_sync) {
                        DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
                    }
                    sg('HomeBridge.to_set', json_encode($payload));
                    //$payload['characteristic'] = 'TargetPosition';
                    //sg('HomeBridge.to_set', json_encode($payload));
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
        case 'thermostat':
            $payload['characteristic'] = 'CurrentTemperature';
            $payload['value']=gg($device1['LINKED_OBJECT'].'.value');
            sg('HomeBridge.to_set',json_encode($payload));

            $payload['characteristic'] = 'TargetTemperature';
            $payload['value']=gg($device1['LINKED_OBJECT'].'.currentTargetValue');
            sg('HomeBridge.to_set',json_encode($payload));
            $payload['characteristic'] = 'CurrentHeatingCoolingState'; //off = 0, heat = 1, and cool = 2, auto = 3
            if (!gg($device1['LINKED_OBJECT'].'.disabled')) {
                if (gg($device1['LINKED_OBJECT'].'.status')) {
                    $payload['value']=1;
                } else {
                    $payload['value']=2;
                }
            } else {
                $payload['value']=0;
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
    }
    if (isset($payload['service'])) {
        if ($debug_sync) {
            DebMes("MQTT to_set : " . json_encode($payload), 'homebridge');
        }
        sg('HomeBridge.to_set', json_encode($payload));
    }
}
endMeasure('homebridge_update');

startMeasure('checkingLinks');
$value = (float)gg($device1['LINKED_OBJECT'] . '.value');
$status = (float)gg($device1['LINKED_OBJECT'] . '.status');

$links = SQLSelect("SELECT devices_linked.*, devices.LINKED_OBJECT FROM devices_linked LEFT JOIN devices ON devices_linked.DEVICE2_ID=devices.ID WHERE DEVICE1_ID=" . (int)$device1['ID']);
$total = count($links);
for ($i = 0; $i < $total; $i++) {
    if (!checkAccess('sdevice',$links[$i]['ID'])) continue;
    $link_type = $links[$i]['LINK_TYPE'];
    $object = $links[$i]['LINKED_OBJECT'];
    $settings = unserialize($links[$i]['LINK_SETTINGS']);
    $timer_name = 'linkTimer' . $links[$i]['ID'];
    $action_string = '';
    // -----------------------------------------------------------------
    if ($link_type == 'switch_it') {
        if ($settings['action_type'] == 'turnoff') {
            $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        } elseif ($settings['action_type'] == 'turnon') {
            $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        } elseif ($settings['action_type'] == 'switch') {
            $action_string = 'callMethodSafe("' . $object . '.switch' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        }
        if ((int)$settings['action_delay'] > 0) {
            $action_string = 'setTimeout(\'' . $timer_name . '\',\'' . $action_string . '\',' . (int)$settings['action_delay'] . ');';
        }
    } elseif ($link_type == 'switch_timer') {
        $timer_name = $object . '_switch_timer';
        $action_string = '';
        if ($settings['darktime']) {
            $action_string .= 'if (gg("DarknessMode.active")) {';
        }
        $action_string .= 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        if ((int)$settings['action_delay'] > 0) {
            $action_string .= 'setTimeout(\'' . $timer_name . '\',\'' . 'callMethod("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));' . '\',' . (int)$settings['action_delay'] . ');';
        }
        if ($settings['darktime']) {
            $action_string .= '}';
        }
    } elseif ($link_type == 'set_color') {
        $action_string = 'callMethodSafe("' . $object . '.setColor' . '",array("color"=>"' . $settings['action_color'] . '","link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        if ((int)$settings['action_delay'] > 0) {
            $action_string = 'setTimeout(\'' . $timer_name . '\',\'' . $action_string . '\',' . (int)$settings['action_delay'] . ');';
        }
        // -----------------------------------------------------------------
        // -----------------------------------------------------------------
    } elseif ($link_type == 'sensor_switch') {
        if ($settings['action_type'] == 'turnoff' && gg($object . '.status')) {
            $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        } elseif ($settings['action_type'] == 'turnon' && !gg($object . '.status')) {
            $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        }
        if ($settings['condition_type'] == 'above' && $value >= (float)$settings['condition_value']) {
            //do the action
        } elseif ($settings['condition_type'] == 'below' && $value < (float)$settings['condition_value']) {
            //do the action
        } else {
            //do nothing
            $action_string = '';
        }
    } elseif ($link_type == 'sensor_pass') {
        $action_string = 'sg("' . $object . '.value' . '","' . $value . '");';
    } elseif ($link_type == 'open_sensor_pass') {
        $action_string = 'sg("' . $object . '.status' . '","' . $status . '");';
    } elseif ($link_type == 'thermostat_switch') {
        $set_value = 0;
        $current_relay_status = gg($device1['LINKED_OBJECT'] . '.relay_status');
        $ncno = gg($device1['LINKED_OBJECT'] . '.ncno');
        if ($ncno == 'no' && $current_relay_status) {
            $current_relay_status = 0;
        } elseif ($ncno == 'no' && !$current_relay_status) {
            $current_relay_status = 1;
        }
        $current_target_status = gg($object . '.status');
        //echo "status: $current_relay_status / $current_target_status<Br/>";
        if (!$settings['invert_status'] && $current_relay_status) { // NC
            $set_value = 1;
        } elseif ($settings['invert_status'] && !$current_relay_status) {
            $set_value = 1;
        }
        if ($set_value && !$current_target_status) {
            // turn on
            $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        } elseif (!$set_value && $current_target_status) {
            // turn off
            $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"'.$device1['LINKED_OBJECT'].'"));';
        }
    }

    $addons_dir = DIR_MODULES . $this->name . '/addons';
    if (is_dir($addons_dir)) {
        $addon_files = scandir($addons_dir);
        foreach ($addon_files as $file) {
            if (preg_match('/\_links_actions\.php$/', $file)) {
                require($addons_dir . '/' . $file);
            }
        }
    }

    // -----------------------------------------------------------------
    if ($action_string != '') {
        //DebMes("Action string: ".$action_string,'logic_test');
        try {
            $code = $action_string;
            $success = eval($code);
            if ($success === false) {
                registerError('linked_device', sprintf('Error in linked device code "%s". Code: %s', $link_type, $code));
            }
        } catch (Exception $e) {
            registerError('linked_device', sprintf('Error in script "%s": ' . $e->getMessage(), $link_type));
        }
    }

    endMeasure('checkingLinks');

}
