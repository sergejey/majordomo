<?php
/**
* Object handler project script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.0
*/

 // ---------------------------------------------------------------------------
 // REDIRECT TO MASTER HOST

 //Define('MASTER_HOST', 'homenetserver.jbk'); // uncomment to use master host

 if (defined('MASTER_URL') && MASTER_URL!='') {
  // redirecting request master URL

  if ($argv[1]!='') {
   $url='http://'.MASTER_HOST.'/objects/?source=remote&op=m';
   $total=count($argv);
   for($i=2;$i<$total;$i++) {
    if (preg_match('/^(.+?):(.*?)$/is', $argv[$i], $matches)) {
     $url.='&'.$matches[1].'='.urlencode(trim(win2utf($matches[2])));
    }        
   }
  } else {
   $url='http://'.MASTER_HOST.$_SERVER['REQUEST_URI'];
  }
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
  curl_setopt($ch, CURLOPT_TIMEOUT, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $data=curl_exec($ch);
  curl_close($ch);
  exit;
 }

 // ---------------------------------------------------------------------------
 // NORMAL HANDLER
 chdir(dirname(__FILE__).'/..');

 include_once("./config.php");
 include_once("./lib/loader.php");


 startMeasure('TOTAL'); // start calculation of execution time

 include_once(DIR_MODULES."application.class.php");

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");


 if ($argv[1]!='') {
  //echo date('Y-m-d H:i:s')." command line call: ".$argv[1]."\n";
  //DebMes("command line call: ".$argv[1]);
  $commandLine=1;
  if (preg_match('/^(.+?)\.(.+?)$/is', $argv[1], $matches)) {
   $op="m";
   $object=$matches[1];
   $m=$matches[2];
  }

  $total=count($argv);
  for($i=1;$i<$total;$i++) {
   //echo "\n".'|'.$argv[$i].'|'."\n";
   if (preg_match('/^(.+?)[:=](.*?)$/is', $argv[$i], $matches)) {
    $_GET[$matches[1]]=trim(win2utf($matches[2]));
    ${$matches[1]}=trim(win2utf($matches[2]));
   } else {
    //echo "Arg: ".$argv[$i]."\n";
    $_GET['other_params'][]=$argv[$i];
    $other_params[]=$argv[$i];
   }
  }
 }

 foreach($_GET as $k=>$v) {
  $request.='&'.$k.'='.$v;
 }

 if (!$request && $commandLine) {
  $request=implode(' ', $argv);
 }

 //echo "object: $object op: $op m: $m status: $status ";exit;

 if (!$commandLine) {
  header ('Content-Type: text/html; charset=utf-8');
 }
 //echo "\nRequest: ".$request;
 //exit;

 //DebMes("Request: ".$request);

 if ($object!='') {
  //DebMes("object: ".$object);
  $obj=getObject($object);
  if ($obj) {
   //DebMes("object [".$object."] FOUND");
   if ($op=='get') {
    $value=$obj->getProperty($p);
    echo $value;
   }
   if ($op=='set') {
    $obj->setProperty($p, $v);
    echo "OK";
   }
   if ($op=='m') {
    $params=array();
    foreach($_GET as $k=>$v) {
     $params[$k]=${$k};
    }
    //DebMes("Calling method: ".$m.' '.serialize($params));
    //print_r($params);
    $obj->callMethod($m, $params);
   }
  } else {
   DebMes("object [".$object."] not found");
  }
 } elseif ($job!='') {
  $job=SQLSelectOne("SELECT * FROM jobs WHERE ID='".(int)$job."'");
  if ($job['ID']) {

                  try {
                   $code=$job['COMMANDS'];
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error in scheduled job code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                  }
                  echo "OK";
  }
 } elseif ($script!='') {
  echo "\nRunning script: ".$script;
  //DebMes("Running script: ".$script);
  runScript($script, $_GET);
 }

 $db->Disconnect(); // closing database connection

 endMeasure('TOTAL'); // end calculation of execution time
 //performanceReport(); // print performance report

// ob_end_flush();

?>
