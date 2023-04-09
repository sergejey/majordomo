<?php

$level = $this->getProperty('level');
$minWork = $this->getProperty('minWork');
$maxWork = $this->getProperty('maxWork');
$levelWork = $this->getProperty('levelWork');

$statusUpdated = 0;

if ($level > 0) {
    $this->setProperty('levelSaved', $level);
    if (!$this->getProperty('status')) {
        $statusUpdated = 1;
        $this->setProperty('status', 1, false);
    }
} else {
    if ($this->getProperty('status')) {
        $statusUpdated = 1;
        $this->setProperty('status', 0);
    }
}

if ($minWork != $maxWork) {
    $levelWork = round($minWork + round(($maxWork - $minWork) * $level / 100));
    $diff = abs($this->getProperty('levelWork') - $levelWork);
    if ($diff >= 5) {
        $this->setProperty('levelWork', $levelWork);
    }
}


if (!$statusUpdated) {
    $this->callMethod('logicAction');
    include_once(dirname(__FILE__) . '/devices.class.php');
    $dv = new devices();
    $dv->checkLinkedDevicesAction($this->object_title, $level);
}
