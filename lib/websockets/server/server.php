<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details. */

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require(__DIR__ . '/lib/SplClassLoader.php');

$classLoader = new SplClassLoader('WebSocket', __DIR__ . '/lib');
$classLoader->register();

if (!defined('WEBSOCKETS_PORT')) define('WEBSOCKETS_PORT',8002);

$server = new \WebSocket\Server('0.0.0.0', WEBSOCKETS_PORT, false);

// server settings:
$server->setMaxClients(100);
$server->setCheckOrigin(false);
//$server->setAllowedOrigin('foo.lh');
$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(20000);

// Hint: Status application should not be removed as it displays usefull server informations:
$server->registerApplication('status', \WebSocket\Application\StatusApplication::getInstance());
$server->registerApplication('majordomo', \WebSocket\Application\MajordomoApplication::getInstance());

$server->run();