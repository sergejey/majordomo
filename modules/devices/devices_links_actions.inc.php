<?php

//DebMes("Checking linked actions for device ".$device1['ID']);

if ($this->isHomeBridgeAvailable()) {
    // send updated status to HomeKit
    $payload=array();
    $payload['name']=$device1['LINKED_OBJECT'];
    $payload['service_name']=$device1['TITLE'];

    if ($device1['TYPE']=='relay') {
        $payload['service']='Switch';
        $payload['characteristic'] = 'On';
        if (gg($device1['LINKED_OBJECT'].'.status')) {
            $payload['value']=true;
        } else {
            $payload['value']=false;
        }
    } elseif ($device1['TYPE']=='sensor_temp') {
        $payload['service']='TemperatureSensor';
        $payload['characteristic'] = 'CurrentTemperature';
        $payload['value']=gg($device1['LINKED_OBJECT'].'.value');
    } elseif ($device1['TYPE']=='sensor_humidity') {
        $payload['service']='HumiditySensor';
        $payload['characteristic'] = 'CurrentRelativeHumidity';
        $payload['value']=gg($device1['LINKED_OBJECT'].'.value');
    } elseif ($device1['TYPE']=='motion') {
        $payload['service']='MotionSensor';
        $payload['characteristic'] = 'MotionDetected';
        if (gg($device1['LINKED_OBJECT'].'.status')) {
            $payload['value']=true;
        } else {
            $payload['value']=false;
        }
    }
    if (isset($payload['value'])) {
        DebMes('HB sending to_set: '.json_encode($payload));
        sg('HomeBridge.to_set',json_encode($payload));
    }

}

$value = (float)gg($device1['LINKED_OBJECT'].'.value');

$links=SQLSelect("SELECT devices_linked.*, devices.LINKED_OBJECT FROM devices_linked LEFT JOIN devices ON devices_linked.DEVICE2_ID=devices.ID WHERE DEVICE1_ID=".(int)$device1['ID']);
$total = count($links);
for ($i = 0; $i < $total; $i++) {
    $link_type=$links[$i]['LINK_TYPE'];
    $object=$links[$i]['LINKED_OBJECT'];
    $settings=unserialize($links[$i]['LINK_SETTINGS']);
    $timer_name='linkTimer'.$links[$i]['ID'];
    $action_string='';
    // -----------------------------------------------------------------
    if ($link_type=='switch_it') {
        if ($settings['action_type'] == 'turnoff') {
            $action_string = 'callMethod("' . $object . '.turnOff' . '");';
        } elseif ($settings['action_type'] == 'turnon') {
            $action_string = 'callMethod("' . $object . '.turnOn' . '");';
        } elseif ($settings['action_type'] == 'switch') {
            $action_string = 'callMethod("' . $object . '.switch' . '");';
        }
        if ((int)$settings['action_delay'] > 0) {
            $action_string = 'setTimeout(\'' . $timer_name . '\',\'' . $action_string . '\',' . (int)$settings['action_delay'] . ');';
        }
    } elseif ($link_type=='set_color') {
            $action_string='callMethod("'.$object.'.setColor'.'",array("color"=>"'.$settings['action_color'].'"));';
            if ((int)$settings['action_delay']>0) {
                $action_string='setTimeout(\''.$timer_name.'\',\''.$action_string.'\','.(int)$settings['action_delay'].');';
            }
            // -----------------------------------------------------------------
    // -----------------------------------------------------------------
    } elseif ($link_type=='sensor_switch') {
        if ($settings['action_type']=='turnoff' && gg($object.'.status')) {
            $action_string='callMethod("'.$object.'.turnOff'.'");';
        } elseif ($settings['action_type']=='turnon' && !gg($object.'.status')) {
            $action_string='callMethod("'.$object.'.turnOn'.'");';
        }
        if ($settings['condition_type']=='above' && $value>=(float)$settings['condition_value']) {
            //do the action
        } elseif ($settings['condition_type']=='below' && $value<(float)$settings['condition_value']) {
            //do the action
        } else {
            //do nothing
            $action_string='';
        }
    }

    // -----------------------------------------------------------------
    if ($action_string!='') {
        //DebMes("Action string: ".$action_string);
        try {
            $code = $action_string;
            $success = eval($code);
            if ($success === false) {
                registerError('linked_device', sprintf('Error in linked device code "%s". Code: %s', $link_type, $code));
            }
        } catch (Exception $e) {
            registerError('linked_device', sprintf('Error in script "%s": '.$e->getMessage(), $link_type));
        }
    }

}

