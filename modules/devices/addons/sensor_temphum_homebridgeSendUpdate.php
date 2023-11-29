<?php

$payload['service'] = 'TemperatureSensor';
$payload['characteristic'] = 'CurrentTemperature';
$payload['value'] = (int)gg($device1['LINKED_OBJECT'] . '.value');
sg('HomeBridge.to_set', json_encode($payload));
$payload['name'] .= "_Hum";
$payload['service_name'] .= "_Hum";
$payload['service'] = 'HumiditySensor';
$payload['characteristic'] = 'CurrentRelativeHumidity';
$payload['value'] = (int)gg($device1['LINKED_OBJECT'] . '.valueHumidity');
