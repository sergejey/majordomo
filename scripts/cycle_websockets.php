<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");

if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
 echo "Web-sockets disabled\n";
 exit;
}


include_once(DIR_MODULES . "control_modules/control_modules.class.php");

include_once(DIR_MODULES . 'scenes/scenes.class.php');
$scenes = new scenes();

include_once(DIR_MODULES . 'plans/plans.class.php');
$plans= new plans();

include_once(DIR_MODULES . 'commands/commands.class.php');
$commands = new commands();

if (file_exists(DIR_MODULES . 'devices/devices.class.php')) {
 include_once(DIR_MODULES . 'devices/devices.class.php');
 $devices = new devices();
}

include_once(DIR_MODULES . 'objects/objects.class.php');
$objects_module = new objects();


//Define('DEBUG_WEBSOCKETS', 1);

$websockets_script_started=time();

$cycleName=str_replace('.php', '', basename(__FILE__)) . 'Run';
setGlobal(str_replace('.php', '', basename(__FILE__)) . 'Run', time(), 1);

if (!defined('WEBSOCKETS_PORT')) define('WEBSOCKETS_PORT',8001);

require_once('./lib/WS/Connection.php');
require_once('./lib/WS/Socket.php');
require_once('./lib/WS/Server.php');
require_once('./lib/WS/Timer.php');
require_once('./lib/WS/TimerCollection.php');
require_once('./lib/WS/Application/ApplicationInterface.php');
require_once('./lib/WS/Application/Application.php');
require_once('./lib/WS/Application/MajordomoApplication.php');
require_once('./lib/WS/Application/StatusApplication.php');


$server = new \Bloatless\WebSocket\Server('0.0.0.0', WEBSOCKETS_PORT);

// server settings:
$server->setMaxClients(100);
$server->setCheckOrigin(false);
//$server->setAllowedOrigin('foo.lh');
$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(2000);

// Hint: Status application should not be removed as it displays usefull server informations:
$server->registerApplication('status', \Bloatless\WebSocket\Application\StatusApplication::getInstance());
$server->registerApplication('majordomo', \Bloatless\WebSocket\Application\MajordomoApplication::getInstance());

$server->run();