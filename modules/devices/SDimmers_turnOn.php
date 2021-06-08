<?php

$this->setProperty('status', 1);
$switchLevel=$this->getProperty('switchLevel');
if (!$switchLevel)
{
    $levelSaved=$this->getProperty('levelSaved');
    if ($levelSaved>0) {
      $this->setProperty('level', $levelSaved);
    } else {
      $this->setProperty('level', 100);
    }
}