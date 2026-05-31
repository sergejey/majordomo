<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

ensureHistorySchema();

//SQLTruncateTable('phistory_queue');

// Intentionally do not run OPTIMIZE TABLE on each cycle start.
// It is expensive on large installations and should be done via maintenance windows.

$limit = (int)gg('phistory_queue_limit');
if (!$limit) {
    $limit = 200;
}

$checked_time = 0;
setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
$cycleVarName = 'ThisComputer.' . str_replace('.php', '', basename(__FILE__)) . 'Run';

echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

$processed = array();
$processed_cleanup_time = 0;

while (1) {
    if (time() - $checked_time > 5) {
        $checked_time = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
        // saveToCache("MJD:$cycleVarName", $checked_time);
    }


    /*
    $keep = SQLSelect("SELECT DISTINCT VALUE_ID, KEEP_HISTORY FROM phistory_queue");
    if ($keep[0]['VALUE_ID']) {
        $total = count($keep);
        for ($i = 0; $i < $total; $i++) {
            $keep_rec = $keep[$i];
            if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
                $table_name = createHistoryTable($keep_rec['VALUE_ID']);
            } else {
                $table_name = 'phistory';
            }
            if ($keep_rec['KEEP_HISTORY'] == 0) continue;
            $start_tm = date('Y-m-d H:i:s',(time()-(int)$keep_rec['KEEP_HISTORY']*24*60*60));
            echo date('Y-m-d H:i:s').' '.("DELETE FROM $table_name WHERE VALUE_ID='" . $keep_rec['VALUE_ID'] . "' AND ADDED<('$start_tm')\n");
            SQLExec("DELETE FROM $table_name WHERE VALUE_ID='" . $keep_rec['VALUE_ID'] . "' AND ADDED<('$start_tm')");
            echo date('Y-m-d H:i:s ')." Done \n";
        }
    }
    */
    $queue_error_status = gg('phistory_queue_problem');

    $tmp = SQLSelectOne("SELECT COUNT(*) as TOTAL FROM phistory_queue;");
    $count_queue = (int)$tmp['TOTAL'];

    $queue = SQLSelect("SELECT * FROM phistory_queue ORDER BY ID LIMIT " . $limit);
    if (isset($queue[0]['ID'])) {
        if ((time() - $processed_cleanup_time) > 60 * 60) {
            $processed_cleanup_time = time();
            foreach ($processed as $value_id => $processed_time) {
                if ((time() - $processed_time) > 6 * 60 * 60) {
                    unset($processed[$value_id]);
                }
            }
        }

        if ($count_queue > $limit && !$queue_error_status) {
            sg('phistory_queue_problem', 1);
            $txt = 'Properties history queue is too long (' . $count_queue . ')';
            echo date("H:i:s") . " " . $txt . "\n";
            registerError('phistory_queue', $txt);
        } elseif ($count_queue <= $limit && $queue_error_status) {
            sg('phistory_queue_problem', 0);
        }

        $total = count($queue);
        for ($i = 0; $i < $total; $i++) {
            $q_rec = $queue[$i];
            $value = $q_rec['VALUE'];
            $old_value = $q_rec['OLD_VALUE'];
            debug_echo("Queue $i / $total");
            SQLExec("DELETE FROM phistory_queue WHERE ID='" . $q_rec['ID'] . "'");
            if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
                $table_name = 'phistory_value_' . $q_rec['VALUE_ID'];
            } else {
                $table_name = 'phistory';
            }

            if ($value != $old_value || (defined('HISTORY_NO_OPTIMIZE') && HISTORY_NO_OPTIMIZE == 1)) {
                if (!isset($processed[$q_rec['VALUE_ID']])) {
                    $processed[$q_rec['VALUE_ID']] = time();
                    //$processed[$q_rec['VALUE_ID']]=0;
                }
                if ((time() - $processed[$q_rec['VALUE_ID']]) > 4 * 60 * 60) {
                    $v = SQLSelectOne("SELECT PROPERTY_ID FROM pvalues WHERE ID=" . (int)$q_rec['VALUE_ID']);
                    $prop = SQLSelectOne("SELECT * FROM properties WHERE ID=" . (int)$v['PROPERTY_ID']);
                    cleanUpValueHistory($q_rec['VALUE_ID'], $q_rec['KEEP_HISTORY'], $prop['DATA_TYPE']);
                    $processed[$q_rec['VALUE_ID']] = time();
                    debug_echo(" Done ");
                }
                $h = array();
                $h['VALUE_ID'] = $q_rec['VALUE_ID'];
                $h['ADDED'] = $q_rec['ADDED'];
                $h['VALUE'] = $value;
                if (strlen($h['VALUE']) > 255) $h['VALUE'] = substr($h['VALUE'], 0, 255);
                $h['SOURCE'] = $q_rec['SOURCE'];
                debug_echo(" Insert new value " . $h['VALUE_ID'] . " " . $h['ADDED'] . " " . $value);
                $h['ID'] = SQLInsert($table_name, $h);
                debug_echo(" Done ");
            } elseif ($value == $old_value) {

                $tmp_history = SQLSelect("SELECT * FROM $table_name WHERE VALUE_ID='" . $q_rec['VALUE_ID'] . "' ORDER BY ID DESC LIMIT 2");
                $prev_value = $tmp_history[0]['VALUE'] ?? null;
                $prev_prev_value = $tmp_history[1]['VALUE'] ?? null;

                $this_source = $q_rec['SOURCE'];
                $this_source = preg_replace('/\d+/is', 'N', $this_source);

                $prev_source = $tmp_history[0]['SOURCE'] ?? '';
                $prev_source = preg_replace('/\d+/is', 'N', $prev_source);

                if ($this_source == $prev_source &&
                    $prev_value == $prev_prev_value &&
                    !empty($tmp_history[0]['ID'])) {
                    debug_echo(" Update same value " . $q_rec['VALUE_ID']);
                    SQLExec("UPDATE $table_name SET ADDED='" . $q_rec['ADDED'] . "' WHERE ID=" . $tmp_history[0]['ID']);
                    /*
                    $tmp_history[0]['ADDED'] = $q_rec['ADDED'];
                    foreach($tmp_history[0] as $k=>$v) {
                        if ($k=='ID' || $k=='ADDED') continue;
                        unset($tmp_history[0][$k]);
                    }
                    */
                    //SQLUpdate($table_name, $tmp_history[0]);
                    debug_echo(" Done ");
                } else {
                    debug_echo(" Insert same new value " . $q_rec['VALUE_ID']);
                    $h = array();
                    $h['VALUE_ID'] = $q_rec['VALUE_ID'];
                    $h['ADDED'] = $q_rec['ADDED'];
                    $h['VALUE'] = $value;
                    if (strlen($h['VALUE']) > 255) $h['VALUE'] = substr($h['VALUE'], 0, 255);
                    $h['SOURCE'] = $q_rec['SOURCE'];
                    $h['ID'] = SQLInsert($table_name, $h);
                    debug_echo(" Done ");
                }
            }
            // delete old data
        }
        unset($queue, $tmp_history, $h, $q_rec);
    } else
        sleep(1);

    if (isRebootRequired() || isset($_GET['onetime'])) {
        exit;
    }
}

