<?php

$old_value=(float)$params['OLD_VALUE'];
$new_value=(float)$params['NEW_VALUE'];

$diff = $new_value-$old_value;
if ($diff<0) {
    $diff = $new_value;
}

$conversion = (float)$this->getProperty('conversion');
if ($conversion>0) {
    $diff = $diff * $conversion;
}

$data_value = (float)$this->getProperty('value');
$new_data_value = (($data_value + $diff));

$this->setProperty('value',round($new_data_value,3));