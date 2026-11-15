<?php

// Linked-module polls set SOURCE to the module name — ignore those as user intent.
$source = isset($params['SOURCE']) ? (string)$params['SOURCE'] : '';
$sourceToken = strtolower(trim(strtok(str_replace(',', ' ', $source), ' ')));
if ($sourceToken !== '' && preg_match('/^[a-z][a-z0-9_]*$/', $sourceToken) && strpos($source, '/') === false) {
    return;
}

if (isset($params['NEW_VALUE']) && isset($params['OLD_VALUE']) && (string)$params['NEW_VALUE'] === (string)$params['OLD_VALUE']) {
    return;
}

$brightness = isset($params['NEW_VALUE']) ? (int)$params['NEW_VALUE'] : (int)$this->getProperty('brightness');
if ($brightness < 0) {
    $brightness = 0;
} elseif ($brightness > 100) {
    $brightness = 100;
}

if ($brightness <= 0) {
    if ($this->getProperty('status')) {
        $this->callMethodSafe('turnOff');
    }
    return;
}

$colorSaved = preg_replace('/[^0-9a-f]/i', '', (string)$this->getProperty('colorSaved'));
$colorSaved = str_pad(substr(strtolower($colorSaved), 0, 6), 6, '0');
$ar = str_split($colorSaved, 2);
$max = max(hexdec($ar[0]), hexdec($ar[1]), hexdec($ar[2]));
if ($max < 8) {
    $colorSaved = 'ffffff';
    $this->setProperty('colorSaved', $colorSaved);
} elseif ($max < 255) {
    $colorSaved = sprintf(
        '%02x%02x%02x',
        (int)round(hexdec($ar[0]) * 255 / $max),
        (int)round(hexdec($ar[1]) * 255 / $max),
        (int)round(hexdec($ar[2]) * 255 / $max)
    );
    $this->setProperty('colorSaved', $colorSaved);
}

$ar = str_split($colorSaved, 2);
$factor = $brightness / 100;
$color = sprintf(
    '%02x%02x%02x',
    (int)round(hexdec($ar[0]) * $factor),
    (int)round(hexdec($ar[1]) * $factor),
    (int)round(hexdec($ar[2]) * $factor)
);

if (!$this->getProperty('status')) {
    $this->setProperty('status', 1);
}

$this->setProperty('color', $color);
