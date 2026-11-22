<?php

$color = $this->getProperty('color');

if (method_exists($this, 'callMethodSafe')) {
    $this->callMethodSafe('keepAlive');
}

$hsv = hexToHsv($color);

if ($hsv && is_array($hsv)) {
    $this->setProperty('hue', intval($hsv[0]));
    $this->setProperty('saturation', intval($hsv[1] * 100));
    $this->setProperty('lightness', intval($hsv[2] * 100));
}

// Linked-module polls set SOURCE to the module name (no path). Skip rewriting
// colorSaved from that echo so dim/scaled reads cannot trap turnOn forever.
$source = isset($params['SOURCE']) ? (string)$params['SOURCE'] : '';
$sourceToken = strtolower(trim(strtok(str_replace(',', ' ', $source), ' ')));
if ($sourceToken !== '' && preg_match('/^[a-z][a-z0-9_]*$/', $sourceToken) && strpos($source, '/') === false) {
    return;
}

if (!isset($params['PROPERTY']) || $params['PROPERTY'] == 'color') {
    $hex = preg_replace('/[^0-9a-f]/i', '', (string)$color);
    $hex = str_pad(substr(strtolower($hex), 0, 6), 6, '0');
    if ($hex != '000000') {
        $ar = str_split($hex, 2);
        $max = max(hexdec($ar[0]), hexdec($ar[1]), hexdec($ar[2]));
        if ($max >= 8) {
            $normalized = sprintf(
                '%02x%02x%02x',
                (int)round(hexdec($ar[0]) * 255 / $max),
                (int)round(hexdec($ar[1]) * 255 / $max),
                (int)round(hexdec($ar[2]) * 255 / $max)
            );
            $this->setProperty('colorSaved', $normalized);
        }
    }
}
