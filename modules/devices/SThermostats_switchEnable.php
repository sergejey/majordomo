<?php

if ($this->getProperty('disabled')) {
  $this->callMethod('enable');
} else {
  $this->callMethod('disable');
}