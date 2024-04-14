<?php

// Temp
$payload['service'] = 'TemperatureSensor';
addToOperationsQueue("homekit_queue", "add", json_encode($payload, JSON_UNESCAPED_UNICODE));

$payload['characteristic'] = 'CurrentTemperature';
$payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.value');
addToOperationsQueue("homekit_queue", "set", json_encode($payload, JSON_UNESCAPED_UNICODE));

// Hum
$payload['service_name'] .= '_Hum';
$payload['service'] = 'HumiditySensor';
addToOperationsQueue("homekit_queue", "add/service", json_encode($payload, JSON_UNESCAPED_UNICODE));

$payload['characteristic'] = 'CurrentRelativeHumidity';
$payload['value'] = gg($devices[$i]['LINKED_OBJECT'] . '.valueHumidity');
addToOperationsQueue("homekit_queue", "set", json_encode($payload, JSON_UNESCAPED_UNICODE));
$frombattery = true;