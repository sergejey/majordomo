<?
/**
* This file is part of MajorDoMo system. More details at http://smartliving.ru/
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by>
* @version 1.3
*/

 include_once("./config.php");
 include_once("./lib/loader.php");
 include_once(DIR_MODULES."panel.class.php");

 $session=new session("test");
 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");


 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();

 $app=new panel();

 if ($md!=$app->name) {
  $app->restoreParams();
 } else {
  $app->getParams();
 }

 $result=$app->run();

 // BEGIN: filter output
 if ($filterblock!='') {
  preg_match('/<!-- begin_data \['.$filterblock.'\] -->(.*?)<!-- end_data \['.$filterblock.'\] -->/is', $result, $match);
  $result=$match[1];
 }
 // END: filter output

 // BEGIN: language constants
 if (preg_match_all('/&\#060\#LANG_(.+?)\#&\#062/is', $result, $matches)) {
  $total=count($matches[0]);
  for($i=0;$i<$total;$i++) {
   if (preg_match('/value=["\']'.preg_quote($matches[0][$i]).'["\']/is', $result)) {
    continue;
   }
   if (defined('LANG_'.$matches[1][$i])) {
    $result=str_replace($matches[0][$i], constant('LANG_'.$matches[1][$i]), $result);
   } else {
    //echo "<b><font color='red'>Warning: <i>".'LANG_'.$matches[1][$i]."</i> not defined, please check dictionary file</font></b><br>";
    echo "'".$matches[1][$i]."'=>'',<br>";
   }
  }
 }
 // END: language constants

 if (!headers_sent()) {
  header ("HTTP/1.0: 200 OK\n");
  header ('Content-Type: text/html; charset=utf-8');
 }
 echo $result;

 $session->save();
 $db->Disconnect(); // closing database connection

?>