<?php

/**
 * Main project script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by>
 * @url http://smartliving.ru
 * @version 1.2
 */

include_once("./lib/perfmonitor.class.php");
startMeasure('TOTAL');

include_once("./config.php");
include_once("./lib/loader.php");

// start calculation of execution time
startMeasure('prepare');
include_once(DIR_MODULES . "application.class.php");

$session = new session("prj");

startMeasure('load_settings');
include_once("./load_settings.php");
endMeasure('load_settings');

if (!file_exists(ROOT . 'cms/modules_installed/control_modules.installed')) {
    include_once(DIR_MODULES . "control_modules/control_modules.class.php");
    $ctl = new control_modules();
}

$app = new application();

if ($md != $app->name)
    $app->restoreParams();
else
    $app->getParams();

if ($app->action != '' && $app->action != 'docs') $fake_doc = '';

$result = $app->run();
$result = str_replace("nf.php", "index.php", $result);

endMeasure('prepare');
require(ROOT.'lib/utils/postprocess_general.inc.php');
require(ROOT.'lib/utils/postprocess_subscriptions.inc.php');
require(ROOT.'lib/utils/postprocess_result.inc.php');

/**
 * Echo large text
 * @param mixed $string     Text
 * @param mixed $bufferSize Buffer size
 * @return void
 */
function echobig($string, $bufferSize = 8192)
{
   $chars = strlen($string) - 1;
   for ($start = 0; $start <= $chars; $start += $bufferSize)
   {
      echo substr($string,$start,$bufferSize);
   }
}

startMeasure('final_echo');


if (!headers_sent()) {
   header("HTTP/1.0: 200 OK\n");
   header('Content-Type: text/html; charset=utf-8');
   header('Access-Control-Allow-Origin: *');
   if (!ob_get_length()) {
      if(!ob_start("ob_gzhandler")) ob_start();
   }
}

echobig($result);

endMeasure('final_echo', 1);

if ($cache_filename != '' && $cached_result == '')
{
   SaveFile(ROOT . 'cms/cached/' . $cache_filename, $result);
}

$session->save();

if (isset($wsClient) && $wsClient) {
 $wsClient->disconnect();
}

// end calculation of execution time
endMeasure('TOTAL');

// print performance report
performanceReport();

ob_end_flush();
