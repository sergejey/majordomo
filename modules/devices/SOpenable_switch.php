<?php

$status = $this->getProperty('status');
if ($status) {
    $this->callMethod('open');
} else {
    $this->callMethod('close');
}