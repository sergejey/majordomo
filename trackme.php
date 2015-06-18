<?php

/**
* Main project script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/

Define('BTRACED', 1);

if ($_GET['a'] == 'upload')
{
   $_POST['latitude']  = $_GET['lat'];
   $_POST['longitude'] = $_GET['long'];
   $_POST['altitude']  = $_GET['alt'];
   $_POST['speed']     = $_GET['sp'];
   $_POST['battlevel'] = $_GET["bs"];
   $_POST['deviceid']  = 'TrackMe';
 
   include_once('./gps.php');
 
   echo "Result:0";
}

