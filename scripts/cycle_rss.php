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

 include_once(DIR_MODULES.'rss_channels/rss_channels.class.php');
 $rss_ch=new rss_channels();

 while(1) {

  //updating RSS channels
  $to_update=SQLSelect("SELECT ID, TITLE FROM rss_channels WHERE NEXT_UPDATE<=NOW() LIMIT 1");
  $total=count($to_update);
  for($i=0;$i<$total;$i++) {
   $rss_ch->updateChannel($to_update[$i]['ID']);
  }

  if (file_exists('./reboot')) {
   $db->Disconnect();
   exit;
  }

  sleep(1);


 }

?>