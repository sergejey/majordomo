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
         $load_type=gg($device['LINKED_OBJECT'].'.loadType');
         if     ($load_type=='light')  $payload['service'] = 'Lightbulb';
         elseif ($load_type=='vent')   $payload['service'] = 'Fan';
         elseif ($load_type=='switch') $payload['service'] = 'Switch';
         else                          $payload['service'] = 'Outlet';

         if ($data['characteristic'] == 'On') {
            $payload['characteristic'] = 'On';
            if (gg($device['LINKED_OBJECT'].'.status')) {
               $payload['value']=true;
            } else {
               $payload['value']=false;
            }
         }
         break;
      case 'sensor_temp':
         $payload['service']='TemperatureSensor';
         if ($data['characteristic'] == 'CurrentTemperature') {
            $payload['characteristic'] = 'CurrentTemperature';
            $payload['value']=gg($device['LINKED_OBJECT'].'.value');
         }
         if ($data['characteristic'] == 'BatteryLevel') {
            $payload['value'] = 90;
         }
         break;
      case 'sensor_humidity':
         $payload['service']='HumiditySensor';
         if ($data['characteristic'] == 'CurrentRelativeHumidity') {
            $payload['characteristic'] = 'CurrentRelativeHumidity';
            $payload['value']=gg($device['LINKED_OBJECT'].'.value');
         }
         if ($data['characteristic'] == 'BatteryLevel') {
            $payload['value'] = 90;
         }
         break;
      case 'motion':
         $payload['service']='MotionSensor';
         if ($data['characteristic'] == 'MotionDetected') {
            $payload['characteristic'] = 'MotionDetected';
            $payload['value']=gg($device['LINKED_OBJECT'].'.status');
         }
         break;
      case 'sensor_light':
         $payload['service']='LightSensor';
         if ($data['characteristic'] == 'CurrentAmbientLightLevel') {
            $payload['characteristic'] = 'CurrentAmbientLightLevel';
            $payload['value']=gg($device['LINKED_OBJECT'].'.value');
         }
         break;
      case 'openclose':
         $payload['service']='ContactSensor';
         if($data['characteristic'] == 'ContactSensorState') {
            $payload['characteristic'] = 'ContactSensorState';
            $nc = gg($device['LINKED_OBJECT'].'.ncno') == 'nc';
            $payload['value'] = $nc ? 1 - gg($device['LINKED_OBJECT'].'.status') : gg($device['LINKED_OBJECT'].'.status');
         }
         break;
      case 'rgb':
         $payload['service'] = 'Lightbulb';
         if ($data['characteristic'] == 'On') {
            $payload['characteristic'] = 'On';
            if (gg($device['LINKED_OBJECT'].'.status')) {
               $payload['value']=true;
            } else {
               $payload['value']=false;
            }
         } elseif ($data['characteristic'] == 'Hue') {
            $payload['characteristic'] = 'Hue';
            $payload['value'] = gg($device['LINKED_OBJECT'].'.hue');
         } elseif ($data['characteristic'] == 'Saturation') {
            $payload['characteristic'] = 'Saturation';
            $payload['value'] = gg($device['LINKED_OBJECT'].'.saturation');
         } elseif ($data['characteristic'] == 'Brightness') {
            $payload['characteristic'] = 'Brightness';
            $payload['value'] = gg($device['LINKED_OBJECT'].'.brightness');
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
        sg('HomeBridge.to_set',json_encode($payload));
    }
}

// set status from HomeKit
if ($params['PROPERTY']=='from_set' && $device['ID']) {
   DebMes($device['TITLE'].' set '.$data['characteristic'].' to '.$data['value']);
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
         if ($data['value']) {
            if (gg($device['LINKED_OBJECT'].'.status') == 0) callMethodSafe($device['LINKED_OBJECT'].'.turnOn');
         } else {
            if (gg($device['LINKED_OBJECT'].'.status') == 1) callMethodSafe($device['LINKED_OBJECT'].'.turnOff');
         }
      }
      $colorChange = false;
      if ($data['characteristic']=='Brightness') {
         if ($data['value']) {
            sg($device['LINKED_OBJECT'].'.brightness', $data['value']);
            callMethodSafe($device['LINKED_OBJECT'].'.turnOn');
         } else {
            sg($device['LINKED_OBJECT'].'.brightness', 0);
            callMethodSafe($device['LINKED_OBJECT'].'.turnOff');
         }
      }
      if ($data['characteristic']=='Hue') {
         sg($device['LINKED_OBJECT'].'.hue', $data['value']);
         $colorChange = true;
      }
      if ($data['characteristic']=='Saturation') {
         sg($device['LINKED_OBJECT'].'.saturation', $data['value']);
         $colorChange = true;
      }
      if ($colorChange) {
         $h = gg($device['LINKED_OBJECT'].'.hue');
         $s = gg($device['LINKED_OBJECT'].'.saturation');
         $b = gg($device['LINKED_OBJECT'].'.lightness');
         $color = hsvToHex($h, $s, $b);
         sg($device['LINKED_OBJECT'].'.color', $color);
         if ($color!='000000') {
            $this->setProperty('colorSaved',$color);
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
