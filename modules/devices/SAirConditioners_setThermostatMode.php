<?php

if (!isset($params['value'])) return;
$value = $params['value'];

$targetTitle = 'thermostat';

$this->setProperty($targetTitle,$value);