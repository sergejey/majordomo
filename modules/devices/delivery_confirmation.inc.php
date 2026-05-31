<?php

$property_name = $params['PROPERTY'];
//DebMes("Event statusUpdated: " . json_encode($params), 'confirmation/'.$this->object_title);
$timer_name = $this->object_title . '_confirmation_timer';
clearTimeOut($timer_name);
if (isset($params['NO_LINKED']) && is_array($params['NO_LINKED']) && $params['NO_LINKED']) {
    // confirmation received
    //DebMes("Confirmation received for " . $property_name . " update", 'confirmation/'.$this->object_title);
} elseif (!isset($params['NO_LINKED']) || (is_array($params['NO_LINKED']) && !$params['NO_LINKED']) || $params['NO_LINKED'] == 0) {
    // set
    //DebMes("Initial set of " . $property_name . " to " . $params['NEW_VALUE'], 'confirmation/'.$this->object_title);
    $attempt = 0;
    $saved = $params['OLD_VALUE'];
    $new_value = $params['NEW_VALUE'];
    if (isset($params['SOURCE']) && preg_match('/saved\|(.+)\|attempt(\d+)/is', $params['SOURCE'], $m)) {
        $attempt = (int)$m[2];
        $saved = $m[1];
    }
    $attempt++;
    if ($attempt > 1) {
        //DebMes("Restoring original value of " . $this->object_title . "." . $property_name . " to $saved", 'confirmation/'.$this->object_title);
        //setGlobal($this->object_title . "." . $property_name, $saved, 1);
    }
    if ($attempt <= 5) {
        $timer_source = 'saved|' . $saved . '|attempt' . $attempt;
        //DebMes("Timer source: " . $timer_source, 'confirmation/'.$this->object_title);
        //DebMes("Setting time for new attempt: " . "setGlobal('" . $this->object_title . "." . $property_name . "', '" . $new_value . "', 0, '$timer_source');", 'confirmation/'.$this->object_title);
        setTimeOut($timer_name, "setGlobal('" . $this->object_title . "." . $property_name . "', '" . $new_value . "', 0, '$timer_source');", 5);
    } else {
        //DebMes("Delivery failed for " .$this->object_title . "." . $property_name . " update", 'confirmation/'.$this->object_title);
    }
}