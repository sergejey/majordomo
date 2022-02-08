<?php

$this->setProperty('status', 0);
$switchLevel=$this->getProperty('switchLevel');
if (!$switchLevel)
    $this->setProperty('level', 0);