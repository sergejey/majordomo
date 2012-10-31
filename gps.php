<?
/**
* Main project script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/


 include_once("./config.php");
 include_once("./lib/loader.php");

 startMeasure('TOTAL'); // start calculation of execution time

 include_once(DIR_MODULES."application.class.php");

 $session=new session("prj");
 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database

// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total = count($settings);
for ($i = 0; $i < $total; $i ++)
        Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');
include_once (ROOT . 'languages/default.php');

if (defined('SETTINGS_SITE_TIMEZONE')) {
 ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
}



 if (IsSet($_POST['latitude']))  {
  //DebMes("GPS DATA RECEIVED: \n".serialize($_POST));
  if ($_POST['deviceid']) {
   $device=SQLSelectOne("SELECT * FROM gpsdevices WHERE DEVICEID='".DBSafe($_POST['deviceid'])."'");
   if (!$device['ID']) {
    $device=array();
    $device['DEVICEID']=$_POST['deviceid'];
    $device['TITLE']='New GPS Device';
    $device['ID']=SQLInsert('gpsdevices', $device);
    SQLExec("UPDATE gpslog SET DEVICE_ID='".$device['ID']."' WHERE DEVICEID='".DBSafe($_POST['deviceid'])."'");
   }
   $device['LAT']=$_POST['latitude'];
   $device['LON']=$_POST['longitude'];
   $device['UPDATED']=date('Y-m-d H:i:s');
   SQLUpdate('gpsdevices', $device);
  }

  $rec=array();
  $rec['ADDED']=date('Y-m-d H:i:s');
  $rec['LAT']=$_POST['latitude'];
  $rec['LON']=$_POST['longitude'];
  $rec['ALT']=round($_POST['altitude'], 2);
  $rec['PROVIDER']=$_POST['provider'];
  $rec['SPEED']=round($_POST['speed'], 2);
  $rec['BATTLEVEL']=$_POST['battlevel'];
  $rec['CHARGING']=(int)$_POST['charging'];
  $rec['DEVICEID']=$_POST['deviceid'];
  if ($device['ID']) {
   $rec['DEVICE_ID']=$device['ID'];
  }
  $rec['ID']=SQLInsert('gpslog', $rec);

  // checking locations
  $lat=(float)$_POST['latitude'];
  $lon=(float)$_POST['longitude'];

  $locations=SQLSelect("SELECT * FROM gpslocations");
  $total=count($locations);
  for($i=0;$i<$total;$i++) {
   //echo "<br>".$locations[$i]['TITLE'];
   if (!$locations[$i]['RANGE']) {
    $locations[$i]['RANGE']=500;
   }
   $distance=calculateTheDistance ($lat, $lon, $locations[$i]['LAT'], $locations[$i]['LON']);
   //echo ' ('.$locations[$i]['LAT'].' : '.$locations[$i]['LON'].') '.$distance.' m';
   if ($distance<=$locations[$i]['RANGE']) {

    Debmes("Device (".$device['TITLE'].") NEAR location ".$locations[$i]['TITLE']);
    // we are at location
    $rec['LOCATION_ID']=$locations[$i]['ID'];
    SQLUpdate('gpslog', $rec);

    $tmp=SQLSelectOne("SELECT * FROM gpslog WHERE DEVICE_ID='".$device['ID']."' AND ID!='".$rec['ID']."' ORDER BY ADDED DESC LIMIT 1");
    if ($tmp['LOCATION_ID']!=$locations[$i]['ID']) {
     Debmes("Device (".$device['TITLE'].") ENTERED location ".$locations[$i]['TITLE']);
     // entered location
     $gpsaction=SQLSelectOne("SELECT * FROM gpsactions WHERE LOCATION_ID='".$locations[$i]['ID']."' AND ACTION_TYPE=1 AND USER_ID='".$device['USER_ID']."'");
     if ($gpsaction['ID']) {
      $gpsaction['EXECUTED']=date('Y-m-d H:i:s');
      $gpsaction['LOG']=$gpsaction['EXECUTED']." Executed\n".$gpsaction['LOG'];
      SQLUpdate('gpsactions', $gpsaction);
      if ($gpsaction['SCRIPT_ID']) {
       runScript($gpsaction['SCRIPT_ID']);
      } elseif ($gpsaction['CODE']) {
       eval($gpsaction['CODE']);
      }
     }
    }

   } else {

    $tmp=SQLSelectOne("SELECT * FROM gpslog WHERE DEVICE_ID='".$device['ID']."' AND ID!='".$rec['ID']."' ORDER BY ADDED DESC LIMIT 1");
    if ($tmp['LOCATION_ID']==$locations[$i]['ID']) {
     Debmes("Device (".$device['TITLE'].") LEFT location ".$locations[$i]['TITLE']);
     // left location
     $gpsaction=SQLSelectOne("SELECT * FROM gpsactions WHERE LOCATION_ID='".$locations[$i]['ID']."' AND ACTION_TYPE=0 AND USER_ID='".$device['USER_ID']."'");
     if ($gpsaction['ID']) {
      $gpsaction['EXECUTED']=date('Y-m-d H:i:s');
      $gpsaction['LOG']=$gpsaction['EXECUTED']." Executed\n".$gpsaction['LOG'];
      SQLUpdate('gpsactions', $gpsaction);
      if ($gpsaction['SCRIPT_ID']) {
       runScript($gpsaction['SCRIPT_ID']);
      } elseif ($gpsaction['CODE']) {
       eval($gpsaction['CODE']);
      }
     }
    }

   }
  }

 }

 $tmp=SQLSelectOne("SELECT *, DATE_FORMAT(ADDED, '%H:%i') as DAT FROM shouts ORDER BY ADDED DESC LIMIT 1");

 if (!headers_sent()) {
  header ("HTTP/1.0: 200 OK\n");
  header ('Content-Type: text/html; charset=utf-8');
 }

 if (defined('BTRACED')) {
  echo "OK";
 } elseif ($tmp['MESSAGE']!='') {
  echo ' '.$tmp['DAT'].' '.transliterate($tmp['MESSAGE']);
 }




 $db->Disconnect(); // closing database connection

 endMeasure('TOTAL'); // end calculation of execution time
// Радиус земли


function calculateTheDistance ($latA, $lonA, $latB, $lonB) {
    
    define('EARTH_RADIUS', 6372795);

//    $lat1= $latA;
 //   $lat2= $la

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