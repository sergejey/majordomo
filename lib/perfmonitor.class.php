<?php

/**
 * Performance monitoring
 *
 * This function is for performacne monitoring of scripts
 * usage:
 * add after begin of monitoring process StartMeasure('process name');
 * add before end of process EndMeasure('process name');
 * use PerformanceReport(); at the end of script
 * (use TOTAL process name for measure of total process and % for other)
 *
 * @package framework
 * @author Serge Dzheigalo <jey@unit.local>
 * @copyright Activeunit Inc 2001-2004
 * @version 1.0
 */

/**
 * Getting micro-time
 * @return double|int
 */
function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

/**
 * Starting measurement of time for specified block
 * @param mixed $mpoint Monitoring block name
 * @return void
 */
function StartMeasure($mpoint)
{
    global $perf_data;

    global $script_started_time;
    if ($mpoint == 'TOTAL' && $script_started_time) {
        $perf_data[$mpoint]['START'] = $script_started_time;
    } else {
        $perf_data[$mpoint]['START'] = getmicrotime();
    }

    if ((isset($perf_data[$mpoint]['MEMORY_START']) && !$perf_data[$mpoint]['MEMORY_START'])
        || !isset($perf_data[$mpoint]['MEMORY_START'])
        && function_exists('memory_get_usage')
    ) {
        $perf_data[$mpoint]['MEMORY_START'] = memory_get_usage();
    }
}

/**
 * Ending measurement of time for specified block
 * @param mixed $mpoint monitoring block name
 * @param mixed $save_to_db Save to DB (Default 0) Currently not used
 * @return void
 */
function EndMeasure($mpoint)
{
    global $perf_data;

    if (!isset($perf_data[$mpoint]['START'])) {
        return;
    }

    $perf_data[$mpoint]['END'] = getmicrotime();

    if (!isset($perf_data[$mpoint]['MEMORY_END']) && function_exists('memory_get_usage')) {
        $perf_data[$mpoint]['MEMORY_END'] = memory_get_usage();
    }

    if (!isset($perf_data[$mpoint]['TIME'])) {
        $perf_data[$mpoint]['TIME'] = 0;
    }

    $perf_data[$mpoint]['TIME'] += $perf_data[$mpoint]['END'] - $perf_data[$mpoint]['START'];

    if (!isset($perf_data[$mpoint]['NUM'])) {
        $perf_data[$mpoint]['NUM'] = 0;
    }

    $perf_data[$mpoint]['NUM']++;

    //$save_to_db=1;
    /*
    if ($save_to_db) {
        global $db;
        if ($db->dbh) {
            $rec = array();
            $rec['OPERATION'] = substr($mpoint, 0, 200);
            $rec['COUNTER'] = 1;
            $rec['TIMEUSED'] = $perf_data[$mpoint]['TIME'];
            $rec['ADDED'] = date('Y-m-d H:i:s');
            if ($_SERVER['REQUEST_URI']) {
                $rec['SOURCE'] = 'web';
            } else {
                $rec['SOURCE'] = 'cmd';
            }
            SQLInsert('performance_log', $rec);
        }
    }
*/
}

/**
 * Printing report for all blocks
 * @param mixed $hidden n/a (default 1)
 * @return void
 */
function PerformanceReport($hidden = 0)
{
    global $perf_data;

    if (!$hidden) {
        echo "<!-- BEGIN PERFORMANCE REPORT\n";
    }

    foreach ($perf_data as $k => $v) {
        if (!$v['NUM']) {
            EndMeasure($k);
        }
    }

    foreach ($perf_data as $k => $v) {
        if ($perf_data['TOTAL']['TIME']) {
            $v['PROC'] = ((int)($v['TIME'] / $perf_data['TOTAL']['TIME'] * 100 * 100)) / 100;
        }

        $rs = "$k (" . $v['NUM'] . "): " . round($v['TIME'], 4) . " " . round($v['PROC'], 2) . "%";

        if ($v['MEMORY_START']) {
            $rs .= ' M (s): ' . $v['MEMORY_START'] . 'b';
        }

        if ($v['MEMORY_END']) {
            $rs .= ' M (e): ' . $v['MEMORY_END'] . 'b';
        }

        if (!$v['NUM']) {
            $tmp[] = "Not finished $k";
        }

        $tmp[] = $rs;
    }

    if (!$hidden) {
        echo implode("\n", $tmp);
        echo "\n END PERFORMANCE REPORT -->";
    }
    return $tmp;
}

