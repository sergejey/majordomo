<?php

/**
 * Object handler project script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.0
 */

list($usec, $sec) = explode(" ",microtime());
$script_started_time = ((float)$usec + (float)$sec);


//Define('MASTER_HOST', 'homenetserver.jbk'); // uncomment to use master host
if (defined('MASTER_URL') && MASTER_URL != '')
{
   // redirecting request master URL
   if ($argv[1] != '')
   {
      $url = 'http://' . MASTER_HOST . '/objects/?source=remote&op=m';
      $total = count($argv);
      
      for ($i = 2; $i < $total; $i++)
      {
         if (preg_match('/^(.+?):(.*?)$/is', $argv[$i], $matches))
            $url .= '&' . $matches[1] . '=' . urlencode(trim(win2utf($matches[2])));
      }
   }
   else
   {
      $url = 'http://' . MASTER_HOST . $_SERVER['REQUEST_URI'];
   }
   
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
   curl_setopt($ch, CURLOPT_TIMEOUT, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   
   $data = curl_exec($ch);
   curl_close($ch);
   
   exit;
}

// NORMAL HANDLER
chdir(dirname(__FILE__) . '/..');

include_once("./config.php");
include_once("./lib/loader.php");

startMeasure('TOTAL'); // start calculation of execution time

include_once(DIR_MODULES . "application.class.php");

$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
include_once("./load_settings.php");

if ($argv[1] != '')
{
   $commandLine = 1;
   
   if (preg_match('/^(.+?)\.(.+?)$/is', $argv[1], $matches))
   {
      $op = "m";
      $object = $matches[1];
      $m = $matches[2];
   }

   $total = count($argv);
   
   for ($i = 1; $i < $total; $i++)
   {
      if (preg_match('/^(.+?)[:=](.*?)$/is', $argv[$i], $matches))
      {
         $matchesParsed = trim(win2utf($matches[2]));
         
         $_GET[$matches[1]] = $matchesParsed;
         ${$matches[1]} = $matchesParsed;
      }
      else
      {
         $_GET['other_params'][] = $argv[$i];
         $other_params[] = $argv[$i];
      }
   }
}

if (preg_match('/\/\?(\w+)\.(\w+)/', $_SERVER['REQUEST_URI'], $matches))
{
   $_GET['op'] = 'm';
   $_GET['object'] = $matches[1];
   $_GET['m'] = $matches[2];
}

foreach ($_GET as $k => $v)
{
   $request .= '&' . $k . '=' . $v;
}

if (!$request && $commandLine)
{
   $request = implode(' ', $argv);
}

//echo "object: $object op: $op m: $m status: $status ";exit;
if (!$commandLine)
{
   header('Content-Type: text/html; charset=utf-8');
}


if ($module != '') {
 include_once(DIR_MODULES.$module.'/'.$module.'.class.php');
 $mdl=new $module();
 echo $mdl->usual($_GET);
}

if ($object != '')
{
   $obj = getObject($object);
   
   if ($obj)
   {
      //DebMes("object [".$object."] FOUND");
      if ($op == 'get')
      {
         $value = $obj->getProperty($p);
         echo $value;
      }
      
      if ($op == 'set')
      {
         $obj->setProperty($p, $v);
         echo "OK";
      }
      
      if ($op == 'm')
      {
         $params = array();
         foreach ($_GET as $k => $v)
         {
            $params[$k] = ${$k};
         }
         
         //DebMes("Calling method: ".$m.' '.serialize($params));
         //print_r($params);
         $obj->callMethod($m, $params);
      }
   }
   else
   {
      DebMes("object [" . $object . "] not found");
   }
}
elseif ($job != '')
{
   $job = SQLSelectOne("SELECT * FROM jobs WHERE ID='" . (int)$job . "'");
   
   if ($job['ID'])
   {
      try
      {
         verbose_log("Scheduled job [".$job['TITLE']."]");
         $code = $job['COMMANDS'];
         if ($code != '') {
            $success = eval($code);
         } else {
            $success = true;
         }
         if ($success === false)
         {
            DebMes("Error in scheduled job code: " . $code);
            registerError('scheduled_jobs', "Error in scheduled job code: " . $code);
         }
      }
      catch (Exception $e)
      {
         DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
         registerError('scheduled_jobs', get_class($e) . ', ' . $e->getMessage());
      }
      echo "OK";
   } else {
      //DebMes("Job not found: ".$_SERVER['REQUEST_URI']);
      echo "OK";
   }
}
elseif ($method != '')
{
   $method=str_replace('%', '', $method);
   callMethod($method, $_REQUEST);
}
elseif ($script != '')
{
   runScript($script, $_REQUEST);
}

$db->Disconnect(); // closing database connection

endMeasure('TOTAL'); // end calculation of execution time
//performanceReport(); // print performance report

// ob_end_flush();