function debug_echo($line)
{
    //echo date('Y-m-d H:i:s').' '.$line."\n";
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

function ensureHistorySchema()
{
    ensureTableIndex('phistory', 'idx_phistory_value_added', 'VALUE_ID,ADDED');
    ensureTableIndex('phistory_queue', 'idx_phistory_queue_value_id', 'VALUE_ID');
    ensureTableIndex('phistory_queue', 'idx_phistory_queue_added', 'ADDED');
    ensureTableIndex('history', 'idx_history_object_added', 'OBJECT_ID,ADDED');
    ensureTableIndex('history', 'idx_history_method_added', 'METHOD_ID,ADDED');
    ensureTableIndex('history', 'idx_history_value_added', 'VALUE_ID,ADDED');
}

function ensureTableIndex($table_name, $index_name, $index_columns)
{
    $table_name_safe = preg_replace('/[^a-z0-9_]/i', '', (string)$table_name);
    $index_name_safe = preg_replace('/[^a-z0-9_]/i', '', (string)$index_name);
    $index_columns_safe = preg_replace('/[^a-z0-9_,]/i', '', (string)$index_columns);
    if ($table_name_safe == '' || $index_name_safe == '' || $index_columns_safe == '') {
        return;
    }

    $table_exists = SQLSelectOne("SHOW TABLES LIKE '" . DBSafe($table_name_safe) . "'");
    if (!$table_exists) {
        return;
    }

    $check = SQLSelectOne("SHOW INDEX FROM `$table_name_safe` WHERE Key_name='" . DBSafe($index_name_safe) . "' LIMIT 1");
    if (!isset($check['Key_name'])) {
        SQLExec("ALTER TABLE `$table_name_safe` ADD INDEX `$index_name_safe` ($index_columns_safe)");
    }
}
