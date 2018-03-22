<?php

$ot = $this->object_title;

$status = $this->getProperty('status');
$currentValue = $this->getProperty('value');
$min_value = (float)$this->getProperty('minValue');
if (!$min_value) {
    $min_value=1;
}

$timer=$ot.'_turned_off';

if ($currentValue>=$min_value) {
    clearTimeout($timer);
    if (!$status) {
        $this->setProperty('status',1);
        $this->callMethod('loadStatusChanged',array('status'=>1));
    }
} elseif ($currentValue<$min_value) {
    if ($status) {
        setTimeout($timer,'setGlobal("'.$ot.'.status",0);callMethod("'.$ot.'.loadStatusChanged",array("status"=>0));',10);
    }
}