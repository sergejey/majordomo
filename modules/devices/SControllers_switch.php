<?php 

if ($this->getProperty('status')) {
 $this->callmethodSafe('turnOff');
} else {
 $this->callmethodSafe('turnOn');
}