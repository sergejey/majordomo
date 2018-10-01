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
$new_data_value = round(($data_value + $diff),3);

if ($data_value!=$new_data_value) {
  $this->setProperty('value',$new_data_value);
}

