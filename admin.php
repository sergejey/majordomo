<?php
/**
 * This file is part of MajorDoMo system. More details at https://majordomohome.com/
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <sergejey@gmail.com>
 * @version 1.3
 */
Define('ALLOW_RUNNING_WITH_ERRORS', 1);

include_once("./config.php");
include_once("./lib/perfmonitor.class.php");
startMeasure('TOTAL');

startMeasure('loader');
include_once("./lib/loader.php");
endMeasure('loader');


include_once("./load_settings.php");

if (isset($_GET['part_load']) && checkFromCache('reload:' . md5($_SERVER['REQUEST_URI']))) {
    $res = array();
    $res['TITLE'] = '';
    $res['CONTENT'] = '';
    $res['NEED_RELOAD'] = 1;
    echo json_encode($res);
    exit;
}

include_once(DIR_MODULES . "panel.class.php");
$session = new session("prj");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$cl = new control_modules();
$app = new panel();

$md = gr('md');
if ($md != $app->name)
    $app->restoreParams();
else
    $app->getParams();

startMeasure('apprun');
$result = $app->run();
endMeasure('apprun');

startMeasure('part2');

if (isset($filterblock) and $filterblock != '') {
    $blockPattern = '/<!-- begin_data \[' . $filterblock . '\] -->(.*?)<!-- end_data \[' . $filterblock . '\] -->/is';
    preg_match($blockPattern, $result, $match);
    $result = $match[1];
}

startMeasure('languageConstants');
if (preg_match_all('/&\#060\#LANG_(.+?)\#&\#062/is', $result, $matches)) {
    $total = count($matches[0]);
    for ($i = 0; $i < $total; $i++) {
        if (defined('LANG_' . $matches[1][$i])) {
            $result = str_replace($matches[0][$i], constant('LANG_' . $matches[1][$i]), $result);
        } else {
            echo "'" . $matches[1][$i] . "'=>'',<br />";
        }
    }
}
endMeasure('languageConstants');

$result = str_replace("nf.php", "admin.php", $result);

startMeasure('postProcessGeneral');
require(ROOT . 'lib/utils/postprocess_general.inc.php');
endMeasure('postProcessGeneral');

startMeasure('postProcessSubscriptions');
require(ROOT . 'lib/utils/postprocess_subscriptions.inc.php');
//require(ROOT.'lib/utils/postprocess_result.inc.php');
endMeasure('postProcessSubscriptions');
endMeasure('part2');

if ((defined('ENABLE_PANEL_ACCELERATION') && ENABLE_PANEL_ACCELERATION)) {
    startMeasure('accelerationProcess');
    $result = preg_replace('/href="(\/admin\.php.+?)">/is', 'href="\1" onclick="return partLoad(this.href);">', $result);
    endMeasure('accelerationProcess');
}


if (isset($_GET['part_load'])) {

    $res = array();
    $res['TITLE'] = '';
    $res['CONTENT'] = '';
    $res['NEED_RELOAD'] = 1;

    $cut_begin = '<div id="partLoadContent">';
    $cut_begin_index = mb_strpos($result, $cut_begin);
    $cut_end = '</div><!--partloadend-->';
    $cut_end_index = mb_strpos($result, $cut_end);

    if (is_integer($cut_begin_index) && is_integer($cut_end_index)) {
        $cut_begin_index += mb_strlen($cut_begin) + 2;
        $res['CONTENT'] = mb_substr($result, $cut_begin_index, ($cut_end_index - $cut_begin_index));
        $res['NEED_RELOAD'] = 0;
        if (headers_sent()
            || is_integer(mb_strpos($res['CONTENT'], '$(document).ready'))
            || is_integer(mb_strpos($res['CONTENT'], '$(function('))
            || is_integer(mb_strpos($res['CONTENT'], 'codemirror/'))) {
            $res['CONTENT'] = '';
            $res['NEED_RELOAD'] = 1;
        }
        if (preg_match('/<title>(.+?)<\/title>/is', $result, $m)) {
            $res['TITLE'] = $m[1];
        }
    } else {
        $res['CONTENT'] = '';
        $res['NEED_RELOAD'] = 1;
    }

    $result = json_encode($res);
    if (is_integer(mb_strpos($result, '"CONTENT":null')) && !$res['NEED_RELOAD']) {
        $res['CONTENT'] = '';
        $res['NEED_RELOAD'] = 1;
        $result = json_encode($res);
    }

    if ($res['NEED_RELOAD']) {
        saveToCache('reload:' . md5($_SERVER['REQUEST_URI']), 1);
    }

    header("HTTP/1.0: 200 OK\n");
    header('Content-Type: text/html; charset=utf-8');
    echo $result;
    exit;
    $session->save();
    exit;
}

startMeasure('echoall');


if (isset($_GET['dynids']) and is_array($_GET['dynids'])) {

    $data = array();
    foreach ($_GET['dynids'] as $data_id) {
        if (preg_match('/id="' . $data_id . '">(.+?)<!--\/dynamic_content-->/uis', $result, $m)) {
            $data['blocks'][] = array('name' => $data_id, 'content' => $m[1]);
        }

    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit;
}


if (!headers_sent()) {
    header("HTTP/1.0: 200 OK\n");
    header('Content-Type: text/html; charset=utf-8');
    if (!ob_get_length()) {
        if (!ob_start("ob_gzhandler")) ob_start();
    }
}

echo $result;
endMeasure('echoall');

$session->save();

// end calculation of execution time
endMeasure('TOTAL');

// print performance report
performanceReport();
