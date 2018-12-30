<?php 

$currentStatus=$this->getProperty('status');
if ($currentStatus) {
 $this->callMethodSafe('turnOff');
} else {
 $this->callMethodSafe('turnOn');
}
