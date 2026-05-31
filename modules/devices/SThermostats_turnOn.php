<?php

$this->setProperty('status', 1);
if ($this->getProperty('disabled')) {
    $this->callMethod('enable');
}
