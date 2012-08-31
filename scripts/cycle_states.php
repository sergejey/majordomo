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

 DebMes("Running cycle: ".basename(__FILE__));

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl=new control_modules();



 while(1) {


 // check main system states
 $objects=getObjectsByClass('systemStates');
 $total=count($objects);
 for($i=0;$i<$total;$i++) {
  $old_state=getGlobal($objects[$i]['TITLE'].'.stateColor');
  callMethod($objects[$i]['TITLE'].'.checkState');
  $new_state=getGlobal($objects[$i]['TITLE'].'.stateColor');
  if ($new_state!=$old_state) {
   echo $objects[$i]['TITLE']." state changed to ".$new_state."\n";
   $params=array('STATE'=>$new_state);
   callMethod($objects[$i]['TITLE'].'.stateChanged', $params);
  }
 }


  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }

  sleep(1);


 }

?>