<?php

/*
 $tm=time();
 $this->setProperty('updated', $tm);
 $this->setProperty('updatedText', date('H:i', $tm));
*/

 if ($this->getProperty('level')>0) {
  $this->setProperty('status', 1);
 } else {
  $this->setProperty('status', 0);
 }