<?php

$this->setProperty('status', 1);

$colorSaved = preg_replace('/[^0-9a-f]/i', '', (string)$this->getProperty('colorSaved'));
$colorSaved = str_pad(substr(strtolower($colorSaved), 0, 6), 6, '0');
$ar = str_split($colorSaved, 2);
$max = max(hexdec($ar[0]), hexdec($ar[1]), hexdec($ar[2]));

// Near-black saved color is treated as broken (e.g. polling stuck dim RGB).
if ($max < 8) {
    $colorSaved = 'ffffff';
    $this->setProperty('colorSaved', $colorSaved);
} else {
    // Keep colorSaved at full brightness; output is scaled by brightness.
    $colorSaved = sprintf(
        '%02x%02x%02x',
        (int)round(hexdec($ar[0]) * 255 / $max),
        (int)round(hexdec($ar[1]) * 255 / $max),
        (int)round(hexdec($ar[2]) * 255 / $max)
    );
    $this->setProperty('colorSaved', $colorSaved);
}

$brightness = (int)$this->getProperty('brightness');
if ($brightness <= 0) {
    $brightness = (int)$this->getProperty('brightnessSaved');
}
if ($brightness <= 0) {
    $brightness = 100;
}
if ($brightness > 100) {
    $brightness = 100;
}
$this->setProperty('brightness', $brightness);

$ar = str_split($colorSaved, 2);
$factor = $brightness / 100;
$color = sprintf(
    '%02x%02x%02x',
    (int)round(hexdec($ar[0]) * $factor),
    (int)round(hexdec($ar[1]) * $factor),
    (int)round(hexdec($ar[2]) * $factor)
);

$this->setProperty('color', $color);
