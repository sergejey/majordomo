<?php

$this->setProperty('status', 0);

if ($this->getProperty('disabled')) {
    $this->callMethod('enable');
}

