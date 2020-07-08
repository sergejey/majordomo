<?php

/**
 * Summary of subscribeToEvent
 * @param mixed $module_name Module name
 * @param mixed $event_name Event name
 * @param mixed $filter_details Filter details (default '')
 * @return void
 */
function subscribeToEvent($module_name, $event_name, $filter_details = '', $priority = 0)
{
    $rec = SQLSelectOne("SELECT * FROM settings WHERE NAME = 'HOOK_EVENT_" . DBSafe(strtoupper($event_name)) . "'");

    if (!$rec['ID']) {
        $rec = array();
        $rec['NAME'] = 'HOOK_EVENT_' . strtoupper($event_name);
        $rec['TITLE'] = $rec['NAME'];
        $rec['TYPE'] = 'json';
        $rec['NOTES'] = '';
        $rec['DATA'] = '';
        $rec['PRIORITY'] = 0;
        $rec['ID'] = SQLInsert('settings', $rec);
    }

    $data = json_decode($rec['VALUE'], true);
    if (!isset($data[$module_name])) {
        $data[$module_name] = array();
    } else {
        return;
    }
    $data[$module_name]['filter'] = $filter_details;
    if ($priority) {
        $data[$module_name]['priority'] = $priority;
    }
    $rec['VALUE'] = json_encode($data);
    SQLUpdate('settings', $rec);
}

/**
 * Summary of unsubscribeFromEvent
 * @param mixed $module_name Module name
 * @param mixed $event_name Event name (default '')
 * @return void
 */
function unsubscribeFromEvent($module_name, $event_name = '')
{
    $sqlQuery = "SELECT *
                  FROM settings
                 WHERE NAME LIKE 'HOOK_EVENT_" . DBSafe(strtoupper($event_name)) . "'
                   AND TYPE = 'json'";

    $rec = SQLSelectOne($sqlQuery);

    if (isset($rec['ID'])) {
        $data = json_decode($rec['VALUE'], true);

        if (isset($data[$module_name])) {
            unset($data[$module_name]);
            $rec['VALUE'] = json_encode($data);
            SQLUpdate('settings', $rec);
        }
    }
}

function processSubscriptionsSafe($event_name, $details = '')
{
    //processSubscriptions($event_name,$details);
    if (!is_array($details)) {
        $details = array();
    }
    $data = array(
        'processSubscriptions' => 1,
        'event' => $event_name,
        'params' => json_encode($details),

    );
    if (session_id()) {
        $data[session_name()] = session_id();
    }
    $url = BASE_URL . ROOTHTML . 'objects/?';
    postURLBackground($url, $data);
    return 1;
}

function processSubscriptionByModule($module_name,$event_name, &$details) {
    $modulePath = DIR_MODULES . $module_name . '/' . $module_name . '.class.php';
    $output = '';
    if (file_exists($modulePath)) {
        include_once($modulePath);
        $module_object = new $module_name();
        if (method_exists($module_object, 'processSubscription')) {
            //DebMes("$module_name.processSubscription ($event_name)",'process_subscription');
            verbose_log("Processing subscription to [" . $event_name . "] by [" . $module_name . "] (" . (is_array($details) ? json_encode($details) : '') . ")");
            try {
                $output = $module_object->processSubscription($event_name, $details);
            } catch (Exception $e) {
                //DebMes('Error in processing "%s": ' . $e->getMessage(), 'process_subscription');
            }
            //DebMes("$module_name.processSubscription ($event_name) DONE",'process_subscription');
        } else {
            //DebMes("$module_name.processSubscription error (method not found)", 'process_subscription');
        }
        unset($module_object);
    } else {
        //DebMes("$module_name.processSubscription error (module class not found)", 'process_subscription');
    }
    return $output;
}

/**
 * Summary of processSubscriptions
 * @param mixed $event_name Event name
 * @param mixed $details Details (default '')
 * @return int|void
 */
function processSubscriptions($event_name, $details = '', $return_output = false)
{
    if (!$return_output) {
        postToWebSocketQueue($event_name, $details, 'PostEvent');
    }
    if (!is_array($details)) {
        $details = array();
    } 
    if (!defined('SETTINGS_HOOK_EVENT_' . strtoupper($event_name))) {
        return 0;
    }
    $data = json_decode(constant('SETTINGS_HOOK_EVENT_' . strtoupper($event_name)), true);
    if (is_array($data)) {
        $data2 = array();
        foreach ($data as $k => $v) {
            $data2[] = array('module' => $k, 'filter' => $v['filter'], 'priority' => (int)$v['priority']);
        }
        usort($data2, function ($a, $b) {
            if ($a['priority'] == $b['priority']) return 0;
            return ($a['priority'] > $b['priority']) ? -1 : 1;
        });
        $output = '';
        $total = count($data2);
        for ($i = 0; $i < $total; $i++) {
            $module_name = $data2[$i]['module'];
            $filter_details = $data2[$i]['filter'];
            $output .= processSubscriptionByModule($module_name,$event_name,$details);
            if (!isset($details['BREAK'])) {
                $details['BREAK'] = false;
            }
            if ($details['BREAK']) break;
        }

        if (!isset($details['PROCESSED'])) {
            $details['PROCESSED'] = false;
        }
        //if (!$details['PROCESSED'] && $event_name == 'COMMAND') { sayReplySafe(LANG_DEVICES_UNKNOWN_COMMAND,2);}
        if ($return_output) {
            return $output;
        } else {
            return (int)$details['PROCESSED'];
        }
    }
    return 0;

}

/**
 * Summary of removeMissingSubscribers
 * @return void
 */
function removeMissingSubscribers()
{
    $settings = SQLSelect("SELECT * FROM settings WHERE NAME LIKE 'HOOK_EVENT_%' AND TYPE='json'");
    $total = count($settings);

    for ($i = 0; $i < $total; $i++) {
        $data = json_decode($settings[$i]['VALUE'], true);
        $changed = 0;

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $module_name = $k;
                if (!file_exists(DIR_MODULES . $module_name . '/' . $module_name . '.class.php')) {
                    unset($data[$module_name]);
                    $changed = 1;
                }
            }

            if ($changed) {
                $settings[$i]['VALUE'] = json_encode($data);
                SQLUpdate('settings', $settings[$i]);
            }
        }
    }
}
