<?php

function mjdGetRedisConnection()
{
    if (!defined('USE_REDIS') || !USE_REDIS) {
        return null;
    }

    global $redisConnection;
    if (isset($redisConnection) && is_object($redisConnection)) {
        return $redisConnection;
    }

    $host = (string)USE_REDIS;
    if ($host === '') {
        $host = '127.0.0.1';
    }

    $port = 6379;
    if (defined('REDIS_PORT')) {
        $port = (int)REDIS_PORT;
    } elseif (defined('SETTINGS_SYSTEM_REDIS_PORT')) {
        $port = (int)SETTINGS_SYSTEM_REDIS_PORT;
    }

    $db = 0;
    if (defined('REDIS_DB')) {
        $db = (int)REDIS_DB;
    } elseif (defined('SETTINGS_SYSTEM_REDIS_DB')) {
        $db = (int)SETTINGS_SYSTEM_REDIS_DB;
    }

    $username = '';
    if (defined('REDIS_USERNAME')) {
        $username = (string)REDIS_USERNAME;
    } elseif (defined('SETTINGS_SYSTEM_REDIS_USERNAME')) {
        $username = (string)SETTINGS_SYSTEM_REDIS_USERNAME;
    }

    $password = '';
    if (defined('REDIS_PASSWORD')) {
        $password = (string)REDIS_PASSWORD;
    } elseif (defined('SETTINGS_SYSTEM_REDIS_PASSWORD')) {
        $password = (string)SETTINGS_SYSTEM_REDIS_PASSWORD;
    }

    $timeout = 2.5;
    if (defined('REDIS_TIMEOUT')) {
        $timeout = (float)REDIS_TIMEOUT;
    } elseif (defined('SETTINGS_SYSTEM_REDIS_TIMEOUT')) {
        $timeout = (float)SETTINGS_SYSTEM_REDIS_TIMEOUT;
    }

    try {
        $redis = new Redis();
        if (!@$redis->pconnect($host, $port, $timeout)) {
            return null;
        }

        if ($password !== '') {
            if ($username !== '') {
                if (!@$redis->auth(array($username, $password))) {
                    return null;
                }
            } else {
                if (!@$redis->auth($password)) {
                    return null;
                }
            }
        }

        if ($db > 0) {
            if (!@$redis->select($db)) {
                return null;
            }
        }

        $redisConnection = $redis;
        return $redisConnection;
    } catch (Throwable $e) {
        return null;
    }
}
