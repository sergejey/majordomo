<?php

$status = $this->getProperty('status');

$tm=time();
$this->setProperty('updated', $tm);
$this->setProperty('alive', 1);
$group_name=$this->getProperty('groupName');
$objects = getObjectsByProperty('group'.$group_name,1);
foreach($objects as $object_title) {
    sg($object_title.'.status',$status);
}