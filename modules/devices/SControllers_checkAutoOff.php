<?php
$ot=$this->object_title;
$linked_room=$this->getProperty('linkedRoom');
if ($linked_room)
{
    $activity = gg($linked_room.'.SomebodyHere');
    if ($activity){
         $timeout_off=(int)$this->getProperty('autoOffEcoValue');
         if ($timeout_off) {
             $timeout_off = $timeout_off * 60;
             setTimeOut($ot.'_checkAutoOff','callMethod("'.$ot.'.checkAutoOff");',$timeout_off); 
         }
         else
            $this->setProperty('status', 0);
    }
    else
        $this->setProperty('status', 0);
}
else
    $this->setProperty('status', 0);