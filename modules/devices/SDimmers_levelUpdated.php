<?php

/*
 $tm=time();
 $this->setProperty('updated', $tm);
 $this->setProperty('updatedText', date('H:i', $tm));
*/

 $level=$this->getProperty('level');
 $minWork=$this->getProperty('minWork');
 $maxWork=$this->getProperty('maxWork');

 if ($level>0) {
  $this->setProperty('status', 1);
  if ($minWork!=$maxWork) {
   $levelWork=$minWork+round(($maxWork-$minWork)*$level/100);
   $this->setProperty('levelWork',(int)$levelWork,true);
  }
 } else {
  $this->setProperty('status', 0);
  $this->setProperty('levelWork',(int)$minWork,true);
 }