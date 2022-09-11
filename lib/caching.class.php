<?php

function saveCycleToCache($key, $value)
{
    if (is_array($value) || strlen($value) > 255) {
        SQLExec("DELETE FROM cached_cycles WHERE TITLE='".$key."'");
        deleteFromCache($key);
        return;
    }

    if (isset($_SERVER['REQUEST_METHOD'])) {
        global $memory_cycle_cache;
        $memory_cycle_cache[$key] = $value;
    }
        $rec = array('TITLE' => $key, 'VALUE' => $value);
    $sqlQuery = "REPLACE INTO cached_cycles (TITLE, VALUE) " .
        " VALUES ('" . DbSafe1($rec['TITLE']) . "', " .
        "'" . DbSafe1($rec['VALUE']) . "')";
    SQLExec($sqlQuery);
}

function checkCycleFromCache($key)
{
    if (isset($_SERVER['REQUEST_METHOD'])) {
            global $memory_cycle_cache;
                if (is_array($memory_cycle_cache) && isset($memory_cycle_cache[$key])) {
                    return $memory_cycle_cache[$key];
                }
        }
        $rec = SQLSelectOne("SELECT * FROM cached_cycles WHERE TITLE = '" . DBSafe($key) . "'");
    if ($rec['TITLE']) {
        return $rec['VALUE'];
    } else {
        return false;
    }
}



/**
 * Summary of clearCacheData
 * @param mixed $prefix prefix
 * @return void
 */
function clearCacheData($prefix = '')
{
    $prefix = strtolower($prefix);
    if (defined('USE_REDIS')) {
        global $redisConnection;
        if (!isset($redisConnection)) {
            $redisConnection = new Redis();
            $redisConnection->pconnect(USE_REDIS);
        }
        if (!$prefix) $redisConnection->flushDB();
        else {
            $list = $redisConnection->getKeys($prefix . "*");
            foreach ($list as $key1)
                $redisConnection->del($key1);
        }
        return;
    }
    if (!$prefix) SQLTruncateTable('cached_values');
    else SQLExec("delete from cached_values where KEYWORD like '$prefix%'");
}

/**
 *  Return all Cache Data from prefix
 * Summary of getAllCache
 * @param mixed $prefix
 * @return array
 */
function getAllCache($prefix = '')
{
    $prefix = strtolower($prefix);
    $out = array();
    if (defined('USE_REDIS')) {
        global $redisConnection;
        if (!isset($redisConnection)) {
            $redisConnection = new Redis();
            $redisConnection->pconnect(USE_REDIS);
        }
        $list = $redisConnection->getKeys($prefix . "*");
        foreach ($list as $key1)
            $out[$key1] = $redisConnection->get($key1);
    } else $out = SQLExec("select * from cached_values where KEYWORD like '$prefix%'");
    return $out;
}


/**
 * Summary of saveToCache
 * @param mixed $key Key
 * @param mixed $value Value
 * @return void
 */
function saveToCache($key, $value)
{
    $key = strtolower($key);
    if (is_array($value) || strlen($value) > 255) {
        deleteFromCache($key);
        return;
    }

    if (defined('USE_REDIS')) {
        global $redisConnection;
        if (!isset($redisConnection)) {
            $redisConnection = new Redis();
            $redisConnection->pconnect(USE_REDIS);
        }
        $redisConnection->set($key, (string)$value);
        return;
    }

    $rec = array('KEYWORD' => $key, 'DATAVALUE' => $value);
    $sqlQuery = "REPLACE INTO cached_values (KEYWORD, DATAVALUE) " .
        " VALUES ('" . DbSafe1($rec['KEYWORD']) . "', " .
        "'" . DbSafe1($rec['DATAVALUE']) . "')";
    SQLExec($sqlQuery);
}

function deleteFromCache($key) {
    SQLExec("DELETE FROM cached_values WHERE KEYWORD='" . $key . "'");
    if (defined('USE_REDIS')) {
        global $redisConnection;
        if (!isset($redisConnection)) {
            $redisConnection = new Redis();
            $redisConnection->pconnect(USE_REDIS);
        }
        if ($redisConnection->exists($key)) {
            $redisConnection->del($key);
        }
    }
}

/**
 * Summary of checkFromCache
 * @param mixed $key Key
 * @return mixed
 */
function checkFromCache($key)
{
    $key = strtolower($key);
    if (defined('USE_REDIS')) {
        global $redisConnection;
        if (!isset($redisConnection)) {
            $redisConnection = new Redis();
            $redisConnection->pconnect(USE_REDIS);
        }
        if ($redisConnection->exists($key)) {
            $value = $redisConnection->get($key);
            return $value;
        } else {
            return false;
        }
    }


    $rec = SQLSelectOne("SELECT * FROM cached_values WHERE KEYWORD = '" . DBSafe($key) . "'");

    if (isset($rec['KEYWORD'])) {
        return $rec['DATAVALUE'];
    } else {
        return false;
    }
}

