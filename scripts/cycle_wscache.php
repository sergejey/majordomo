<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");

if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS == 1) {
    echo "Web-sockets disabled\n";
    exit;
}

SQLTruncateTable('cached_ws');
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$checked_time = 0;
$latest_sent = time();
setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
$cycleVarName = 'ThisComputer.' . str_replace('.php', '', basename(__FILE__)) . 'Run';
if (defined('SETTINGS_SYSTEM_WEBSOCKETS_RESTART_TIMEOUT') && (int)SETTINGS_SYSTEM_WEBSOCKETS_RESTART_TIMEOUT >= 0) {
    $websocket_restart_timeout = (int)SETTINGS_SYSTEM_WEBSOCKETS_RESTART_TIMEOUT;
} else {
    $websocket_restart_timeout = 0;
}

if (defined('WEBSOCKETS_QUEUE_LIMIT') && (int)WEBSOCKETS_QUEUE_LIMIT > 0) {
    $websocket_queue_limit = (int)WEBSOCKETS_QUEUE_LIMIT;
} else {
    $websocket_queue_limit = 500;
}

clearTimeout('restartWebSocket');

while (1) {
    if ($checked_time != time()) {
        $checked_time = time();
        try {
            $queue = SQLSelect("SELECT * FROM cached_ws ORDER BY ADDED LIMIT " . $websocket_queue_limit);
            if (is_array($queue) && !empty($queue)) {
                $total = count($queue);
                $sent_ok = 1;
                $properties = array();
                $values = array();
                $post_property_keys = array();

                for ($i = 0; $i < $total; $i++) {
                    $row = $queue[$i];
                    $property = $row['PROPERTY'] ?? '';
                    $postAction = $row['POST_ACTION'] ?? 'PostProperty';
                    $dataValue = $row['DATAVALUE'] ?? '';

                    if ($property === '') {
                        continue;
                    }

                    if ($postAction == 'PostProperty') {
                        $decoded = json_decode($dataValue, true);
                        if (is_array($decoded)) {
                            $dataValue = $decoded;
                        }
                        $properties[] = $property;
                        $values[] = $dataValue;
                        $post_property_keys[] = $property;
                        continue;
                    }

                    $decoded = json_decode($dataValue, true);
                    if (is_array($decoded)) {
                        $dataValue = $decoded;
                    }

                    $sent = postToWebSocket($property, $dataValue, $postAction);
                    if ($sent) {
                        SQLExec("DELETE FROM cached_ws WHERE PROPERTY='" . DBSafe($property) . "'");
                    } else {
                        $sent_ok = 0;
                    }
                }

                if (count($properties) > 0) {
                    $sent = postToWebSocket($properties, $values, 'PostProperty');
                    if ($sent) {
                        foreach ($post_property_keys as $property) {
                            SQLExec("DELETE FROM cached_ws WHERE PROPERTY='" . DBSafe($property) . "'");
                        }
                    } else {
                        $sent_ok = 0;
                    }
                }

                if ($sent_ok) {
                    $latest_sent = time();
                    // saveToCache("MJD:$cycleVarName", $latest_sent);
                    setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $latest_sent, 1);
                    if ($websocket_restart_timeout > 0) {
                        setTimeout('restartWebSocket', 'sg("cycle_websocketsRun","");sg("cycle_websocketsControl","restart");', $websocket_restart_timeout);
                    } else {
                        clearTimeout('restartWebSocket');
                    }
                } else {
                    echo date("H:i:s") . ' Error while posting to websocket.' . "\n";
                }
            }
            unset($queue, $properties, $values, $post_property_keys);
        } catch (Throwable $e) {
            DebMes('cycle_wscache error: ' . $e->getMessage(), 'websockets');
            echo date("H:i:s") . ' cycle_wscache exception: ' . $e->getMessage() . "\n";
        }
    }
    if (isRebootRequired() || isset($_GET['onetime'])) {
        exit;
    }
    sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
