<?php

if (!$params['color']) return;

$color = strtolower($params['color']);
$color = preg_replace('/^#/','',$color);

$transform=array(
    'red'=>'ff0000',
    'green'=>'00ff00',
    'blue'=>'0000ff',
    'white'=>'ffffff'
);

if (isset($transform[$color])) {
    $color = $transform[$color];
}

if ($color == '000000') {
    $this->callMethodSafe('turnOff');
} else {
    if (!$this->getProperty('status')) {
        $this->callMethod('turnOn');
    }
    $this->setProperty('color', $color);
    $this->setProperty('colorSaved', $color);
}
