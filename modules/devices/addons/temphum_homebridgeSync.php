<?php

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