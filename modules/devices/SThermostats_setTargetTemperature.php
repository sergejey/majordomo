<?php
if (!isset($params['value'])) return;
$value = $params['value'];

$this->setProperty('currentTargetValue', $value);
