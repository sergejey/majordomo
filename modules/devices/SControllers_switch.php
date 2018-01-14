<?php 

$currentStatus=$this->getProperty('status');
if ($currentStatus) {
 $this->callmethodSafe('turnOff');
} else {
 $this->callmethodSafe('turnOn');
}