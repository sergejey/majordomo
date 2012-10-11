<?

 chdir(dirname(__FILE__).'/../');

 include_once("./config.php");
 include_once("./lib/loader.php");
 include_once("./lib/threads.php");

 set_time_limit(0);

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

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();

 if (defined('ONEWIRE_SERVER')) {
  include_once(DIR_MODULES.'onewire/onewire.class.php');
  $onw=new onewire();
 } else {
  exit;
 }


 while(1) {

  echo date("H:i:s")." running ".basename(__FILE__)."\n";

  if (!$updated_time || (time()-$updated_time)>1*60*60) {
   //Log activity every hour
   DebMes("Cycle running OK: ".basename(__FILE__));
   $updated_time=time();
  }


  $onw->updateDevices(); // check all 1wire devices
  $onw->updateDisplays();
  
  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }

  sleep(1);


 }

 DebMes("Unexpected close of cycle: ".basename(__FILE__));

?>