<?php

$status = $this->getProperty('status');
$level=$this->getProperty('level');
$levelSaved=$this->getProperty('levelSaved');
if ($status>0 && !$level && $levelSaved) {
    $this->setProperty('level',$levelSaved);
}