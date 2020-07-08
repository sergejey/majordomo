<?php

if ($this->getProperty('status')) {
  $this->setProperty('status', 0);
} else {
  $this->setProperty('status', 1);
}
