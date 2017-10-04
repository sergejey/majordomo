<?php

/*
 * @version 0.1 (auto-set)
 */

/**
 * 404-error handler
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 */
list($usec, $sec) = explode(" ",microtime());
$script_started_time = ((float)$usec + (float)$sec);


if (!preg_match('/\/$/', $_SERVER["REQUEST_URI"]))
   $file = basename($_SERVER["REQUEST_URI"]);

$ext = strtolower(substr($file, -3));

if ($ext == 'jpg' || $ext == 'gif' || $ext == 'css')
{
   header("HTTP/1.0: 404 Page not found\n");
   exit;
}

if (preg_match("/\?(.*?)$/", $_SERVER["REQUEST_URI"], $matches))
   $redir_qry = $matches[1];

$file = preg_replace("/\.htm.*$/","", $file);

if ($file != '')
   $fake_doc = $file;

include_once("./config.php");

// use this array for URL conversion rules
$requests = array(
   "/^\/panel\/event\/(\d+)\.html/is"  => '?(panel:{action=events})&md=events&view_mode=edit_events&id=\1',
   "/^\/panel\/script\/(\d+)\.html/is"  => '?(panel:{action=scripts})&md=scripts&view_mode=edit_scripts&id=\1',
   "/^\/panel\/command\/(\d+)\.html/is" => '?(panel:{action=commands})&md=commands&view_mode=edit_commands&id=\1',
    "/^\/panel\/xray\.html/is" => '?(panel:{action=xray})&md=xray',
   "/^\/panel\/linkedobject.html/is"    => '?(panel:{action=linkedobject})',
    "/^\/panel\/popup\/(.+?).html/is"   => '?(panel:{action=\1})&print=1',
   "/^\/panel\/class\/(\d+)\.html/is"   => '?(panel:{action=classes})&md=classes&view_mode=edit_classes&id=\1',
   "/^\/panel\/class\/(\d+)\/properties\.html/is"=> '?(panel:{action=classes})&md=classes&view_mode=edit_classes&id=\1&tab=properties',
   "/^\/panel\/class\/(\d+)\/methods\.html/is"=> '?(panel:{action=classes})&md=classes&view_mode=edit_classes&id=\1&tab=methods',
   "/^\/panel\/class\/(\d+)\/methods\/(\d+)\.html/is"=> '?(panel:{action=classes}classes:{view_mode=edit_classes, tab=methods, id=\1, instance=adm})&md=methods&view_mode=edit_methods&id=\2',
   "/^\/panel\/class\/(\d+)\/object\/(\d+)\.html/is"=> '?(panel:{action=classes}classes:{view_mode=edit_classes, tab=objects, id=\1, instance=adm})&md=objects&view_mode=edit_objects&id=\2',
   "/^\/panel\/class\/(\d+)\/object\/(\d+)\\/methods\.html/is"=> '?(panel:{action=classes}classes:{view_mode=edit_classes, tab=objects, id=\1, instance=adm})&md=objects&view_mode=edit_objects&id=\2&tab=methods',
   "/^\/panel\/class\/(\d+)\/object\/(\d+)\\/methods\/(\d+)\.html/is"=> '?(panel:{action=classes}classes:{view_mode=edit_classes, tab=objects, id=\1, instance=adm})&md=objects&view_mode=edit_objects&id=\2&tab=methods&overwrite=1&method_id=\3',
   "/^\/panel\/class\/(\d+)\/object\/(\d+)\\/properties\.html/is"=> '?(panel:{action=classes}classes:{view_mode=edit_classes, tab=objects, id=\1, instance=adm})&md=objects&view_mode=edit_objects&id=\2&tab=properties',
   "/^\/panel\/scene\/(\d+)\/elements\/(\d+)\\/state(\d+)\.html/is"=> '?(panel:{action=scenes})&md=scenes&view_mode=edit_scenes&id=\1&tab=elements&view_mode2=edit_elements&element_id=\2&state_id=\3',
   "/^\/panel\/scene\/(\d+)\/elements\/(\d+)\.html/is"=> '?(panel:{action=scenes})&md=scenes&view_mode=edit_scenes&id=\1&tab=elements&view_mode2=edit_elements&element_id=\2',
   "/^\/panel\/zwave\/(\d+)\.html/is"   => '?(panel:{action=zwave})&md=zwave&view_mode=edit_zwave_devices&id=\1',
   "/^\/panel\/devices\/(\d+)\.html/is"   => '?(panel:{action=devices})&md=devices&view_mode=edit_devices&id=\1',
   "/^\/panel\/app_gpstrack\/action_(\d+)\.html/is"=> '?(panel:{action=app_gpstrack})&md=app_gpstrack&data_source=gpsactions&view_mode=edit_gpsactions&id=\1',
   "/^\/panel\/pattern\/(\d+)\.html/is" => '?(panel:{action=patterns})&md=patterns&view_mode=edit_patterns&id=\1',
   "/^\/menu\.html/is"                  => '?(application:{action=menu})',
   "/^\/pages\.html/is"                 => '?(application:{action=pages})',
   "/^\/menu\/(\d+?)\.html/is"          => '?(application:{action=menu, parent_item=\1})',
   "/^\/popup\/(shoutbox)\.html/is"     => '?(application:{action=\1, popup=1, app_action=1})',
   "/^\/module\/(.+?)\.html/is"     => '?(application:{action=\1, popup=1, app_action=1})',
    "/^\/apps\/(.+?)\.html/is"     => '?(application:{action=apps, popup=1, app_action=\1})',
    "/^\/apps\.html/is"     => '?(application:{action=apps, popup=1})',
   "/^\/popup\/(.+?)\/(.+?)\.html/is"   => '?(application:{action=\1, popup=1})',
   "/^\/popup\/(.+?)\.html/is"          => '?(application:{action=\1, popup=1})',
   "/^\/ajax\/(.+?)\.html/is"           => '?(application:{action=\1, ajax=1})',
   "/^\/page\/(\w+?)\.html/is"          => '?(application:{action=layouts, popup=1}layouts:{view_mode=view_layouts, id=\1})',
   "/^\/getnextevent\.html/is"          => '?(application:{action=events})',
   "/^\/getlatestnote\.html/is"         => '?(application:{action=getlatestnote})',
   "/^\/getlatestmp3\.html/is"          => '?(application:{action=getlatestmp3})',
   "/^\/design_sample\.html/is"         => '?(application:{action=design_sample})',
   "/^\/docs\/(\d+)\.html/is"           => '?(application:{action=docs, doc_id=\1})',
   "/^\/([\w-]+)\.html/is"              => '?(application:{action=docs, doc_name=\1})'
);

