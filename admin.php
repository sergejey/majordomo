<?php
/**
 * This file is part of MajorDoMo system. More details at http://smartliving.ru/
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by>
 * @version 1.3
 */

include_once("./config.php");
include_once("./lib/perfmonitor.class.php");
startMeasure('TOTAL');

startMeasure('loader');
include_once("./lib/loader.php");
endMeasure('loader');

include_once(DIR_MODULES . "panel.class.php");

$session = new session("prj");

startMeasure('db connection');
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
endMeasure('db connection');
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$cl  = new control_modules();
$app = new panel();

if ($md != $app->name)
   $app->restoreParams();
else
   $app->getParams();

startMeasure('apprun');
$result = $app->run();
endMeasure('apprun');

startMeasure('part2');
// BEGIN: filter output
if ($filterblock != '')
{
   $blockPattern = '/<!-- begin_data \[' . $filterblock . '\] -->(.*?)<!-- end_data \[' . $filterblock . '\] -->/is';
   preg_match($blockPattern, $result, $match);
   $result = $match[1];
}
// END: filter output

// BEGIN: language constants
if (preg_match_all('/&\#060\#LANG_(.+?)\#&\#062/is', $result, $matches))
{
   $total = count($matches[0]);
   for ($i = 0; $i < $total; $i++)
   {
      if (defined('LANG_' . $matches[1][$i]))
      {
         $result = str_replace($matches[0][$i], constant('LANG_' . $matches[1][$i]), $result);
      }
      else
      {
         echo "'" . $matches[1][$i] . "'=>'',<br />";
      }
   }
}
// END: language constants

$result = str_replace("nf.php", "admin.php", $result);

require(ROOT.'lib/utils/postprocess_result.inc.php');


if (!defined('DISABLE_PANEL_ACCELERATION') || DISABLE_PANEL_ACCELERATION!=1) {
 if (preg_match_all('/href="(\/admin\.php.+?)">/is',$result,$matches)) {
    $total = count($matches[1]);
    for ($i = 0; $i < $total; $i++) {
       $result=str_replace($matches[0][$i],'href="'.$matches[1][$i].'" onclick="return partLoad(this.href);">',$result);
    }
 }
}


endMeasure('part2');


if ($_GET['part_load']) {

   $res=array();
   $res['TITLE']='';
   $res['CONTENT']='';
   $res['NEED_RELOAD']=1;

   $cut_begin='<div id="partLoadContent">';
   $cut_begin_index=mb_strpos($result, $cut_begin);
   $cut_end='</div><!--partloadend-->';
   $cut_end_index=mb_strpos($result, $cut_end);

   if (is_integer($cut_begin_index) && is_integer($cut_end_index)) {
      $cut_begin_index+=mb_strlen($cut_begin)+2;
      $res['CONTENT']=mb_substr($result,$cut_begin_index,($cut_end_index-$cut_begin_index));
      $res['NEED_RELOAD']=0;
      if (headers_sent() || is_integer(mb_strpos($res['CONTENT'], '$(document).ready')) || is_integer(mb_strpos($res['CONTENT'], 'js/codemirror'))) { 
         $res['CONTENT']='';
         $res['NEED_RELOAD']=1;
      }
      if (preg_match('/<title>(.+?)<\/title>/is',$result,$m)) {
         $res['TITLE']=$m[1];
      }
   } else {
         $res['CONTENT']='';
         $res['NEED_RELOAD']=1;
   }

      $result=json_encode($res);
      if (is_integer(mb_strpos($result, '"CONTENT":null')) && !$res['NEED_RELOAD']) {
             $res['CONTENT']='';
             $res['NEED_RELOAD']=1;
             $result=json_encode($res);
      }

      header("HTTP/1.0: 200 OK\n");
      header('Content-Type: text/html; charset=utf-8');
      echo $result;exit;
      $session->save();
      $db->Disconnect(); // closing database connection
      exit;

}

startMeasure('echoall');

if (!headers_sent())
{
   header("HTTP/1.0: 200 OK\n");
   header('Content-Type: text/html; charset=utf-8');
   //ob_start("ob_gzhandler"); // should be un-commented for production server
}

echo $result;
endMeasure('echoall');

$session->save();
$db->Disconnect(); // closing database connection

// end calculation of execution time
endMeasure('TOTAL');

// print performance report
performanceReport();
