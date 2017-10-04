<?php
/*
    $params['PROPERTY']=$property;
    $params['NEW_VALUE']=(string)$value;
*/
DebMes("HB property: ".$params['PROPERTY'], 'homebridge');
DebMes("HB value: ".$params['NEW_VALUE'], 'homebridge');

$data=json_decode($params['NEW_VALUE'],true);

if ($data['name']) {
    $device=SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '".DBSafe($data['name'])."'");
}

if ($params['PROPERTY']=='from_response' && gg('HomeBridge.mode')=='list') {
    $devices=array();
    foreach($data as $k=>$v) {
        if (is_array($v['services'])) {
            $devices[]=$k;
        }
    }
    $total = count($devices);
    if ($total>0) {
        DebMes("Got devices list", 'homebridge');
        sg('HomeBridge.mode','');
        $to_remove=array();
        for ($i = 0; $i < $total; $i++) {
            $device=SQLSelectOne("SELECT ID FROM devices WHERE LINKED_OBJECT LIKE '".DBSafe($devices[$i])."'");
            if (!$device['ID']) {
                $to_remove[]=$devices[$i];
            }
        }
        $total = count($to_remove);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                $payload=array();
                $payload['name']=$to_remove[$i];
                DebMes("Homebridge: removing unknown device ".$payload['name'], 'homebridge');
                sg('HomeBridge.to_remove',json_encode($payload));
            }
        } else {
            DebMes("Nothing to remove", 'homebridge');
        }
    }
}

// reply to status request from HomeKit
if ($params['PROPERTY']=='from_get' && $device['ID']) {
    $payload=array();
    $payload['name']=$device['LINKED_OBJECT'];
    $payload['service_name']=$device['TITLE'];
    if ($device['TYPE']=='relay' && $data['characteristic'] == 'On') {
            $payload['service']='Switch';
            $payload['characteristic'] = 'On';
            if (gg($device['LINKED_OBJECT'].'.status')) {
                $payload['value']=true;  
            } else {
                $payload['value']=false;
            }
    } elseif ($device['TYPE']=='sensor_temp' && $data['characteristic'] == 'CurrentTemperature') {
        $payload['service']='TemperatureSensor';
        $payload['characteristic'] = 'CurrentTemperature';
        $payload['value']=gg($device['LINKED_OBJECT'].'.value');
    } elseif ($device['TYPE']=='sensor_humidity' && $data['characteristic'] == 'CurrentRelativeHumidity') {
        $payload['service']='HumiditySensor';
        $payload['characteristic'] = 'CurrentRelativeHumidity';
        $payload['value']=gg($device['LINKED_OBJECT'].'.value');
    } elseif ($device['TYPE']=='motion' && $data['characteristic'] == 'MotionDetected') {
        $payload['service']='MotionSensor';
        $payload['characteristic'] = 'MotionDetected';
        $payload['value']=gg($device['LINKED_OBJECT'].'.status');
    }
    if (isset($payload['value'])) {
        sg('HomeBridge.to_set',json_encode($payload));
    }
}

// set status from HomeKit
if ($params['PROPERTY']=='from_set' && $device['ID']) {
    if ($device['TYPE']=='relay') {
        if ($data['characteristic']=='On') {
            if ($data['value']) {
                callMethodSafe($device['LINKED_OBJECT'].'.turnOn');
            } else {
                callMethodSafe($device['LINKED_OBJECT'].'.turnOff');
            }
        }
    }
    if ($device['TYPE']=='button') {
        if ($data['characteristic']=='ProgrammableSwitchEvent' || $data['characteristic']=='On') {
            callMethodSafe($device['LINKED_OBJECT'].'.pressed');
            if ($data['characteristic'] == 'On') {
                $payload=array();
                $payload['name']=$device['LINKED_OBJECT'];
                $payload['service_name']=$device['TITLE'];
                //$payload['service'] = 'Switch';
                $payload['characteristic'] = 'On';
                $payload['value'] = false;
                sg('HomeBridge.to_set',json_encode($payload));
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
