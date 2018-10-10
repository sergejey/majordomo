<?php
// thanks to https://github.com/cflurin/homebridge-mqtt

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

$qry="1";

if ($device_id) {
    $qry.=" AND ID=".$device_id;
}
$devices=SQLSelect("SELECT * FROM devices WHERE $qry");
$total = count($devices);
DebMes("Syncing devices (total: $total)",'homebridge');
for ($i = 0; $i < $total; $i++) {
    
   $payload=array();
   $payload['name']=$devices[$i]['LINKED_OBJECT'];
   sg('HomeBridge.to_remove',json_encode($payload));

   $payload['service_name']=$devices[$i]['TITLE'];

   switch($devices[$i]['TYPE']) {
      case 'relay':
         $load_type=gg($devices[$i]['LINKED_OBJECT'].'.loadType');
         if     ($load_type=='light')  $payload['service'] = 'Lightbulb';
         elseif ($load_type=='vent')   $payload['service'] = 'Fan';
         elseif ($load_type=='switch') $payload['service'] = 'Switch';
         else                          $payload['service'] = 'Outlet';
         sg('HomeBridge.to_add',json_encode($payload));

         $payload['characteristic'] = 'On';
         if (gg($devices[$i]['LINKED_OBJECT'].'.status')) {
            $payload['value']=true;
         } else {
            $payload['value']=false;
         }
         sg('HomeBridge.to_set',json_encode($payload));
         break;
      case 'sensor_temp':
         $payload['service']='TemperatureSensor';
         sg('HomeBridge.to_add',json_encode($payload));

         $payload['characteristic'] = 'CurrentTemperature';
         $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.value');
         sg('HomeBridge.to_set',json_encode($payload));
         break;
      case 'sensor_humidity':
         $payload['service']='HumiditySensor';
         sg('HomeBridge.to_add',json_encode($payload));

         $payload['characteristic'] = 'CurrentRelativeHumidity';
         $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.value');
         sg('HomeBridge.to_set',json_encode($payload));
         break;
      case 'motion':
         $payload['service']='MotionSensor';
         sg('HomeBridge.to_add',json_encode($payload));

         $payload['characteristic'] = 'MotionDetected';
         $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.status');
         sg('HomeBridge.to_set',json_encode($payload));
         break;
      case 'button':
         $payload['service']='Switch';
         sg('HomeBridge.to_add',json_encode($payload));
         break;
      case 'sensor_light':
         $payload['service']='LightSensor';
         sg('HomeBridge.to_add',json_encode($payload));
            
         $payload['characteristic'] = 'CurrentAmbientLightLevel';
         $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.value');
         sg('HomeBridge.to_set',json_encode($payload));
         break;
      case 'openclose':
         $payload['service']='ContactSensor';
         sg('HomeBridge.to_add',json_encode($payload));

         $payload['characteristic'] = 'ContactSensorState';
         $payload['value']=gg($devices[$i]['LINKED_OBJECT'].'.ncno') == 'nc' ? 1 - gg($devices[$i]['LINKED_OBJECT'].'.status') : gg($devices[$i]['LINKED_OBJECT'].'.status');
         sg('HomeBridge.to_set',json_encode($payload));
         break;

   }
}

sg('HomeBridge.to_get','{"name": "*"}');
sg('HomeBridge.mode','list');
setTimeout('HomeBridgeMode',"sg('HomeBridge.mode','');",5);