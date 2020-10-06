<?php

if (!isset($params['value'])) return;
$value = $params['value'];

$targetTitle = 'currentTargetValue';

$this->setProperty($targetTitle,$value);