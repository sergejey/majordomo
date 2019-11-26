<?php

if (!isset($params['value'])) return;

$value = $params['value'];
$status = $this->getProperty('status');
if ($status) {
    $targetTitle='normalTargetValue';
} else {
    $targetTitle='ecoTargetValue';
}

$this->setProperty($targetTitle,$value);
$this->callMethod('valueUpdated');