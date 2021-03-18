<?php

//say("Setting level to ".$params['value']);
if (!isset($params['value'])) return;

$new_level = $params['value'];

if ($new_level>=0 && $new_level<=100) {
    $this->setProperty('level',$new_level);
}