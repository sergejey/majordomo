<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

$run_from_start = 1;
include("./scripts/startup_maintenance.php");
$run_from_start = 0;

setGlobal('ThisComputer.started_time', time());
getObject('ThisComputer')->raiseEvent("StartUp");

$sqlQuery = "SELECT *
               FROM classes
              WHERE TITLE LIKE 'timer'";

$timerClass = SQLSelectOne($sqlQuery);
$o_qry = 1;

if ($timerClass['SUB_LIST'] != '') {
    $o_qry .= " AND (CLASS_ID IN (" . $timerClass['SUB_LIST'] . ")";
    $o_qry .= "  OR CLASS_ID = " . $timerClass['ID'] . ")";
} else {
    $o_qry .= " AND 0";
}

$old_minute = date('i');
$old_hour = date('h');
if ($_GET['onetime']) {
    $old_minute = -1;
    if (date('i') == '00') {
        $old_hour = -1;
    }
}
$old_date = date('Y-m-d');

$checked_time = 0;
$started_time = time();

echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

while (1) {
    if (time() - $checked_time > 5) {
        $checked_time = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
        setGlobal('ThisComputer.uptime', time() - getGlobal('ThisComputer.started_time'));
    }

    $m = date('i');
    $h = date('h');
    $dt = date('Y-m-d');

    if ($m != $old_minute) {
        //echo "new minute\n";
        $sqlQuery = "SELECT ID, TITLE
                     FROM objects
                    WHERE $o_qry";

        $objects = SQLSelect($sqlQuery);
        $total = count($objects);

        for ($i = 0; $i < $total; $i++) {
            echo date('H:i:s') . ' ' . $objects[$i]['TITLE'] . "->onNewMinute\n";
            getObject($objects[$i]['TITLE'])->setProperty("time", date('Y-m-d H:i:s'));
            getObject($objects[$i]['TITLE'])->raiseEvent("onNewMinute");
        }

        $old_minute = $m;
    }

    if ($h != $old_hour) {
        $sqlQuery = "SELECT ID, TITLE
                     FROM objects
                    WHERE $o_qry";

        //echo "new hour\n";
        $old_hour = $h;
        $objects = SQLSelect($sqlQuery);
        $total = count($objects);

        for ($i = 0; $i < $total; $i++) {
            echo date('H:i:s') . ' ' . $objects[$i]['TITLE'] . "->onNewHour\n";
            getObject($objects[$i]['TITLE'])->raiseEvent("onNewHour");
        }

        processSubscriptions('HOURLY');

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
    /*
    $queue = SQLSelect("SELECT * FROM phistory_queue ORDER BY ID LIMIT 500");
    if ($queue[0]['ID']) {
        $total = count($queue);
        $processed = array();
        for ($i = 0; $i < $total; $i++) {
            $q_rec = $queue[$i];
            $value = $q_rec['VALUE'];
            $old_value = $q_rec['OLD_VALUE'];

            //echo "Queue $i / $total\n";
            SQLExec("DELETE FROM phistory_queue WHERE ID='" . $q_rec['ID'] . "'");

            if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
                $table_name = 'phistory_value_'.$q_rec['VALUE_ID'];
            } else {
                $table_name = 'phistory';
            }

            if ($value != $old_value || (defined('HISTORY_NO_OPTIMIZE') && HISTORY_NO_OPTIMIZE == 1)) {
                if (!isset($processed[$q_rec['VALUE_ID']])) {
                    $processed[$q_rec['VALUE_ID']]=time();
                }
                if ((time() - $processed[$q_rec['VALUE_ID']]) > 8 * 60 * 60) {
                    $start_tm = date('Y-m-d H:i:s',(time()-(int)$q_rec['KEEP_HISTORY']*24*60*60));
                    //echo date('Y-m-d H:i:s').("processing DELETE FROM $table_name WHERE VALUE_ID='" . $q_rec['VALUE_ID'] . "' AND ADDED<('".$start_tm."')\n");
                    SQLExec("DELETE FROM $table_name WHERE VALUE_ID='" . $q_rec['VALUE_ID'] . "' AND ADDED<('".$start_tm."')");
                    $processed[$q_rec['VALUE_ID']] = time();
                    //echo date('Y-m-d H:i:s ')." Done \n";
                }
                $h = array();
                $h['VALUE_ID'] = $q_rec['VALUE_ID'];
                $h['ADDED'] = $q_rec['ADDED'];
                $h['VALUE'] = $value;
                //echo date('Y-m-d H:i:s ')." Insert new value ".$h['VALUE_ID']."\n";
                $h['ID'] = SQLInsert($table_name, $h);
                //echo date('Y-m-d H:i:s ')." Done \n";
            } elseif ($value == $old_value) {
                $tmp_history = SQLSelect("SELECT * FROM $table_name WHERE VALUE_ID='" . $q_rec['VALUE_ID'] . "' ORDER BY ID DESC LIMIT 2");
                $prev_value = $tmp_history[0]['VALUE'];
                $prev_prev_value = $tmp_history[1]['VALUE'];
                if ($prev_value == $prev_prev_value) {
                    $tmp_history[0]['ADDED'] = $q_rec['ADDED'];
                    //echo date('Y-m-d H:i:s ')." Update same value ".$tmp_history[0]['VALUE_ID']."\n";
                    SQLUpdate($table_name, $tmp_history[0]);
                    //echo date('Y-m-d H:i:s ')." Done \n";
                } else {
                    $h = array();
                    $h['VALUE_ID'] = $q_rec['VALUE_ID'];
                    $h['ADDED'] = $q_rec['ADDED'];
                    $h['VALUE'] = $value;
                    //echo date('Y-m-d H:i:s ')." Insert same value ".$h['VALUE_ID']."\n";
                    $h['ID'] = SQLInsert($table_name, $h);
                    //echo date('Y-m-d H:i:s ')." Done \n";
                }
            }

        }
    }
    */

    if ($dt != $old_date) {
        //echo "new day\n";
        $old_date = $dt;
    }

    if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
        $db->Disconnect();
        exit;
    }

    sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
