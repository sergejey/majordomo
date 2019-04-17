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
  $this->setProperty('levelSaved',$level);
  if (!$this->getProperty('status')) {
   $this->setProperty('status', 1, false);
  }
  if ($minWork!=$maxWork) {
   DebMes("Level updated to ".$level,'dimming');
   $levelWork=round($minWork+round(($maxWork-$minWork)*$level/100));
   if ($this->getProperty('levelWork')!=$levelWork) {
    DebMes("Setting new levelWork to ".(int)$levelWork,'dimming');
    $this->setProperty('levelWork',(int)$levelWork);
   }
  }
 } else {
  if ($this->getProperty('status')) {
   $this->setProperty('status', 0);
  }
  $this->setProperty('levelWork',(int)$minWork);
 }