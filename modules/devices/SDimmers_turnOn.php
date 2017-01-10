<?php

 $this->setProperty('status', 1);

 if ($this->getProperty('level')==0) {
  $this->setProperty('level', 100);
 } else {
  $this->setProperty('level', $this->getProperty('level'));
 }