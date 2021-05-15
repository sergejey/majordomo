<?php
// thanks to https://github.com/cflurin/homebridge-mqtt

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES == 1) return;

$debug_sync = 0;

$qry = "1";

if ($device_id) {
    $qry .= " AND ID=" . $device_id;
}
$devices = SQLSelect("SELECT * FROM devices WHERE $qry");
$total = count($devices);
DebMes("Syncing devices (total: $total)", 'homebridge');
for ($i = 0; $i < $total; $i++) {

    if ($devices[$i]['LINKED_OBJECT'] == '') {
        continue;
    }
    $payload = array();
    $payload['name'] = $devices[$i]['LINKED_OBJECT'];


    if ($devices[$i]['SYSTEM_DEVICE'] || $devices[$i]['ARCHIVED']) {
        if ($debug_sync) {
            DebMes("HomeBridge.to_remove: ".json_encode($payload),'homebridge');
        }
        sg('HomeBridge.to_remove', json_encode($payload));
        continue;
    }

    if ($force_refresh) {
        if ($debug_sync) {
            DebMes("HomeBridge.to_remove: " . json_encode($payload), 'homebridge');
        }
        sg('HomeBridge.to_remove', json_encode($payload));
    }

    $payload['service_name'] = processTitle($devices[$i]['TITLE']);

    switch ($devices[$i]['TYPE']) {
        case 'relay':
            $load_type = gg($devices[$i]['LINKED_OBJECT'] . '.loadType');
            if ($load_type == 'light') $payload['service'] = 'Lightbulb';
            elseif ($load_type == 'vent') $payload['service'] = 'Fan';
            elseif ($load_type == 'switch') $payload['service'] = 'Switch';
            else                          $payload['service'] = 'Outlet';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'On';
            if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                $payload['value'] = true;
            } else {
                $payload['value'] = false;
            }
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'openable':
            $open_type = gg($devices[$i]['LINKED_OBJECT'] . '.openType');
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
                sg('HomeBridge.to_add', json_encode($payload));
                if ($open_type == 'gates') {
                    $payload['characteristic'] = 'CurrentDoorState';
                    if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                        $payload['value'] = "1";
                    } else {
                        $payload['value'] = "0";
                    }
                    sg('HomeBridge.to_set', json_encode($payload));
                    $payload['characteristic'] = 'TargetDoorState';
                    $payload['value'] = "1";
                    sg('HomeBridge.to_set', json_encode($payload));
                } elseif ($open_type == 'door' || $open_type == 'window' || $open_type == 'curtains'  || $open_type == 'shutters') {
                    $payload['characteristic'] = 'CurrentPosition';
                    if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                        $payload['value'] = "100";
                    } else {
                        $payload['value'] = "0";
                    }
                    sg('HomeBridge.to_set', json_encode($payload));
                    $payload['characteristic'] = 'TargetPosition';
                    $payload['value'] = "100";
                    sg('HomeBridge.to_set', json_encode($payload));
                }
            }
            break;
        case 'sensor_temp':
            $payload['service'] = 'TemperatureSensor';
            $payload['CurrentTemperature']['minValue'] = -40;
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CurrentTemperature';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.value');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'sensor_humidity':
            $payload['service'] = 'HumiditySensor';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CurrentRelativeHumidity';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.value');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'sensor_temphum':
            // Temp
            $payload['service'] = 'TemperatureSensor';
            $payload['CurrentTemperature']['minValue'] = -40;
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CurrentTemperature';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.value');
            sg('HomeBridge.to_set', json_encode($payload));

            // Hum
            $payload['name'] .= '_Hum';
            $payload['service_name'] .= '_Hum';
            $payload['service'] = 'HumiditySensor';
            unset($payload['CurrentTemperature']['minValue']);
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CurrentRelativeHumidity';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.valueHumidity');
            sg('HomeBridge.to_set', json_encode($payload));

            break;

        case 'sensor_co2':
            $payload['service'] = 'CarbonDioxideSensor';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CarbonDioxideLevel';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.value');
            sg('HomeBridge.to_set', json_encode($payload));

            $payload['characteristic'] = 'CarbonDioxideDetected';
            $payload['value'] = "0";
            sg('HomeBridge.to_set', json_encode($payload));

            break;

        case 'sensor_moisture':
            //todo
            break;

        case 'sensor_radiation':
            //todo
            break;

        case 'vacuum':
            //todo
            break;

        case 'media':
            //todo
            break;

        case 'tv':
            //todo
            break;

        case 'motion':
            $payload['service'] = 'MotionSensor';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'MotionDetected';
            $payload['value'] = (int)gg($devices[$i]['LINKED_OBJECT'] . '.status');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'smoke':
            $payload['service'] = 'SmokeSensor';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'SmokeDetected';
            $payload['value'] = (int)gg($devices[$i]['LINKED_OBJECT'] . '.status');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'leak':
            $payload['service'] = 'LeakSensor';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'LeakDetected';
            $payload['value'] = (int)gg($devices[$i]['LINKED_OBJECT'] . '.status');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'button':
            $payload['service'] = 'Switch';
            sg('HomeBridge.to_add', json_encode($payload));
            break;
        case 'sensor_light':
            $payload['service'] = 'LightSensor';
            $payload['CurrentAmbientLightLevel']['minValue'] = 0;
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CurrentAmbientLightLevel';
            $payload['value'] = (int)gg($devices[$i]['LINKED_OBJECT'] . '.value');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'openclose':
            $payload['service'] = 'ContactSensor';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'ContactSensorState';
            $payload['value'] = (int)gg($devices[$i]['LINKED_OBJECT'] . '.ncno') == 'nc' ? 1 - gg($devices[$i]['LINKED_OBJECT'] . '.status') : gg($devices[$i]['LINKED_OBJECT'] . '.status');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'dimmer':
            $payload['service'] = 'Lightbulb';
            $payload['Brightness'] = 'default';
            sg('HomeBridge.to_add', json_encode($payload));
            $payload['characteristic'] = 'On';
            if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                $payload['value'] = true;
            } else {
                $payload['value'] = false;
            }
            sg('HomeBridge.to_set', json_encode($payload));
            $payload['characteristic'] = 'Brightness';
            $payload['value'] = (int)gg($devices[$i]['LINKED_OBJECT'] . '.level');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'rgb':
            //DebMes('Sync '.$devices[$i]['TITLE'].' from MJD');
            $payload['service'] = 'Lightbulb';
            $payload['Hue'] = 'default';
            $payload['Saturation'] = 'default';
            $payload['Brightness'] = 'default';

            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'On';
            if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                $payload['value'] = true;
            } else {
                $payload['value'] = false;
            }
            sg('HomeBridge.to_set', json_encode($payload));

            $payload['characteristic'] = 'Hue';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.hue');
            sg('HomeBridge.to_set', json_encode($payload));

            $payload['characteristic'] = 'Saturation';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.saturation');
            sg('HomeBridge.to_set', json_encode($payload));

            $payload['characteristic'] = 'Brightness';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.brightness');
            sg('HomeBridge.to_set', json_encode($payload));
            break;
        case 'ledlamp':
            //DebMes('Sync '.$devices[$i]['TITLE'].' from MJD');
            $payload['service'] = 'Lightbulb';
            $payload['Brightness'] = 'default';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'On';
            if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                $payload['value'] = true;
            } else {
                $payload['value'] = false;
            }
            sg('HomeBridge.to_set', json_encode($payload));
            $payload['characteristic'] = 'Brightness';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.brightness');

            sg('HomeBridge.to_set', json_encode($payload));

            break;

        case 'thermostat':
            $payload['service'] = 'Thermostat';
            sg('HomeBridge.to_add', json_encode($payload));

            $payload['characteristic'] = 'CurrentTemperature';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.value');
            sg('HomeBridge.to_set', json_encode($payload));

            $payload['characteristic'] = 'TargetTemperature';
            $payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.currentTargetValue');
            sg('HomeBridge.to_set', json_encode($payload));

            /*
            $payload['characteristic'] = 'TemperatureDisplayUnits';
            $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.currentTargetValue');
            sg('HomeBridge.to_set',json_encode($payload));
            */
            $payload['characteristic'] = 'CurrentHeatingCoolingState'; //off = 0, heat = 1, and cool = 2
            //$payload['value']=3;
            if (!gg($devices[$i]['LINKED_OBJECT'] . '.disabled')) {
                if (gg($devices[$i]['LINKED_OBJECT'] . '.status')) {
                    $payload['value'] = 1;
                } else {
                    $payload['value'] = 2;
                }
            } else {
                $payload['value'] = 0;
            }
            sg('HomeBridge.to_set', json_encode($payload));
            //TargetHeatingCoolingState


            //CoolingThresholdTemperature
            //HeatingThresholdTemperature
            //Name


            break;
        case 'camera':
            /*
            $cameraUsername = gg($devices[$i]['LINKED_OBJECT'].'.cameraUsername');
            $cameraPassword = gg($devices[$i]['LINKED_OBJECT'].'.cameraPassword');
            $snapshot_url = gg($devices[$i]['LINKED_OBJECT'].'.snapshotURL');
            $stream_url = gg($devices[$i]['LINKED_OBJECT'].'.streamURL');
            $stream_url_hq = gg($devices[$i]['LINKED_OBJECT'].'.streamURL_HQ');
            if ($snapshot_url) {
               $stream_url=$snapshot_url;
            } elseif (!$stream_url && $stream_url_hq) {
               $stream_url = $stream_url_hq;
            }
            $thumb_params ='';
            $thumb_params.= 'username="' . $cameraUsername . '" password="' . $cameraPassword . '"';
            $thumb_params.= ' width="1024"';
            $thumb_params.= ' url="' . $stream_url . '"';
            $streamTransport = gg($devices[$i]['LINKED_OBJECT'].'.streamTransport');
            if ($streamTransport!='auto' && $streamTransport!='') {
               $thumb_params.= ' transport="'.$streamTransport.'"';
            }
            $body = '[#module name="thumb" '. $thumb_params. '#]';
            $body = processTitle($body, $this);
            if (preg_match('/img src="(.+?)"/is',$body,$m)) {
               $snapshotPreviewURL=$m[1];
               $snapshotPreviewURL = preg_replace('/&w=(\d+?)/','', $snapshotPreviewURL);
               $snapshotPreviewURL = preg_replace('/&h=(\d+?)/','', $snapshotPreviewURL);
            } else {
               $snapshotPreviewURL='';
            }
            $snapshotPreviewURL='http://'.getLocalIP().$snapshotPreviewURL;

            $payload['service']='CameraRTPStreamManagement';
            sg('HomeBridge.to_add',json_encode($payload));

            $payload['characteristic'] = 'SupportedVideoStreamConfiguration';
            $payload['value']='';
            sg('HomeBridge.to_set',json_encode($payload));

            $payload['characteristic'] = 'SupportedAudioStreamConfiguration';
            $payload['value']='';
            sg('HomeBridge.to_set',json_encode($payload));

            $payload['characteristic'] = 'SupportedRTPConfiguration';
            $payload['value']='';
            sg('HomeBridge.to_set',json_encode($payload));

            $payload['characteristic'] = 'SelectedRTPStreamConfiguration';
            $payload['value']='';
            sg('HomeBridge.to_set',json_encode($payload));

            $payload['characteristic'] = 'StreamingStatus';
            $payload['value']='';
            sg('HomeBridge.to_set',json_encode($payload));

            $payload['characteristic'] = 'SetupEndpoints';
            $payload['value']='';
            sg('HomeBridge.to_set',json_encode($payload));
   */
            break;
        /*
        case 'sensor_battery':
           $payload['service']='BatteryService';
           sg('HomeBridge.to_add',json_encode($payload));
           // Characteristic.BatteryLevel;
           // Characteristic.ChargingState; 0 - NOT_CHARGING, 1 - CHARGING, 2 - NOT_CHARGEABLE
           // Characteristic.StatusLowBattery;
           $payload['characteristic'] = 'BatteryLevel';
           $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.value');
           sg('HomeBridge.to_set',json_encode($payload));

           $payload['characteristic'] = 'ChargingState';
           $payload['value']=2;
           sg('HomeBridge.to_set',json_encode($payload));

           $payload['characteristic'] = 'StatusLowBattery';
           $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.normalValue') ? 0 : 1;
           sg('HomeBridge.to_set',json_encode($payload));
           break;
        */
    }
}

sg('HomeBridge.to_get', '{"name": "*"}');
sg('HomeBridge.mode', 'list');
setTimeout('HomeBridgeMode', "sg('HomeBridge.mode','');", 5);
