<?php

if (!isset($params['value'])) return;
$value = $params['value'];

$targetTitle = 'fanSpeed';

$this->setProperty($targetTitle,$value);