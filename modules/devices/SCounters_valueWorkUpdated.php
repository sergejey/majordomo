<?php

$old_value = (float)$params['OLD_VALUE'];
$new_value = (float)$params['NEW_VALUE'];

/*
//при частом обновлении данных
$new_value = round($params['VALUE'], 1);
$old_value = round($params['OLD_VALUE'], 1);

if ($new_value == $old_value) {
 return;
}
*/

$diff = $new_value - $old_value;
if ($diff < 0) {
    $diff = $new_value;
}

/*
//пример на 3-х тарифный, для дальнейшего учета и передачи показаний
if (timeBetween('23:00', '07:00')) {
    $conversion = (float)$this->getProperty('nightRate');
    $value = (float)$this->getProperty('night');
    $this->setProperty('night', $value + $diff);
}
elseif (timeBetween('10:00', '17:00') || timeBetween('21:00', '23:00')) {
    $conversion = (float)$this->getProperty('halfpeakRate');
    $value = (float)$this->getProperty('halfpeak');
    $this->setProperty('halfpeak', $value + $diff);
} else {
    $conversion = (float)$this->getProperty('peakRate');
    $value = (float)$this->getProperty('peak');
    $this->setProperty('peak', $value + $diff);
}
*/

$conversion = (float)$this->getProperty('conversion');
if ($conversion > 0) {
    $diff = $diff * $conversion;
}

$value = (float)$this->getProperty('value');
$data_value = round(($value + $diff), 2);

if ($value != $data_value) {
    $this->setProperty('value', $data_value);
}