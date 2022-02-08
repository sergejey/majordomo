<?php

$this->callMethod("updateActivityStatus");

if ($this->getProperty('turnOffLightsOnIdle')) {
    $objects = getObjectsByProperty('linkedRoom',$this->object_title);
    foreach($objects as $obj) {
        if (gg($obj.'.loadType')=='light' && gg($obj.'.status')) {
            callMethod($obj.'.turnOff');
        }
    }
}