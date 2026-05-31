<?php

$ot = $this->object_title;

if (empty($this->getProperty('alive'))) {
    $this->setProperty('alive', 1);
}


$alive_timeout = (int)$this->getProperty('aliveTimeout') * 60 * 60;
if ($alive_timeout==0) {
    $alive_timeout = 2 * 24 * 60 * 60; // 2 days alive timeout by default
}

if ($alive_timeout>0) {
  setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);
}
