<?php

$property_name = $params['PROPERTY'];

$timer_name = $this->object_title . '_confirmation_timer';
$timer_exists = timeOutExists($timer_name);
if ($timer_exists) {
    //DebMes("statusUpdated when timer $timer_name exists: " . json_encode($params), 'confirmation/' . $this->object_title);
}

if (isset($params['NO_LINKED']) && is_array($params['NO_LINKED']) && $params['NO_LINKED']) {
    // confirmation received
    if ($timer_exists) {
        clearTimeOut($timer_name);
        DebMes("Confirmation received for " . $property_name . " update:", 'confirmation/' . $this->object_title);
        DebMes(json_encode($params), 'confirmation/' . $this->object_title);
        DebMes("----------------------", 'confirmation/' . $this->object_title);
    }
} elseif (!isset($params['NO_LINKED']) || (is_array($params['NO_LINKED']) && !$params['NO_LINKED']) || $params['NO_LINKED'] == 0) {
    // set
    $attempt = 0;
    $saved = $params['OLD_VALUE'];
    $new_value = $params['NEW_VALUE'];
    if (isset($params['SOURCE']) && preg_match('/a\.(\d+)\.p\.(\w+)\.s\.(.*?)\./is', $params['SOURCE'], $m)) {
        $attempt = $m[1];
        $attempt_property = $m[2];
        $saved = $m[3];
        //DebMes("Attempt [$attempt] to set [" . $attempt_property . "] to [" . $params['NEW_VALUE'] . "] (timer source: " . $m[0] . ")", 'confirmation/' . $this->object_title);
    } else {
        DebMes("Initial set of " . $property_name . " to " . $params['NEW_VALUE'], 'confirmation/' . $this->object_title);
        if ($timer_exists) {
            DebMes("Timer $timer_name already exists (do nothing)", 'confirmation/' . $this->object_title);
            return;
        }
    }
    $attempt++;
    if ($attempt > 1) {
    }
    if ($attempt <= 5) {
        $timer_source = 'a.' . $attempt . '.p.' . $property_name . '.s.' . $saved .'.';
        //DebMes("Timer $timer_name source: " . $timer_source, 'confirmation/' . $this->object_title);
        DebMes("Setting timer $timer_name for new attempt: " . "setGlobal('" . $this->object_title . "." . $property_name . "', '" . $new_value . "', 0, '$timer_source');", 'confirmation/' . $this->object_title);
        setTimeOut($timer_name, "setGlobal('" . $this->object_title . "." . $property_name . "', '" . $new_value . "', 0, '$timer_source');", 5);
    } else {
        DebMes("Delivery failed for " . $this->object_title . "." . $property_name . " update", 'confirmation/' . $this->object_title);
        DebMes("Restoring original value of " . $this->object_title . "." . $property_name . " to $saved", 'confirmation/' . $this->object_title);
        //setGlobal($this->object_title . "." . $property_name, $saved, 1);
        DebMes("----------------------", 'confirmation/' . $this->object_title);
    }
}