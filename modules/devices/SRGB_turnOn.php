<?php

$this->setProperty('status', 1);

$colorSaved = $this->getProperty('colorSaved');
if ($colorSaved && $colorSaved != '000000') {
    $this->setProperty('color', $colorSaved);
} else {
    $this->setProperty('color', 'ffffff');
}