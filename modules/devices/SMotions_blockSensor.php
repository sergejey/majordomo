<?php

$ot = $this->object_title;
$blocked_timeout=$ot.'_blocked';

if (isset($params['timeout'])) {
    $timeout = $params['timeout'];
} else {
    $timeout = 1 * 60 * 60; // 1 hour timeout
}

$this->setProperty('blocked',1);
setTimeOut($blocked_timeout,'cm("'.$ot.'.unblockSensor");',$timeout);
