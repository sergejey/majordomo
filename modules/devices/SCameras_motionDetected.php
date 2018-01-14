<?php

$ot = $this->object_title;

$this->setProperty('status', 1);
$this->setProperty('activeHTML', $this->getProperty('previewHTML'));

$motion_timeout=20; // seconds timeout
setTimeout($ot.'_motion_timer', 'setGlobal("'.$ot.'.status", 0);setGlobal("'.$ot.'.activeHTML", '');', $motion_timeout);

$this->callMethod('logicAction');