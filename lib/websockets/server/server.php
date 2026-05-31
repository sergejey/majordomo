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
if (!defined('WEBSOCKETS_HOST')) define('WEBSOCKETS_HOST', '0.0.0.0');
if (!defined('WEBSOCKETS_TLS')) define('WEBSOCKETS_TLS', 0);
if (!defined('WEBSOCKETS_MAX_CLIENTS')) define('WEBSOCKETS_MAX_CLIENTS', 100);
if (!defined('WEBSOCKETS_CHECK_ORIGIN')) define('WEBSOCKETS_CHECK_ORIGIN', 0);
if (!defined('WEBSOCKETS_MAX_CONNECTIONS_PER_IP')) define('WEBSOCKETS_MAX_CONNECTIONS_PER_IP', 100);
if (!defined('WEBSOCKETS_MAX_REQUESTS_PER_MINUTE')) define('WEBSOCKETS_MAX_REQUESTS_PER_MINUTE', 20000);
// 0 means "no inactivity timeout". This avoids forced reconnect loops
// for clients that mostly receive data and rarely send messages.
if (!defined('WEBSOCKETS_CLIENT_TIMEOUT')) define('WEBSOCKETS_CLIENT_TIMEOUT', 0);
if (!defined('WEBSOCKETS_ALLOWED_ORIGINS')) define('WEBSOCKETS_ALLOWED_ORIGINS', '');

function majordomoCreateWebSocketServer()
{
	$server = new \WebSocket\Server((string)WEBSOCKETS_HOST, (int)WEBSOCKETS_PORT, (bool)WEBSOCKETS_TLS);

	// server settings:
	$server->setMaxClients((int)WEBSOCKETS_MAX_CLIENTS);
	$server->setCheckOrigin((bool)WEBSOCKETS_CHECK_ORIGIN);
	$server->setMaxConnectionsPerIp((int)WEBSOCKETS_MAX_CONNECTIONS_PER_IP);
	$server->setMaxRequestsPerMinute((int)WEBSOCKETS_MAX_REQUESTS_PER_MINUTE);
	$server->setClientTimeout((int)WEBSOCKETS_CLIENT_TIMEOUT);

	$allowedOrigins = trim((string)WEBSOCKETS_ALLOWED_ORIGINS);
	if ($allowedOrigins !== '') {
		$origins = explode(',', $allowedOrigins);
		foreach ($origins as $origin) {
			$origin = trim($origin);
			if ($origin !== '') {
				$server->setAllowedOrigin($origin);
			}
		}
	}

	// Hint: Status application should not be removed as it displays usefull server informations:
	$server->registerApplication('status', \WebSocket\Application\StatusApplication::getInstance());
	$server->registerApplication('majordomo', \WebSocket\Application\MajordomoApplication::getInstance());

	return $server;
}

if (!debug_backtrace()) {
	$server = majordomoCreateWebSocketServer();
	$server->run();
}
