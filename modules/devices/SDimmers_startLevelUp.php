<?php

$ot = $this->object_title;
$timer_name = $ot.'_level_change';
clearTimeOut($timer_name);

$currentLevel = (int)$this->getProperty('level');
$newLevel = $currentLevel+10;
if ($newLevel>100) {
    $newLevel = 100;
}
$this->callMethod('setLevel',array('value'=>$newLevel));

if ($newLevel<100) {
    setTimeOut($timer_name,"callMethod('$ot.startLevelUp');",1);
}
