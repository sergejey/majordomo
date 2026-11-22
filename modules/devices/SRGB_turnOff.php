<?php

$color = $this->getProperty('color');
if ($color != '000000') {
    $hex = preg_replace('/[^0-9a-f]/i', '', (string)$color);
    $hex = str_pad(substr(strtolower($hex), 0, 6), 6, '0');
    $ar = str_split($hex, 2);
    $max = max(hexdec($ar[0]), hexdec($ar[1]), hexdec($ar[2]));
    if ($max >= 8) {
        // Store full-brightness base so turnOn can restore the last color
        $normalized = sprintf(
            '%02x%02x%02x',
            (int)round(hexdec($ar[0]) * 255 / $max),
            (int)round(hexdec($ar[1]) * 255 / $max),
            (int)round(hexdec($ar[2]) * 255 / $max)
        );
        $this->setProperty('colorSaved', $normalized);
    }
}

$bri = (int)$this->getProperty('brightness');
if ($bri > 0) {
    $this->setProperty('brightnessSaved', $bri);
}

// Do not push color=000000 — some backends (e.g. Magichome) forget last RGB if wiped.
$this->setProperty('status', 0);
