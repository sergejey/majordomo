<?php

$ot = $this->object_title;
$blocked_timeout=$ot.'_blocked';

clearTimeOut($blocked_timeout);
$this->setProperty('blocked',0);