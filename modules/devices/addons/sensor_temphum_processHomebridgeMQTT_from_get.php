<?php

if ($data['characteristic'] == 'CurrentTemperature') {
	 $payload['service'] = 'TemperatureSensor';
	 $payload['characteristic'] = 'CurrentTemperature';
	 $payload['value'] = (int)gg($device['LINKED_OBJECT'] . '.value');
} else {
	 $payload['name'] .= "_Hum";
	 $payload['service_name'] .= "_Hum";
	 $payload['service'] = 'HumiditySensor';
	 $payload['characteristic'] = 'CurrentRelativeHumidity';
	 $payload['value'] = (int)gg($device['LINKED_OBJECT'] . '.valueHumidity');
}
