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
$cycleVarName = 'ThisComputer.' . str_replace('.php', '', basename(__FILE__)) . 'Run';

echo "Running startup maintenance" . PHP_EOL;
$run_from_start = 1;
include("./scripts/startup_maintenance.php");
$run_from_start = 0;

setGlobal('ThisComputer.started_time', time());
callMethod('ThisComputer.StartUp');
processSubscriptionsSafe('startup');

$sqlQuery = "SELECT *
               FROM classes
              WHERE TITLE = 'timer'";

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
//       saveToCache("MJD:$cycleVarName", $checked_time);

        $timestamp = time() - getGlobal('ThisComputer.started_time');
        setGlobal('ThisComputer.uptime', $timestamp);

        $years = floor($timestamp / 31536000);
        $days = floor(($timestamp - ($years * 31536000)) / 86400);
        $hours = floor(($timestamp - ($years * 31536000 + $days * 86400)) / 3600);
        $minutes = floor(($timestamp - ($years * 31536000 + $days * 86400 + $hours * 3600)) / 60);
        $timestring = '';
        if ($years > 0) {
            $timestring .= $years . 'y ';
        }
        if ($days > 0) {
            $timestring .= $days . 'd ';
        }
        if ($hours > 0) {
            $timestring .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $timestring .= $minutes . 'm ';
        }
        setGlobal('ThisComputer.uptimeText', trim($timestring));

    }

    $m = date('i');
    $h = date('h');
    $dt = date('Y-m-d');

    #NewMinute
    if ($m != $old_minute) {
        processSubscriptionsSafe('MINUTELY');
        $sqlQuery = "SELECT ID, TITLE
                     FROM objects
                    WHERE $o_qry";

        $objects = SQLSelect($sqlQuery);
        $total = count($objects);

        for ($i = 0; $i < $total; $i++) {
            echo date('H:i:s') . ' ' . $objects[$i]['TITLE'] . "->onNewMinute\n";
            sg($objects[$i]['TITLE'] . '.time', date('Y-m-d H:i:s'));
            callMethodSafe($objects[$i]['TITLE'] . '.onNewMinute');
        }
        $old_minute = $m;
    }

    #NewHour
    if ($h != $old_hour) {
        processSubscriptionsSafe('HOURLY');
        for ($i = 0; $i < $total; $i++) {
            echo date('H:i:s') . ' ' . $objects[$i]['TITLE'] . "->onNewHour\n";
            callMethodSafe($objects[$i]['TITLE'] . '.onNewHour');
        }
        $old_hour = $h;
    }

    #NewDay
    if ($dt != $old_date) {
        processSubscriptionsSafe('DAILY');
        for ($i = 0; $i < $total; $i++) {
            echo date('H:i:s') . ' ' . $objects[$i]['TITLE'] . "->onNewDay\n";
            callMethodSafe($objects[$i]['TITLE'] . '.onNewDay');
        }
        $old_date = $dt;
    }

    if (file_exists('./reboot') || isset($_GET['onetime'])) {
        exit;
    }

    sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
