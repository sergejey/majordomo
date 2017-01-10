<?php 

$currentStatus=$this->getProperty('status');
if ($currentStatus) {
 $this->callmethod('turnOff');
} else {
 $this->callmethod('turnOn');
}