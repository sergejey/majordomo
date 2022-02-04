<?php

$ot = $this->object_title;

$period = $this->getProperty('periodTime');
if (!$period) $period = 60 * 60;

$min_value = getHistoryMin($ot . '.value', (-1) * $period);
$value = (float)$this->getProperty('value');
if (is_null($min_value) || $value < $min_value) {
    $min_value = $value;
}

if (!is_null($min_value)) {
    $this->setProperty('periodMinValue', $min_value);
}
