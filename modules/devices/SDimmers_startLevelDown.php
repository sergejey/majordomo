<?php

$ot = $this->object_title;
$timer_name = $ot.'_level_change';
clearTimeOut($timer_name);

$currentLevel = (int)$this->getProperty('level');
$newLevel = $currentLevel-10;
if ($newLevel<10) {
    $newLevel = 10;
}
$this->callMethod('setLevel',array('value'=>$newLevel));

if ($newLevel>10) {
    setTimeOut($timer_name,"callMethod('$ot.startLevelDown');",1);
}