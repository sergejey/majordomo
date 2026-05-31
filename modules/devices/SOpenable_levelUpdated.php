<?php

$level = $this->getProperty('level');
$minWork = $this->getProperty('minWork');
$maxWork = $this->getProperty('maxWork');
$status = $this->getProperty('status');

if ($level > 1) {
    $this->setProperty('levelSaved', $level);
}

$statusUpdated = 0;
if ($level > 0 && $status) {
    $statusUpdated = 1;
    $this->setProperty('status', 0);
} elseif ($level == 0 && !$status) {
    $statusUpdated = 1;
    $this->setProperty('status', 1);
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
    $dv->checkLinkedDevicesAction($this->object_title, $params);
}