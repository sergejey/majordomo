<?php

if (isset($params['VALUE']) && !$params['VALUE']) {
    return false;
}

$pulseAmount = $this->getProperty('pulseAmount');
if (!$pulseAmount) return false;

$value = $this->getProperty('valueWork');
$value += $pulseAmount;
$this->setProperty('valueWork',$value);