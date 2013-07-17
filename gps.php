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

if ($_POST['location']) 
{
   $tmp=explode(',', $_POST['location']);
  
   $_POST['latitude']  = $tmp[0];
   $_POST['longitude'] = $tmp[1];
}

if (IsSet($_POST['latitude']))  
{
   //DebMes("GPS DATA RECEIVED: \n".serialize($_POST));
   if ($_POST['deviceid']) 
   {
      $device = SQLSelectOne("SELECT * FROM gpsdevices WHERE DEVICEID='" . DBSafe($_POST['deviceid']) . "'");
   
      if (!$device['ID']) 
      {
         $device = array();
         $device['DEVICEID'] = $_POST['deviceid'];
         $device['TITLE']    = 'New GPS Device';
         $device['ID']       = SQLInsert('gpsdevices', $device);
         
         SQLExec("UPDATE gpslog SET DEVICE_ID='" . $device['ID'] . "' WHERE DEVICEID='" . DBSafe($_POST['deviceid']) . "'");
      }
      
      $device['LAT']     = $_POST['latitude'];
      $device['LON']     = $_POST['longitude'];
      $device['UPDATED'] = date('Y-m-d H:i:s');
      
      SQLUpdate('gpsdevices', $device);
   }

   $rec=array();
   $rec['ADDED']     = date('Y-m-d H:i:s');
   $rec['LAT']       = $_POST['latitude'];
   $rec['LON']       = $_POST['longitude'];
   $rec['ALT']       = round($_POST['altitude'], 2);
   $rec['PROVIDER']  = $_POST['provider'];
   $rec['SPEED']     = round($_POST['speed'], 2);
   $rec['BATTLEVEL'] = $_POST['battlevel'];
   $rec['CHARGING']  = (int)$_POST['charging'];
   $rec['DEVICEID']  = $_POST['deviceid'];
   
   if ($device['ID']) $rec['DEVICE_ID']=$device['ID'];
  
   $rec['ID']=SQLInsert('gpslog', $rec);

   if ($device['USER_ID']) {
    $user=SQLSelectOne("SELECT * FROM users WHERE ID='".$device['USER_ID']."'");
    if ($user['LINKED_OBJECT']) {
     setGlobal($user['LINKED_OBJECT'].'.Coordinates', $rec['LAT'].','.$rec['LON']);
     setGlobal($user['LINKED_OBJECT'].'.CoordinatesUpdated', date('H:i'));
     $prev_log=SQLSelectOne("SELECT * FROM gpslog WHERE ID!='".$rec['ID']."' ORDER BY ID DESC LIMIT 1");
     if ($prev_log['ID']) {
      $distance=calculateTheDistance ($rec['LAT'], $rec['LON'], $prev_log['LAT'], $prev_log['LON']);
      if ($distance>100) {
       //we're moving
       setGlobal($user['LINKED_OBJECT'].'.isMoving', 1);
       clearTimeOut($user['LINKED_OBJECT'].'_moving');
       setTimeOut($user['LINKED_OBJECT'].'_moving', "setGlobal(".$user['LINKED_OBJECT'].".isMoving', 0);", 15*60); // stopped after 15 minutes of inactivity
      }
     }
    }
   }

   // checking locations
   $lat = (float)$_POST['latitude'];
   $lon = (float)$_POST['longitude'];

   $locations = SQLSelect("SELECT * FROM gpslocations");
   $total     = count($locations);

   $location_found=0;
  
   for($i=0;$i<$total;$i++) 
   {
      //echo "<br>".$locations[$i]['TITLE'];
      if (!$locations[$i]['RANGE'])  $locations[$i]['RANGE']=500;
      
      $distance=calculateTheDistance ($lat, $lon, $locations[$i]['LAT'], $locations[$i]['LON']);
      
      //echo ' ('.$locations[$i]['LAT'].' : '.$locations[$i]['LON'].') '.$distance.' m';
      if ($distance<=$locations[$i]['RANGE']) 
      {
         //Debmes("Device (" . $device['TITLE'] . ") NEAR location " . $locations[$i]['TITLE']);
         $location_found=1;
         if ($user['LINKED_OBJECT']) {
          setGlobal($user['LINKED_OBJECT'].'.seenAt', $locations[$i]['TITLE']);
         }
    
         // we are at location
         $rec['LOCATION_ID'] = $locations[$i]['ID'];
    
         SQLUpdate('gpslog', $rec);

         $tmp = SQLSelectOne("SELECT * FROM gpslog WHERE DEVICE_ID='" . $device['ID'] . "' AND ID != '" . $rec['ID'] . "' ORDER BY ADDED DESC LIMIT 1");
         
         if ($tmp['LOCATION_ID']!=$locations[$i]['ID']) 
         {
            Debmes("Device (" . $device['TITLE'] . ") ENTERED location " . $locations[$i]['TITLE']);
     
            // entered location
     
            $gpsaction = SQLSelectOne("SELECT * FROM gpsactions WHERE LOCATION_ID='" . $locations[$i]['ID'] . "' AND ACTION_TYPE = 1 AND USER_ID = '" . $device['USER_ID'] . "'");
     
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
         $tmp = SQLSelectOne("SELECT * FROM gpslog WHERE DEVICE_ID= '" . $device['ID'] . "' AND ID != '" . $rec['ID'] . "' ORDER BY ADDED DESC LIMIT 1");
    
         if ($tmp['LOCATION_ID'] == $locations[$i]['ID']) 
         {
            Debmes("Device (" . $device['TITLE'] . ") LEFT location " . $locations[$i]['TITLE']);
     
            // left location
            $gpsaction = SQLSelectOne("SELECT * FROM gpsactions WHERE LOCATION_ID = '" . $locations[$i]['ID'] . "' AND ACTION_TYPE = 0 AND USER_ID = '" . $device['USER_ID'] . "'");
     
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

if ($user['LINKED_OBJECT'] && !$location_found) {
 setGlobal($user['LINKED_OBJECT'].'.seenAt', '');
}


$tmp = SQLSelectOne("SELECT *, DATE_FORMAT(ADDED, '%H:%i') as DAT FROM shouts ORDER BY ADDED DESC LIMIT 1");

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

endMeasure('TOTAL'); // end calculation of execution time

// Радиус земли
function calculateTheDistance ($latA, $lonA, $latB, $lonB) 
{
   define('EARTH_RADIUS', 6372795);
   //$lat1= $latA;
   //$lat2= $la

   $lat1 = $latA * M_PI / 180;
   $lat2 = $latB * M_PI / 180;
   $long1 = $lonA * M_PI / 180;
   $long2 = $lonB * M_PI / 180;

   $cl1 = cos($lat1);
   $cl2 = cos($lat2);
   $sl1 = sin($lat1);
   $sl2 = sin($lat2);
   $delta = $long2 - $long1;
   $cdelta = cos($delta);
   $sdelta = sin($delta);

   $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
   $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

   //
   $ad = atan2($y, $x);
   $dist = $ad * EARTH_RADIUS;

   return round($dist);
}

?>