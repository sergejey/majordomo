<?php
/**
* Main project script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/

include_once("./config.php");
include_once("./lib/loader.php");

// start calculation of execution time
startMeasure('TOTAL'); 

include_once(DIR_MODULES."application.class.php");

$session = new session("prj");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 

include_once("./load_settings.php");

if ($_REQUEST['location']) 
{
   $tmp=explode(',', $_REQUEST['location']);
  
   $_REQUEST['latitude']  = $tmp[0];
   $_REQUEST['longitude'] = $tmp[1];
}

if (IsSet($_REQUEST['latitude']))  
{
   //DebMes("GPS DATA RECEIVED: \n".serialize($_REQUEST));
   if ($_REQUEST['deviceid']) 
   {
      $device = SQLSelectOne("SELECT * FROM gpsdevices WHERE DEVICEID='" . DBSafe($_REQUEST['deviceid']) . "'");
   
      if (!$device['ID']) 
      {
         $device = array();
         $device['DEVICEID'] = $_REQUEST['deviceid'];
         $device['TITLE']    = 'New GPS Device';
         $device['ID']       = SQLInsert('gpsdevices', $device);
         
         SQLExec("UPDATE gpslog SET DEVICE_ID='" . $device['ID'] . "' WHERE DEVICEID='" . DBSafe($_REQUEST['deviceid']) . "'");
      }
      
      $device['LAT']     = $_REQUEST['latitude'];
      $device['LON']     = $_REQUEST['longitude'];
      $device['UPDATED'] = date('Y-m-d H:i:s');
      
      SQLUpdate('gpsdevices', $device);
   }

   $rec = array();
   $rec['ADDED']     = date('Y-m-d H:i:s');
   $rec['LAT']       = $_REQUEST['latitude'];
   $rec['LON']       = $_REQUEST['longitude'];
   $rec['ALT']       = round($_REQUEST['altitude'], 2);
   $rec['PROVIDER']  = $_REQUEST['provider'];
   $rec['SPEED']     = round($_REQUEST['speed'], 2);
   $rec['BATTLEVEL'] = $_REQUEST['battlevel'];
   $rec['CHARGING']  = (int)$_REQUEST['charging'];
   $rec['DEVICEID']  = $_REQUEST['deviceid'];
   $rec['ACCURACY']  = isset($_REQUEST['accuracy']) ? $_REQUEST['accuracy'] : 0;
   
   if ($device['ID']) $rec['DEVICE_ID'] = $device['ID'];
  
   $rec['ID'] = SQLInsert('gpslog', $rec);

   if ($device['USER_ID']) 
   {
      $user=SQLSelectOne("select * from USERS where ID = '" . $device['USER_ID'] . "'");
      if ($user['LINKED_OBJECT']) 
      {
         setGlobal($user['LINKED_OBJECT'] . '.Coordinates', $rec['LAT'] . ',' . $rec['LON']);
         setGlobal($user['LINKED_OBJECT'] . '.CoordinatesUpdated', date('H:i'));
         
         $prev_log = SQLSelectOne("select * from GPSLOG where ID != '" . $rec['ID'] . "' order by ID desc limit 1");
         if ($prev_log['ID']) 
         {
            $distance = calculateTheDistance($rec['LAT'], $rec['LON'], $prev_log['LAT'], $prev_log['LON']);
            if ($distance > 100) //we're moving 
            {
               setGlobal($user['LINKED_OBJECT'] . '.isMoving', 1);
               clearTimeOut($user['LINKED_OBJECT'] . '_moving');
               // stopped after 15 minutes of inactivity
               setTimeOut($user['LINKED_OBJECT'] . '_moving', "setGlobal('" . $user['LINKED_OBJECT'] . ".isMoving', 0);", 15*60); 
            }
         }
      }
   }

   // checking locations
   $lat = (float)$_REQUEST['latitude'];
   $lon = (float)$_REQUEST['longitude'];

   $locations      = SQLSelect("select * from GPSLOCATIONS");
   $total          = count($locations);
   $location_found = 0;
  
   for($i = 0; $i < $total; $i++) 
   {
      //echo "<br>".$locations[$i]['TITLE'];
      if (!$locations[$i]['RANGE'])  $locations[$i]['RANGE'] = 500;
      
      $distance = calculateTheDistance($lat, $lon, $locations[$i]['LAT'], $locations[$i]['LON']);
      
      //echo ' ('.$locations[$i]['LAT'].' : '.$locations[$i]['LON'].') '.$distance.' m';
      if ($distance<=$locations[$i]['RANGE']) 
      {
         //Debmes("Device (" . $device['TITLE'] . ") NEAR location " . $locations[$i]['TITLE']);
         $location_found = 1;
         
         if ($user['LINKED_OBJECT']) 
            setGlobal($user['LINKED_OBJECT'].'.seenAt', $locations[$i]['TITLE']);
         
         // we are at location
         $rec['LOCATION_ID'] = $locations[$i]['ID'];
    
         SQLUpdate('gpslog', $rec);

         $tmp = SQLSelectOne("select * from GPSLOG where DEVICE_ID = '" . $device['ID'] . "' AND ID != '" . $rec['ID'] . "' order by ADDED desc limit 1");
         
         if ($tmp['LOCATION_ID']!=$locations[$i]['ID']) 
         {
            Debmes("Device (" . $device['TITLE'] . ") ENTERED location " . $locations[$i]['TITLE']);
     
            // entered location
            $gpsaction = SQLSelectOne("select * from GPSACTIONS where LOCATION_ID = '" . $locations[$i]['ID'] . "' AND ACTION_TYPE = 1 AND USER_ID = '" . $device['USER_ID'] . "'");
     
            if ($gpsaction['ID']) 
            {
               $gpsaction['EXECUTED'] = date('Y-m-d H:i:s');
               $gpsaction['LOG']      = $gpsaction['EXECUTED'] . " Executed\n" . $gpsaction['LOG'];
      
               SQLUpdate('gpsactions', $gpsaction);
      
               if ($gpsaction['SCRIPT_ID']) 
               {
                  runScript($gpsaction['SCRIPT_ID']);   
               } 
               elseif ($gpsaction['CODE']) 
               {
                  eval($gpsaction['CODE']);
               }
            }
         }
      } 
      else 
      {
         $tmp = SQLSelectOne("select * from GPSLOG where DEVICE_ID = '" . $device['ID'] . "' and ID != '" . $rec['ID'] . "' order by ADDED desc limit 1");
    
         if ($tmp['LOCATION_ID'] == $locations[$i]['ID']) 
         {
            Debmes("Device (" . $device['TITLE'] . ") LEFT location " . $locations[$i]['TITLE']);
     
            // left location
            $gpsaction = SQLSelectOne("select * from GPSACTIONS where LOCATION_ID = '" . $locations[$i]['ID'] . "' and ACTION_TYPE = 0 and USER_ID = '" . $device['USER_ID'] . "'");
     
            if ($gpsaction['ID']) 
            {
               $gpsaction['EXECUTED'] = date('Y-m-d H:i:s');
               $gpsaction['LOG']      = $gpsaction['EXECUTED'] . " Executed\n" . $gpsaction['LOG'];
      
               SQLUpdate('gpsactions', $gpsaction);
     
               if ($gpsaction['SCRIPT_ID']) 
               {
                  runScript($gpsaction['SCRIPT_ID']);
               } 
               elseif ($gpsaction['CODE']) 
               {
                  eval($gpsaction['CODE']);
               }
            }
         }
      }
   }
}

if ($user['LINKED_OBJECT'] && !$location_found) 
   setGlobal($user['LINKED_OBJECT'].'.seenAt', '');

$tmp = SQLSelectOne("select *, date_format(ADDED, '%H:%i') as DAT from SHOUTS order by ADDED desc limit 1");

if (!headers_sent()) 
{
   header("HTTP/1.0: 200 OK\n");
   header('Content-Type: text/html; charset=utf-8');
}

if (defined('BTRACED')) 
{
   echo "OK";
} 
elseif ($tmp['MESSAGE']!='') 
{
   echo ' ' . $tmp['DAT'] .' ' . transliterate($tmp['MESSAGE']);
}

// closing database connection
$db->Disconnect(); 

// end calculation of execution time
endMeasure('TOTAL'); 

// Радиус земли
function calculateTheDistance($latA, $lonA, $latB, $lonB) 
{
   define('EARTH_RADIUS', 6372795);
   //$lat1= $latA;
   //$lat2= $la

   $lat1  = $latA * M_PI / 180;
   $lat2  = $latB * M_PI / 180;
   $long1 = $lonA * M_PI / 180;
   $long2 = $lonB * M_PI / 180;

   $cl1    = cos($lat1);
   $cl2    = cos($lat2);
   $sl1    = sin($lat1);
   $sl2    = sin($lat2);
   $delta  = $long2 - $long1;
   $cdelta = cos($delta);
   $sdelta = sin($delta);

   $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
   $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

   //
   $ad   = atan2($y, $x);
   $dist = $ad * EARTH_RADIUS;

   return round($dist);
}

?>