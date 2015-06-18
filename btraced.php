<?php

/**
 * Main project script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.1
 */

Define('BTRACED', 1);

if (!defined('ENVIRONMENT'))
{
   error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
   ini_set('display_errors', 0);
}

// Get the received data from the iPhone (XML data)
$body = file_get_contents('php://input');

if ($body === false)
{
   // Error loading XML..., send it back to the iPhone
   echo 'Error loading XML. No  data recieved';
   exit;
}

// Try to load the XML
$xml = simplexml_load_string($body);

// If there was an error report it...
if ($xml == false)
{
   // Error loading XML..., send it back to the iPhone
   echo '{ "id":902, "error":true, "message":"Cant load XML", "valid":true }';
   exit;
}
else
{
   // Get username and password
   $username = $xml->username;
   $password = $xml->password;
   
   // Optional: You can check the username and password against your database
   // Uncomment for hardcoded testing
   // if (($username != 'user') && ($password != 'test'))
   // {
   //    echo '{ "id":1, "error":true, "valid":true }';
   //    exit();
   // }
   
   // Get device identification
   $deviceId = $xml->devId;

   $_REQUEST['deviceid'] = $deviceId;
   
   // Prepare list of points
   $goodPointsList = "";
   
   // Start processing each travel
   foreach ($xml->travel as $travel)
   {
      // Get travel common information
      $travelId      = $travel->id;
      $travelName    = $travel->description;
      $travelLength  = $travel->length;
      $travelTime    = $travel->time;
      $travelTPoints = $travel->tpoints;
      
      // Prepare the succesful points
      $goodPointsList = '';
      
      // Process each point
      foreach ($travel->point as $point)
      {
         // Get all the information for this point
         $pointId        = $point->id;
         $pointDate      = date("Y-m-d H:i:s", trim($point->date));
         $pointLat       = $point->lat;
         $pointLon       = $point->lon;
         $pointSpeed     = $point->speed;
         $pointCourse    = $point->course;
         $pointHAccu     = $point->haccu;
         $pointBatt      = $point->bat;
         $pointVAccu     = $point->vaccu;
         $pointAltitude  = $point->altitude;
         $pointContinous = $point->continous;
         $pointTDist     = $point->tdist;
         $pointRDist     = $point->rdist;
         $pointTTime     = $point->ttime;

         $p = array();

         $p['TM']        = (int)trim($point->date);
         $p['LATITUDE']  = $point->lat;
         $p['LONGITUDE'] = $point->lon;
         $p['SPEED']     = $point->speed;
         $p['BATTERY']   = $point->bat;
         $p['ALTITUDE']  = $point->altitude;

         $all_points[] = $p;
         
         // Create SQL sentence
         
         /*
         $sql = "INSERT INTO tblBtracedTripsData(DevID, TripID, TripName, TripLength, TripTime, TripTotalPoints,
                                                 PointID, PointDate, PointLat, PointLon, PointSpeed, PointCourse,
                                                PointHAccu, PointBatt, PointVAccu, PointAltitude, PointContinuos,
                                                 PointTotalDistance, PointRelativeDistance, PointTotalTime)
                 VALUES ('$deviceId' , '$travelId', '$travelName', '$travelLength', '$travelTime', '$travelTPoints',
                         '$pointId', '$pointDate', '$pointLat', '$pointLon', '$pointSpeed', '$pointCourse',
                         '$pointHAccu', '$pointBatt', '$pointVAccu', '$pointAltitude', '$pointContinous',
                         '$pointTDist', '$pointRDist', '$pointTTime')";
         
         $insertResult = mysql_query($sql, $conexion);
         */
         
         $goodPointsList .= $pointId . ",";
      }
   }
   
   // Check if there was points
   if ($goodPointsList != "")
   {
      // Remove last comma
      $goodPointsList = substr($goodPointsList, 0, -1);
      
      // Send back the answer for the saved points
      echo '{"id":0, "tripid":' . $travelId . ',"points":[' . $goodPointsList . '],"valid":true}';
   }
   else
   {
      // Just OK, the code should never reach here as we always have points
      echo '{"id":0, "tripid":' . $travelId . ',"valid":true}';
      exit;
   }
}

/**
 * Summary of cmp
 * @param mixed $a A
 * @param mixed $b B
 * @return double|int
 */
function cmp($a, $b)
{
   if ($a['TM'] == $b['TM'])
      return 0;
   
   return ($a['TM'] > $b['TM']) ? -1 : 1;
}

usort($all_points, "cmp");

$cp = $all_points[0];

$_REQUEST['latitude']  = $cp['LATITUDE'];
$_REQUEST['longitude'] = $cp['LONGITUDE'];
$_REQUEST['altitude']  = $cp['ALTITUDE'];
$_REQUEST['speed']     = $cp['SPEED'];
$_REQUEST['battlevel'] = $cp['BATTERY'];

/*
$_POST['latitude']
$_POST['longitude']
$_POST['altitude']
$_POST['speed']
$_POST['provider']
$_POST['battlevel']
$_POST['charging']
*/

include_once('./gps.php');