$found=0;
foreach($requests as $key => $value)
{
   if (!$found && preg_match($key, $_SERVER["REQUEST_URI"], $matches))
   {
      $link = $value;

      $matchesCount = count($matches);
      
      for ($i = 1; $i < $matchesCount; $i++)
      {
         $link = str_replace("\\$i", $matches[$i], $link);
      }
  
      $link  = preg_replace('/\\\\(\d+?)/is', '', $link);
      $found = 1;
   }
}

if (preg_match('/^moved:(.+)/is', $link, $matches))
{
   Header("HTTP/1.1 301 Moved Permanently");
   header("Location:" . $matches[1]);
   exit;
}

include_once("./lib/perfmonitor.class.php");

startMeasure('TOTAL');
include_once("./config.php");
startMeasure('loader');
include_once("./lib/loader.php");
endMeasure('loader');

if ($link != '')
{
   $mdl       = new module();
   $param_str = $mdl->parseLinks("<a href=\"$link\">");
 
   if (preg_match("/<a href=\".+?\?pd=(.*?)&(.+)\">/", $param_str, $matches))
   {
      $pd    = $matches[1];
      $other = $matches[2];
      $tmp   = explode('&', $other);

      foreach ($tmp as $pair)
      {
         $tmp2 = explode('=', $pair);
         if (isset($tmp2[1])) {
            $_GET[$tmp2[0]] = $tmp2[1];
            ${$tmp2[0]}     = $tmp2[1];
         }
      }
   }
   elseif (preg_match("/<a href=\".+?\?pd=(.*?)\">/", $param_str, $matches))
   {
      $pd = $matches[1];
   }
}
else
{
   header("HTTP/1.0 404 Not Found");
   echo "The page cannot be found. Please use <a href='/'>this link</a> to continue browsing.";
   exit;
}

if (preg_match('/^\/panel\//is', $_SERVER['REQUEST_URI']))
   include_once("admin.php");
else
   include_once("index.php");

