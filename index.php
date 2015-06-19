<?php

/**
 * Main project script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by>
 * @url http://smartliving.ru
 * @version 1.2
 */

include_once("./config.php");
include_once("./lib/loader.php");

// start calculation of execution time
startMeasure('TOTAL');

include_once(DIR_MODULES . "application.class.php");

$session = new session("prj");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

$use_caching   = 0;
$cache_expire  = 60 * 60; // 60 minutes cache expiration time
$cached_result = '';

$req_url = $_SERVER['REQUEST_URI'];

if ($req_url == '/')
   $req_url = '/index.html';

if ($use_caching && preg_match('/^\/([\/\w_-]+)\.html$/', $req_url, $matches) && $_SERVER['REQUEST_METHOD'] != 'POST')
{
   $cache_filename = preg_replace('/\W/', '_', $matches[1]) . '.html';
   
   if (file_exists(ROOT . 'cached/' . $cache_filename))
   {
      if ((time() - filemtime(ROOT . 'cached/' . $cache_filename)) <= $cache_expire)
      {
         $cached_result = LoadFile(ROOT . 'cached/' . $cache_filename);
      }
      else
      {
         unlink(ROOT . 'cached/' . $cache_filename);
      }
   }
}

if ($cached_result == '')
{
   if (!file_exists(DIR_MODULES . 'control_modules/installed'))
   {
      include_once(DIR_MODULES . "control_modules/control_modules.class.php");
      $ctl = new control_modules();
   }

   $app = new application();

   if ($md != $app->name)
      $app->restoreParams();
   else
      $app->getParams();

   if ($app->action != '' && $app->action != 'docs')
      $fake_doc = '';

   if ($app->action == '' && $fake_doc != "" && file_exists(DIR_MODULES . 'cms_docs/cms_docs.class.php'))
   {
      $sqlQuery = "SELECT ID
                     FROM cms_docs
                    WHERE NAME = '" . DBSafe($fake_doc) . "'";
      
      $tmp = SQLSelectOne($sqlQuery);
      
      if (isset($tmp['ID']))
      {
         $app->action = "docs";
         $app->doc    = $tmp['ID'];
      }
      elseif (file_exists(DIR_TEMPLATES . $fake_doc . ".html"))
      {
         $app->action = $fake_doc;
      }
      else
      {
         //$tmp1=SQLSelectOne("SELECT ID FROM cms_docs WHERE NAME='404'");
         $tmp1 = array();
         if ($tmp1['ID'])
         {
            $app->action = "docs";
            $app->doc    = $tmp1['ID'];
         }
         else
         {
            header("HTTP/1.0 404 Not Found");
            echo "The page cannot be found. Please use <a href='/'>this link</a> to continue browsing.";
            exit;
         }
      }
   }

   $result = $app->run();
   $result = str_replace("nf.php", "index.php", $result);
}
else
{
   // show cached result
   $result = $cached_result;
}

// BEGIN: begincut endcut placecut
if (preg_match_all('/<!-- placecut (\w+?) -->/is', $result, $matches))
{
   $matchesCount = count($matches[1]);
   for ($i = 0; $i < $matchesCount; $i++)
   {
      $block = $matches[1][$i];
      if (preg_match('/<!-- begincut ' . $block . ' -->(.*?)<!-- endcut ' . $block . ' -->/is', $result, $matches2))
      {
         $result = str_replace($matches[0][$i], $matches2[1], $result);
         $result = str_replace($matches2[0], '', $result);
      }
   }
}
// END: begincut endcut placecut

// BEGIN: filter output
if ($filterblock != '')
{
   $matchPattern = '/<!-- begin_data \[' . $filterblock . '\] -->(.*?)<!-- end_data \[' . $filterblock . '\] -->/is';
   preg_match($matchPattern, $result, $match);
   $result = $match[1];
}
// END: filter output

// GLOBALS
$result = preg_replace('/%rand%/is', rand(), $result);

if (preg_match_all('/%(\w{2,}?)\.(\w{2,}?)%/is', $result, $m))
{
   $total = count($m[0]);
   
   for ($i = 0; $i < $total; $i++)
   {
      $result = str_replace($m[0][$i], getGlobal($m[1][$i] . '.' . $m[2][$i]), $result);
   }
}

if (preg_match_all('/%(\w{2,}?)\.(\w{2,}?)\|(\d+)%/is', $result, $m))
{
   $total = count($m[0]);
   $seen  = array();
   
   for ($i = 0; $i < $total; $i++)
   {
      $var      = $m[1][$i] . '.' . $m[2][$i];
      $interval = (int)$m[2][$i] * 1000;

      if (!$interval)
         $interval = 10000;
      
      $id = 'var_' . preg_replace('/\W/', '_', $var) . $seen[$var];
      $seen[$var]++;

      $scriptReplace  = '<span id="' . $id . '">...</span>';
      $scriptReplace .= '<script type="text/javascript">';
      $scriptReplace .= 'ajaxGetGlobal("' . $var . '", "' . $id . '", ' . $interval . ');';
      $scriptReplace .= '</script>';

      $result = str_replace($m[0][$i], $scriptReplace, $result);
   }
}
// END GLOBALS

// BEGIN: language constants
if (preg_match_all('/&\#060\#LANG_(.+?)\#&\#062/is', $result, $matches))
{
   $total = count($matches[0]);
   
   for ($i = 0; $i < $total; $i++)
   {
      if (preg_match('/value=["\']' . preg_quote($matches[0][$i]) . '["\']/is', $result))
         continue;
      
      $languageConstant = 'LANG_' . $matches[1][$i];
      if (defined($languageConstant))
      {
         $result = str_replace($matches[0][$i], constant($languageConstant), $result);
      }
      else
      {
         $resultMessageHtml = 'Warning: <i>' . $languageConstant . '</i> not defined, please check dictionary file';
         echo '<b style="color:red;">' . $resultMessageHtml . '</b><br />';
      }
   }
}
// END: language constants

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


if (!headers_sent())
{
   header("HTTP/1.0: 200 OK\n");
   header('Content-Type: text/html; charset=utf-8');
   //ob_start("ob_gzhandler"); // should be un-commented for production server
}

echobig($result);

endMeasure('final_echo', 1);

if ($cache_filename != '' && $cached_result == '')
{
   SaveFile(ROOT . 'cached/' . $cache_filename, $result);
}

$session->save();

// closing database connection
$db->Disconnect();

// end calculation of execution time
endMeasure('TOTAL');

// print performance report
performanceReport();

// ob_end_flush();
