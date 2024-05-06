<?php

$linked_room=$this->getProperty('linkedRoom');

if ($params['PROPERTY'] == "valueHumidity"){
	$ot=$this->object_title;
	$description = $this->description;
	if (!$description) {
		$description = $ot;
	}
	
	$directionTimeout=(int)$this->getProperty('directionTimeout');
	if (!$directionTimeout) {
		$directionTimeout=1*60*60;
	}
		$data1 = getHistoryValue($this->object_title.".valueHumidity", time()-$directionTimeout);
	$direction = 0;
	if ($data1>$value) {
		$direction=-1;
	} elseif ($data1<$value) {
		$direction=1;
	}
	$currentDirection = $this->getProperty('directionHumidity');
	if ($currentDirection != $direction) {
		$this->setProperty('directionHumidity',$direction);
	}
	
	$is_blocked=(int)$this->getProperty('blocked');
	if ($is_blocked) {
		return;
	}
	
	$value=(float)$this->getProperty('valueHumidity');
	$minValue=(float)$this->getProperty('minHumidityValue');
	$maxValue=(float)$this->getProperty('maxHumidityValue');
	$is_normal=(int)$this->getProperty('normalHumidityValue');

	
	if ($maxValue==0 && $minValue==0 && !$is_normal) {
		$this->setProperty('normalHumidityValue', 1);
	} elseif (($value>$maxValue || $value<$minValue) && $is_normal) {
		$this->setProperty('normalHumidityValue', 0);
		if ($this->getProperty('notify')) {
			//out of range notify
			say(LANG_DEVICES_NOTIFY_OUTOFRANGE. ' ('.$description.' '.$value.')', 2);
		}
	} elseif (($value<=$maxValue && $value>=$minValue) && !$is_normal) {
		$this->setProperty('normalHumidityValue', 1);
		if ($this->getProperty('notify')) {
			//back to normal notify
			say(LANG_DEVICES_NOTIFY_BACKTONORMAL. ' ('.$description.' '.$value.')', 2);
		}
	}
	
	if ($linked_room && $this->getProperty('mainSensor')) {
		sg($linked_room.'.humidity',$this->getProperty('valueHumidity'));
	}
}
else {
	if ($linked_room && $this->getProperty('mainSensor')) {
		sg($linked_room.'.temperature',$this->getProperty('value'));
	}
}
