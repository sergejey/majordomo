<?php

if ($data['characteristic'] == 'CurrentTemperature') {
	 $payload['service'] = 'TemperatureSensor';
	 $payload['characteristic'] = 'CurrentTemperature';
	 $payload['value'] = gg($device['LINKED_OBJECT'] . '.value');
} else {
	 $payload['service_name'] .= "_Hum";
	 $payload['service'] = 'HumiditySensor';
	 $payload['characteristic'] = 'CurrentRelativeHumidity';
	 $payload['value'] = gg($device['LINKED_OBJECT'] . '.valueHumidity');
}