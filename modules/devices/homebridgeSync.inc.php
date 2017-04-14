<?php
// thanks to https://github.com/cflurin/homebridge-mqtt

$qry="1";

if ($device_id) {
    $qry.=" AND ID=".$device_id;
}
$devices=SQLSelect("SELECT * FROM devices WHERE $qry");
$total = count($devices);
for ($i = 0; $i < $total; $i++) {
    if ($devices[$i]['TYPE']=='relay') {

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        sg('HomeBridge.to_remove',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='Switch';
        sg('HomeBridge.to_add',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='Switch';
        $payload['characteristic'] = 'On';
        if (gg($devices[$i]['LINKED_OBJECT'].'.status')) {
            $payload['value']=true;
        } else {
            $payload['value']=false;
        }
        sg('HomeBridge.to_set',json_encode($payload));

    } elseif ($devices[$i]['TYPE']=='sensor_temp') {

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        sg('HomeBridge.to_remove',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='TemperatureSensor';
        sg('HomeBridge.to_add',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='TemperatureSensor';
        $payload['characteristic'] = 'CurrentTemperature';
        $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.value');
        sg('HomeBridge.to_set',json_encode($payload));

    } elseif ($devices[$i]['TYPE']=='sensor_humidity') {

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        sg('HomeBridge.to_remove',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='HumiditySensor';
        sg('HomeBridge.to_add',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='HumiditySensor';
        $payload['characteristic'] = 'CurrentRelativeHumidity';
        $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.value');
        sg('HomeBridge.to_set',json_encode($payload));

    } elseif ($devices[$i]['TYPE']=='motion') {

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        sg('HomeBridge.to_remove',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='MotionSensor';
        sg('HomeBridge.to_add',json_encode($payload));

    } elseif ($devices[$i]['TYPE']=='button') {

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        sg('HomeBridge.to_remove',json_encode($payload));

        $payload=array();
        $payload['name']=$devices[$i]['LINKED_OBJECT'];
        $payload['service_name']=$devices[$i]['TITLE'];
        $payload['service']='Switch';
        sg('HomeBridge.to_add',json_encode($payload));

    }
}

sg('HomeBridge.to_get','{"name": "*"}');
sg('HomeBridge.mode','list');
setTimeout('HomeBridgeMode',"sg('HomeBridge.mode','');",5);