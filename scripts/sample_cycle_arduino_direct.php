<?php

/**
 * User: DnAp
 * Date: 12.06.13
 * Time: 22:27
 */

$uSleep = 300;

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

include_once(__DIR__ . '/php_serial.class.php');

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

$handle = opendir('/dev/');

if (!$handle)
{
   DebMes("Support only *nux system" . basename(__FILE__));
   exit;
}

$tty = array();

while (false !== ($entry = readdir($handle)))
{
   if(substr($entry, 0, 6) == 'ttyACM')
      $tty[] = substr($entry, 6);
}

if (empty($tty))
{
   DebMes("/dev/ttyACM* not found" . basename(__FILE__));
   exit;
}

sort($tty);
$updated_time = time();

try
{
   $serial = new PhpSerial;
   $serial->deviceSet("/dev/ttyACM" . end($tty));
   $serial->confBaudRate(9600);
   $serial->confParity("none");
   $serial->confCharacterLength(8);
   $serial->confStopBits(1);
   $data = "";
   
   if ($serial->deviceOpen())
   {
      DebMes("Open device " . end($tty) . ": " . basename(__FILE__));

      do
      {
         $data .= $serial->readPort();

         //GET /objects/?object=sensorGarage&op=m&m=statusChanged&status=%i HTTP/1.0
         $start = strpos($data, 'GET ');
         $end = strpos($data, ' HTTP/1.0');
         
         if ($start !== false && $end)
         {
            $url = BASE_URL . trim(substr($data, $start + 4, $end - $start - 4));
            $context  = stream_context_create(array('http' => array('timeout' => 5)));

            file_get_contents($url, false, $context);

            $data = substr($data, $end + 9);
         }

         if (!$updated_time || (time() - $updated_time) > 1 * 60 * 60)
         {
            //Log activity every hour
            DebMes("Cycle running OK: " . basename(__FILE__));
            $updated_time = time();
         }
         
         usleep($uSleep);
      }
      while(!file_exists('./reboot') || IsSet($_GET['onetime']));
   }
   else
   {
      DebMes("Not open device " . end($tty) . ": " . basename(__FILE__));
   }
}
catch (Exception $e)
{
   DebMes("Not open device " . end($tty) . " " . $e->getMessage() . ": " . basename(__FILE__));
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

