<?php

$ot = $this->object_title;

if (empty($this->getProperty('alive'))) {
    $this->setProperty('alive', 1);
}


$alive_timeout_raw = $this->getProperty('aliveTimeout');
if ($alive_timeout_raw === '' || $alive_timeout_raw === false || $alive_timeout_raw === null) {
    $alive_timeout_raw = 48; // 2 days by default when the property is not set
}
$alive_timeout = (int)$alive_timeout_raw * 60 * 60;

// An explicit 0 disables the timer; the device stays online until another
// activity updates it.
if ($alive_timeout > 0) {
  setTimeout($ot . '_alive_timer', 'setGlobal("' . $ot . '.alive", 0);', $alive_timeout);
}
