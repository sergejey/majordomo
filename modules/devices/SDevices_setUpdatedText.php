<?php

$ot = $this->object_title;
$updatedTime = $this->getProperty('updated');
$passed = time() - $updatedTime;
$newTimeout=0;

$passedText = getPassedText($updatedTime);
$this->setProperty('updatedText',$passedText);

if ($passed<10) {
    $newTimeout = 10;
} elseif ($passed<60) {
    $newTimeout = 10;
} elseif ($passed<60*60) {
    $newTimeout = 60;
} elseif ($passed<20*60*60) {
    $newTimeout = 60 * 60;
}

if ($newTimeout > 0) {
    setTimeOut($ot.'_updateTime','callMethod("'.$ot.'.setUpdatedText");',$newTimeout);
}