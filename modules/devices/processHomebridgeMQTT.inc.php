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

    switch($device['TYPE']) {
      case 'relay':
         if ($data['characteristic'] == 'On') {
            $load_type=gg($device['LINKED_OBJECT'].'.loadType');
            if     ($load_type=='light')  $payload['service'] = 'Lightbulb';
            elseif ($load_type=='vent')   $payload['service'] = 'Fan';
            elseif ($load_type=='switch') $payload['service'] = 'Switch';
            else                          $payload['service'] = 'Outlet';
            $payload['characteristic'] = 'On';
            if (gg($device['LINKED_OBJECT'].'.status')) {
               $payload['value']=true;
            } else {
               $payload['value']=false;
            }
         }
         break;
      case 'sensor_temp':
         if ($data['characteristic'] == 'CurrentTemperature') {
            $payload['service']='TemperatureSensor';
            $payload['characteristic'] = 'CurrentTemperature';
            $payload['value']=gg($device['LINKED_OBJECT'].'.value');
         }
         break;
      case 'sensor_humidity':
         if ($data['characteristic'] == 'CurrentRelativeHumidity') {
            $payload['service']='HumiditySensor';
            $payload['characteristic'] = 'CurrentRelativeHumidity';
            $payload['value']=gg($device['LINKED_OBJECT'].'.value');
         }
         break;
      case 'motion':
         if ($data['characteristic'] == 'MotionDetected') {
            $payload['service']='MotionSensor';
            $payload['characteristic'] = 'MotionDetected';
            $payload['value']=gg($device['LINKED_OBJECT'].'.status');
         }
         break;
      case 'sensor_light':
         if ($data['characteristic'] == 'CurrentAmbientLightLevel') {
            $payload['service']='LightSensor';
            $payload['characteristic'] = 'CurrentAmbientLightLevel';
            $payload['value']=gg($device['LINKED_OBJECT'].'.value');
         }
         break;
      case 'openclose':
         if($data['characteristic'] == 'ContactSensorState') {
            $payload['service']='ContactSensor';
            $payload['characteristic'] = 'ContactSensorState';
            $nc = gg($device['LINKED_OBJECT'].'.ncno') == 'nc';
            $payload['value'] = $nc ? 1 - gg($device['LINKED_OBJECT'].'.status') : gg($device['LINKED_OBJECT'].'.status');
         }
         break;
      case 'rgb':
         if ($data['characteristic'] == 'On') {
            $payload['service'] = 'Lightbulb';
            $payload['characteristic'] = 'On';
            if (gg($device['LINKED_OBJECT'].'.status')) {
               $payload['value']=true;
            } else {
               $payload['value']=false;
            }
         } elseif ($data['characteristic'] == 'Hue') {
            $payload['service'] = 'Lightbulb';
            $payload['characteristic'] = 'Hue';
            $payload['value'] = gg($device['LINKED_OBJECT'].'.hue');
         } elseif ($data['characteristic'] == 'Saturation') {
            $payload['service'] = 'Lightbulb';
            $payload['characteristic'] = 'Saturation';
            $payload['value'] = gg($device['LINKED_OBJECT'].'.saturation');
         } elseif ($data['characteristic'] == 'Brightness') {
            $payload['service'] = 'Lightbulb';
            $payload['characteristic'] = 'Brightness';
            $payload['value'] = gg($device['LINKED_OBJECT'].'.brightness');
         }
         break;
    }
    if (isset($payload['value'])) {
        sg('HomeBridge.to_set',json_encode($payload));
    }
}

// set status from HomeKit
if ($params['PROPERTY']=='from_set' && $device['ID']) {
    if (in_array($device['TYPE'], array('relay'))) {
        if ($data['characteristic']=='On') {
            if ($data['value']) {
                callMethodSafe($device['LINKED_OBJECT'].'.turnOn');
            } else {
                callMethodSafe($device['LINKED_OBJECT'].'.turnOff');
            }
        }
    }
    if (in_array($device['TYPE'], array('rgb'))) {
      if ($data['characteristic']=='On') {
         if ($data['value']) callMethodSafe($device['LINKED_OBJECT'].'.turnOn');
         else callMethodSafe($device['LINKED_OBJECT'].'.turnOff');
      }
      if ($data['characteristic']=='Brightness') {
         if ($data['value']) {
            callMethodSafe($device['LINKED_OBJECT'].'.turnOn');
            sg($device['LINKED_OBJECT'].'.brightness', $data['value']);
         } else {
            callMethodSafe($device['LINKED_OBJECT'].'.turnOff');
            sg($device['LINKED_OBJECT'].'.brightness', 0);
         }
      }
      if ($data['characteristic']=='Hue') {
         sg($device['LINKED_OBJECT'].'.hue', $data['value']);
      }
      if ($data['characteristic']=='Saturation') {
         sg($device['LINKED_OBJECT'].'.saturation', $data['value']);
      }
      $h = gg($device['LINKED_OBJECT'].'.hue');
      $s = gg($device['LINKED_OBJECT'].'.saturation');
      $b = gg($device['LINKED_OBJECT'].'.brightness');
      $color = hsvToHex($h, $s, $b);
      sg($device['LINKED_OBJECT'].'.color', $color);
      if ($color!='000000') {
         sg($device['LINKED_OBJECT'].'.colorSaved', $color);
      }
      callMethodSafe($device['LINKED_OBJECT'].'.Refresh');
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
