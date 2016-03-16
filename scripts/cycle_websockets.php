<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

if (defined('DISABLE_WEBSOCKETS') && DISABLE_WEBSOCKETS==1) {
 echo "Web-sockets disabled\n";
 exit;
}


include_once(DIR_MODULES . "control_modules/control_modules.class.php");

include_once(DIR_MODULES . 'scenes/scenes.class.php');
$scenes = new scenes();

include_once(DIR_MODULES . 'commands/commands.class.php');
$commands = new commands();

//Define('DEBUG_WEBSOCKETS', 1);

$websockets_script_started=time();

$cycleName=str_replace('.php', '', basename(__FILE__)) . 'Run';
setGlobal($cycleName, time(), 1);

require_once('./lib/websockets/server/server.php');

$db->Disconnect(); // closing database connection
