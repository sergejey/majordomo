<?php

if ($params['PROPERTY'] == 'value'){
	$payload['service'] = 'TemperatureSensor';
	$payload['characteristic'] = 'CurrentTemperature';
} else if ($params['PROPERTY'] == 'valueHumidity'){
	$payload['service_name'] .= "_Hum";
	$payload['service'] = 'HumiditySensor';
	$payload['characteristic'] = 'CurrentRelativeHumidity';
}
$payload['value'] = $params['NEW_VALUE'];
