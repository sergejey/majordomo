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
    if (isset($rec['TITLE'])) {
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
    $prefix = strtolower((string)$prefix);
    if (defined('USE_REDIS')) {
        $redisConnection = mjdGetRedisConnection();
        if (!is_object($redisConnection)) {
            return;
        }
        if (!$prefix) $redisConnection->flushDB();
        else {
            $list = $redisConnection->keys($prefix . "*");
            foreach ($list as $key1)
                $redisConnection->del($key1);
        }
        return;
    }
    if (!$prefix) {
        SQLTruncateTable('cached_values');
    } else {
        SQLExec("delete from cached_values where KEYWORD like '" . DBSafe($prefix) . "%'");
    }
}

/**
 *  Return all Cache Data from prefix
 * Summary of getAllCache
 * @param mixed $prefix
 * @return array
 */
function getAllCache($prefix = '')
{
    $prefix = strtolower((string)$prefix);
    $out = array();
    if (defined('USE_REDIS')) {
        $redisConnection = mjdGetRedisConnection();
        if (!is_object($redisConnection)) {
            return $out;
        }
        $list = $redisConnection->keys($prefix . "*");
        foreach ($list as $key1)
            $out[$key1] = $redisConnection->get($key1);
    } else {
        $out = SQLSelect("select * from cached_values where KEYWORD like '" . DBSafe($prefix) . "%'");
    }
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
    $key = strtolower((string)$key);
    
    if (defined('USE_REDIS')) {
        $redisConnection = mjdGetRedisConnection();
        if (!is_object($redisConnection)) {
            return;
        }
        $redisConnection->set($key, (string)$value);
        return;
    }

    if (defined('CACHE_VALUE_MAX_SIZE')) {
        $cache_value_max_size = CACHE_VALUE_MAX_SIZE;
    } else {
        $cache_value_max_size = 255;
    }

    if (is_array($value) || strlen((string)$value) > $cache_value_max_size) {
        deleteFromCache($key);
        return;
    }

    $rec = array('KEYWORD' => $key, 'DATAVALUE' => $value);
    $sqlQuery = "REPLACE INTO cached_values (KEYWORD, DATAVALUE) " .
        " VALUES ('" . DbSafe1($rec['KEYWORD']) . "', " .
        "'" . DbSafe1($rec['DATAVALUE']) . "')";
    SQLExec($sqlQuery);
}

function deleteFromCache($key) {
    $key = strtolower((string)$key);
    SQLExec("DELETE FROM cached_values WHERE KEYWORD='" . DBSafe($key) . "'");
    if (defined('USE_REDIS')) {
        $redisConnection = mjdGetRedisConnection();
        if (is_object($redisConnection) && $redisConnection->exists($key)) {
            $redisConnection->del($key);
        }
    }
}

/**
 * Storage for transient object properties. cached_values is intentionally NOT
 * used: it is wiped by clearCacheData() on cycle restarts and admin actions.
 * The table is created in objects::dbInstall() and lives in RAM, so values
 * disappear on a MySQL restart.
 */
function saveTransient($key, $value)
{
    $key = strtolower((string)$key);
    $max = 2048;
    $value = (string)$value;
    if (mb_strlen($value, 'UTF-8') > $max) {
        if (function_exists('DebMes')) {
            DebMes('Transient value for ' . $key . ' is longer than ' . $max . ' characters and was cut', 'transient');
        }
        $value = mb_substr($value, 0, $max, 'UTF-8');
    }
    // ignore_errors: a full MEMORY table must not stop the calling process
    if (!SQLExec("REPLACE INTO transient_values (KEYWORD, DATAVALUE) VALUES ('" .
        DbSafe1($key) . "', '" . DbSafe1($value) . "')", true) && function_exists('DebMes')) {
        DebMes('Cannot save transient value for ' . $key, 'transient');
    }
}

function checkTransient($key)
{
    $key = strtolower((string)$key);
    $rec = SQLSelectOne("SELECT DATAVALUE FROM transient_values WHERE KEYWORD='" . DbSafe1($key) . "'");
    return isset($rec['DATAVALUE']) ? $rec['DATAVALUE'] : false;
}

function deleteTransient($key)
{
    $key = strtolower((string)$key);
    SQLExec("DELETE FROM transient_values WHERE KEYWORD='" . DbSafe1($key) . "'");
}

function getTransientValuesByProperty($propertyId)
{
    $rows = SQLSelect("SELECT objects.ID AS OBJECT_ID, objects.TITLE, transient_values.DATAVALUE AS VALUE FROM objects, transient_values " .
        "WHERE transient_values.KEYWORD=CONCAT('t:', objects.ID, '.', " . (int)$propertyId . ")");
    return is_array($rows) ? $rows : array();
}

function deleteTransientByObject($objectId)
{
    SQLExec("DELETE FROM transient_values WHERE KEYWORD LIKE 't:" . (int)$objectId . ".%'");
}

function deleteTransientByProperty($propertyId)
{
    $rows = SQLSelect("SELECT KEYWORD FROM transient_values");
    $propertyId = (int)$propertyId;
    foreach ($rows as $row) {
        if (preg_match('/^t:\d+\.' . $propertyId . '(\.u)?$/', strtolower($row['KEYWORD']))) {
            deleteTransient($row['KEYWORD']);
        }
    }
}

/** Drop rows that no longer belong to a transient property (RAM table hygiene). */
function cleanupTransientValues()
{
    // objects module may not be reinstalled yet; a missing table would stop cycle.php
    if (!SQLSelectOne("SHOW TABLES LIKE 'transient_values'")) return;
    $rows = SQLSelect("SELECT KEYWORD FROM transient_values");
    foreach ($rows as $row) {
        $key = strtolower($row['KEYWORD']);
        if (!preg_match('/^t:(\d+)\.(\d+)(\.u)?$/', $key, $m)) {
            deleteTransient($row['KEYWORD']);
            continue;
        }
        // transient values usually have no pvalues row
        $rec = SQLSelectOne("SELECT properties.ID FROM properties, objects WHERE properties.ID=" . (int)$m[2] .
            " AND properties.TRANSIENT=1 AND objects.ID=" . (int)$m[1] . " LIMIT 1");
        if (!isset($rec['ID'])) {
            deleteTransient($row['KEYWORD']);
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
    $key = strtolower((string)$key);
    if (defined('USE_REDIS')) {
        $redisConnection = mjdGetRedisConnection();
        if (!is_object($redisConnection)) {
            return false;
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
        if (Is_Object($wsClient)) {
            $wsClient->disconnect();
        }
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
