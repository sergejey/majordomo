<?php

/**
 * Summary of registerEvent
 * @param mixed $eventName Event name
 * @param mixed $details Details (default '')
 * @param mixed $expire_in Expire time (default 365)
 * @return mixed
 */
function registerEvent($eventName, $details = '', $expire_in = 0)
{
    include_once(DIR_MODULES . 'events/events.class.php');
    $events = new events();
    return $events->registerEvent($eventName, $details, $expire_in);
}

/**
 * Summary of registeredEventTime
 * @param mixed $eventName Event name
 * @return mixed
 */
function registeredEventTime($eventName)
{
    $sqlQuery = "SELECT ID, UNIX_TIMESTAMP(ADDED) AS TM
                  FROM events
                 WHERE EVENT_TYPE = 'system'
                   AND EVENT_NAME = '" . DBSafe($eventName) . "'
                 ORDER BY ADDED DESC
                 LIMIT 1";

    $rec = SQLSelectOne($sqlQuery);

    if ($rec['ID']) {
        return $rec['TM'];
    } else {
        return -1;
    }
}

/**
 * Summary of registeredEventDetails
 * @param mixed $eventName Event name
 * @return mixed
 */
function registeredEventDetails($eventName) {
    $event = SQLSelectOne("SELECT DETAILS FROM events WHERE EVENT_NAME='" . $eventName . "'"); 
    if (is_array($event)) {
        return $event['DETAILS'];
    } else {
        return false;
    }
}
