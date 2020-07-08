<?php

$color = $this->getProperty('color');
if ($color != '000000') $this->setProperty('colorSaved', $this->getProperty('color'));
$this->setProperty('color', '000000');
$this->setProperty('status', 0);