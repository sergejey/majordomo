<?php

$level = $this->getProperty('level');
$status = $this->getProperty('status');

if ($level>1) {
    $this->setProperty('levelSaved',$level);
}

if ($level > 0 && $status) {
    $this->setProperty('status',0);
} elseif ($level==0 && !$status) {
    $this->setProperty('status',1);
}
