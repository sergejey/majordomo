<?
/*
* @version 0.1 (auto-set)
*/

/**
* 404-error handler
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
*/


if (!preg_match('/\/$/', $_SERVER["REQUEST_URI"])) {
 $file=basename($_SERVER["REQUEST_URI"]);
}


$ext=strtolower(substr($file, -3));
if ($ext=='jpg' || $ext=='gif' || $ext=='css') {
 header ("HTTP/1.0: 404 Page not found\n");
 exit;
}

if (preg_match("/\?(.*?)$/", $_SERVER["REQUEST_URI"], $matches)) {
 $redir_qry=$matches[1];
}

$file=preg_replace("/\.htm.*$/","",$file);
if ($file!='') {
 $fake_doc=$file;
}


include_once("./config.php");

// use this array for URL conversion rules
$requests=array(
 "/^\/menu\.html/is" => '?(application:{action=menu})',
 "/^\/menu\/(\d+?)\.html/is" => '?(application:{action=menu, parent_item=\1})',
 "/^\/popup\/(.+?)\.html/is" => '?(application:{action=\1, popup=1})',
 "/^\/page\/(\d+?)\.html/is" => '?(application:{action=layouts, popup=1}layouts:{view_mode=view_layouts, id=\1})',
 "/^\/getnextevent\.html/is" => '?(application:{action=events})',
 "/^\/getlatestnote\.html/is"  => '?(application:{action=getlatestnote})',
 "/^\/getlatestmp3\.html/is"  => '?(application:{action=getlatestmp3})',
 "/^\/design_sample\.html/is" => '?(application:{action=design_sample})',
 "/^\/docs\/(\d+)\.html/is" => '?(application:{action=docs, doc_id=\1})',
 "/^\/([\w-]+)\.html/is" => '?(application:{action=docs, doc_name=\1})'
);


foreach($requests as $key=>$value) {
 if (!$found && preg_match($key, $_SERVER["REQUEST_URI"], $matches)) {
  $link=$value;
  for($i=1;$i<count($matches);$i++) {
   $link=str_replace("\\$i", $matches[$i], $link);
  }
  $link=preg_replace('/\\\\(\d+?)/is', '', $link);
  $found=1;
 }
}

if (preg_match('/^moved:(.+)/is', $link, $matches)) {
 Header("HTTP/1.1 301 Moved Permanently"); 
 header("Location:".$matches[1]);
 exit;
}

include_once("./config.php");
include_once("./lib/loader.php");

if ($link!='') {
 $mdl=new module();
 $param_str=$mdl->parseLinks("<a href=\"$link\">");
 preg_match("<a href=\".+?\?pd=(.*?)\">", $param_str, $matches);
 $pd=$matches[1];
} else {
 header ("HTTP/1.0 404 Not Found");
 echo "The page cannot be found. Please use <a href='/'>this link</a> to continue browsing.";
 exit;
}

include_once("index.php");

?>