<?php

$color = $this->getProperty('color');

$hsv = hexToHsv($color);

if ($hsv && is_array($hsv)) {
    $this->setProperty('hue', intval($hsv[0]));
    $this->setProperty('saturation', intval($hsv[1] * 100));
    $this->setProperty('lightness', intval($hsv[2] * 100));
}

if ($color != '000000') {
    $this->setProperty('colorSaved', $color);
}
