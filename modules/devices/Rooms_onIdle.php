<?php

if ($this->getProperty('turnOffLightsOnIdle')) {
    $turnedOff = array();
    $objects = getObjectsByProperty('linkedRoom', $this->object_title);
    foreach ($objects as $obj) {
        if (gg($obj . '.loadType') == 'light' && gg($obj . '.status')) {
            $turnedOff[] = $obj;
            callMethod($obj . '.turnOff');
        }
    }
    $this->setProperty('turnedOffAutomatically', implode(',', $turnedOff));
}

$this->setProperty('SomebodyHere', 0);
$this->callMethod('updateActivityStatus');