function postToWebSocketQueue($property, $value, $post_action = 'PostProperty')
{
    if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS == 1) {
        return false;
    }
    //SQLExec("DELETE FROM cached_ws WHERE PROPERTY='" . DBSafe($property) . "'");
    $rec = array();
    $rec['PROPERTY'] = $property;
    $rec['DATAVALUE'] = $value;
    $rec['POST_ACTION'] = $post_action;
    $rec['ADDED'] = date('Y-m-d H:i:s');

    $fields = "";
    $values = "";
    $table = 'cached_ws';

    foreach ($rec as $field => $value) {
        if (is_Numeric($field)) continue;
        $fields .= "`$field`, ";
        $values .= "'" . DBSafe1($value) . "', ";
    }

    $fields = substr($fields, 0, strlen($fields) - 2);
    $values = substr($values, 0, strlen($values) - 2);
    $query = "REPLACE INTO `$table`($fields) VALUES($values)";

    $res = SQLExec($query, true);
    return $res;
    //SQLInsert('cached_ws', $rec);
}

function postToWebSocket($property, $value, $post_action = 'PostProperty')
{

    if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS == 1) {
        return false;
    }

    global $websockets_script_started;
    if ($websockets_script_started) {
        return false;
    }

    require_once ROOT . 'lib/websockets/client/lib/class.websocket_client.php';

    global $wsClient;

    if (!Is_Object($wsClient)) {
        $wsClient = new WebsocketClient;
        if (!(@$wsClient->connect('127.0.0.1', WEBSOCKETS_PORT, '/majordomo'))) {
            $wsClient = false;
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                DebMes("Failed to connect to websocket");
                echo date('Y-m-d H:i:s') . " Failed to connect to websocket\n";
            }
        }
    }

    if (!Is_Object($wsClient) && isset($_SERVER['REQUEST_METHOD'])) {
        return false;
    }

    if (is_array($property) && is_array($value)) {
        $data = array();
        $total = count($property);
        for ($i = 0; $i < $total; $i++) {
            $data[] = array('NAME' => $property[$i], 'VALUE' => $value[$i]);
        }
    } else {
        $data = array('NAME' => $property, 'VALUE' => $value);
    }


    $payload = json_encode(array(
        'action' => $post_action,
        'data' => $data
    ));

    $data_sent = false;
    if (Is_Object($wsClient)) {
        $data_sent = @$wsClient->sendData($payload);
        if (!$data_sent) {
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                DebMes("Failed to send data to websocket");
                echo date('Y-m-d H:i:s') . " Failed to send data to websocket\n";
            }
        }
    }

    if (!$data_sent && !isset($_SERVER['REQUEST_METHOD'])) {
        //reconnect
        $wsClient = new WebsocketClient;
        if ((@$wsClient->connect('127.0.0.1', WEBSOCKETS_PORT, '/majordomo'))) {
            $data_sent = @$wsClient->sendData($payload);
        } else {
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                DebMes("Failed to reconnect to websocket");
                echo date('Y-m-d H:i:s') . " Failed to reconnect to websocket\n";
            }
        }
    }

    return $data_sent;

}


function createHistoryTable($value_id)
{
    $table_name = 'phistory_value_' . $value_id;
    SQLExec("CREATE TABLE IF NOT EXISTS `$table_name` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `VALUE_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `ADDED` datetime DEFAULT NULL,
  `VALUE` varchar(255) NOT NULL,
  `SOURCE` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `VALUE_ID` (`VALUE_ID`)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
    return $table_name;
}

function moveDataFromMainHistoryToTable($value_id)
{
    $table_name = 'phistory_value_' . $value_id;
    $qry = "phistory.VALUE_ID=" . $value_id;
    SQLExec("INSERT INTO $table_name (VALUE_ID,ADDED,VALUE,SOURCE) SELECT VALUE_ID,ADDED,VALUE,SOURCE FROM phistory WHERE $qry");
    SQLExec("DELETE FROM phistory WHERE $qry");
    return true;
}

function moveDataFromTableToMainHistory($value_id)
{
    $table_name = 'phistory_value_' . $value_id;
    $qry = "phistory.VALUE_ID=" . $value_id;
    SQLExec("DELETE FROM phistory WHERE $qry");
    SQLExec("INSERT INTO phistory (VALUE_ID,ADDED,VALUE,SOURCE) SELECT VALUE_ID,ADDED,VALUE,SOURCE FROM $table_name");
    SQLDropTable($table_name);
    return true;
}
