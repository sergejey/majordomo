<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

include_once(DIR_MODULES . 'yadevices/yadevices.class.php');

$yadevices = new yadevices();

$checked_time = 0;

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

//Конфиг
$yadevices->getConfig();
$reloadTime = $yadevices->config['RELOAD_TIME'];
if(empty($reloadTime)) {
  die();
}
$isOnCycle = $yadevices->config['STATUS_CYCLE'];

$cycleVarName = 'ThisComputer.'.str_replace('.php', '', basename(__FILE__)) . 'Run'; 

while(true) {
	if ($latest_check_cycle + 15 < time()) {
       $latest_check_cycle = time();
       //saveToCache("MJD:$cycleVarName", $latest_check_cycle);
       setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $latest_check_cycle, 1);
    }
	
	if ((time()-$latest_check) > $reloadTime) {
		$latest_check = time();
		if($isOnCycle == 1) {
		  $yadevices->refreshStations();
		  //$yadevices->refreshDevices();
		  $yadevices->refreshDevicesData();
		}
	}
	if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
		exit;
	}

	sleep(1);
}
