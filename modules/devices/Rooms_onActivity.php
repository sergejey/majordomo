<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES == 1) return;

$ot = $this->object_title;

//$latestActivity = $this->getProperty('LatestActivity');
$this->setProperty('LatestActivity', time());
$this->setProperty('LatestActivityTime', date('H:i'));

if (!$this->getProperty('SomebodyHere')) {
    $this->setProperty('SomebodyHere', 1);
    $this->callMethodSafe('updateActivityStatus');
}

if ($this->getProperty('IdleDelay')) {
    $activity_timeout = (int)$this->getProperty('IdleDelay');
} else {
    $activity_timeout = 10 * 60;
}
setTimeOut($ot . '_activity_timeout', "callMethod('" . $ot . ".onIdle');", $activity_timeout);

if (getGlobal('NobodyHomeMode.active')) {
    callMethod('NobodyHomeMode.deactivate', array('sensor' => $params['sensor'], 'room' => $ot));
}

if ($this->getProperty('turnOffLightsOnIdle') &&
    $this->getProperty('turnedOffAutomatically') &&
    gg('DarknessMode.active')) {
    $turnedOff = explode(',', $this->getProperty('turnedOffAutomatically'));
    $this->setProperty('turnedOffAutomatically', '');
    foreach ($turnedOff as $obj) {
        if (!gg($obj . '.status')) {
            callMethod($obj . '.turnOn');
            usleep(50000);
        }
    }
}

