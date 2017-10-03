<?php

@include_once(ROOT . 'languages/devices_' . SETTINGS_SITE_LANGUAGE . '.php');
@include_once(ROOT . 'languages/devices_default' . '.php');

$ot = $this->object_title;
$updatedTime = $this->getProperty('updated');
$passed = time() - $updatedTime;
$newTimeout=0;

if ($passed<10) {
    $newTimeout = 10;
    $this->setProperty('updatedText',LANG_DEVICES_PASSED_NOW);
} elseif ($passed<60) {
    $newTimeout = 10;
    $this->setProperty('updatedText',$passed.' '.LANG_DEVICES_PASSED_SECONDS_AGO);
} elseif ($passed<60*60) {
    $newTimeout = 60;
    $this->setProperty('updatedText',round($passed/60).' '.LANG_DEVICES_PASSED_MINUTES_AGO);
/*
} elseif ($passed<12*60*60) {
    $newTimeout = 60 * 60;
    $this->setProperty('updatedText',round($passed/60/60).' '.LANG_DEVICES_PASSED_HOURS_AGO);
*/
} elseif ($passed<20*60*60) {
    //just time
    $newTimeout = 60 * 60;
    $this->setProperty('updatedText',date('H:i',$updatedTime));
} else {
    //time and date
    $this->setProperty('updatedText',date('d.M.Y H:i',$updatedTime));//
}

if ($newTimeout > 0) {
    setTimeOut($ot.'_updateTime','callMethod("'.$ot.'.setUpdatedText");',$newTimeout);
}