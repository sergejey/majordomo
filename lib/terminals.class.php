<?php

// Get all terminals
function getAllTerminals($limit = -1, $order = 'ID', $sort = 'ASC') {
	$sqlQuery = 'SELECT * FROM `terminals` ORDER BY `'.DBSafe($order).'` '.DBSafe($sort);
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}

// Get terminal by id
function getTerminalByID($id) {
	$sqlQuery = 'SELECT * FROM `terminals` WHERE `ID` = '.abs(intval($id));
	$terminal = SQLSelectOne($sqlQuery);
	return $terminal;
}

// Get terminal by name
function getTerminalsByName($name, $limit = -1, $order = 'ID', $sort = 'ASC') {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `NAME` = '".DBSafe($name)."' OR `TITLE` = '".DBSafe($name)."' ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}

// Get terminals by host or ip address
function getTerminalsByHost($host, $limit = -1, $order = 'ID', $sort = 'ASC') {
	$localhost = array(
		'localhost',
		'127.0.0.1',
		'ip6-localhost',
		'ip6-loopback',
		'ipv6-localhost',
		'ipv6-loopback',
		'::1',
		'0:0:0:0:0:0:0:1',
	);
	if(in_array(strtolower($host), $localhost)) {
		$sqlQuery = "SELECT * FROM `terminals` WHERE `HOST` = '".implode("' OR `HOST` = '", $localhost)."' ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	} else {
		$sqlQuery = "SELECT * FROM `terminals` WHERE `HOST` = '".DBSafe($host)."' ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	}
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}

// Get terminals that can play
function getTerminalsCanPlay($limit = -1, $order = 'ID', $sort = 'ASC') {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `CANPLAY` = 1 ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}

// Get terminals by player type
function getTerminalsByPlayer($player, $limit = -1, $order = 'ID', $sort = 'ASC') {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `PLAYER_TYPE` = '".DBSafe($player)."' ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}

// Get main terminal
function getMainTerminal() {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `NAME` = 'MAIN'";
	$terminal = SQLSelectOne($sqlQuery);
	return $terminal;
}

// Get online terminals
function getOnlineTerminals($limit = -1, $order = 'ID', $sort = 'ASC') {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `IS_ONLINE` = 1 ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}

// Get MajorDroid terminals
function getMajorDroidTerminals($limit = -1, $order = 'ID', $sort = 'ASC') {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `MAJORDROID_API` = 1 ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	if($limit >= 0) {
		$sqlQuery .= ' LIMIT '.intval($limit);
	}
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}
// Get terminals by CANTTS
function getTerminalsByCANTTS($order = 'ID', $sort = 'ASC') {
	$sqlQuery = "SELECT * FROM `terminals` WHERE `CANTTS` = '".DBSafe('1')."' ORDER BY `".DBSafe($order)."` ".DBSafe($sort);
	if(!$terminals = SQLSelect($sqlQuery)) {
		$terminals = array(NULL);
	}
	return $terminals;
}
// Get local ip 
function getLocalIp() {
	global $local_ip_address_cached;
	if (isset($local_ip_address_cached)) {
		$local_ip_address=$local_ip_address_cached;
        } else if ($_SERVER['SERVER_ADDR'] != '127.0.0.1') {
                $local_ip_address = $_SERVER['SERVER_ADDR'];
	} else {
		$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_connect($s, '8.8.8.8', 53);  // connecting to a UDP address doesn't send packets
		socket_getsockname($s, $local_ip_address, $port);
		@socket_shutdown($s, 2);
		socket_close($s);
		if (!$local_ip_address) {
			$main_terminal=getTerminalsByName('MAIN')[0];
			if ($main_terminal['HOST']) {
				$local_ip_address=$main_terminal['HOST'];
			}
		}
		if ($local_ip_address) {
			$local_ip_address_cached=$local_ip_address;
		}
	}
	return $local_ip_address;
}

/**
 * This function change  position on the played media in player
 * @param mixed $host Host (default 'localhost') name or ip of terminal
 * @param mixed $time second (default 0) to positon from start time
 */
function seekPlayerPosition($host = 'localhost', $time = 0)
{
    if (!$terminal = getTerminalsByName($host, 1)[0]) {
        $terminal = getTerminalsByHost($host, 1)[0];
    }
    if (!$terminal) {
        return;
    }
    include_once(DIR_MODULES . 'app_player/app_player.class.php');
    $player = new app_player();
    $player->play_terminal = $terminal['NAME']; // Имя терминала
    $player->command = 'seek'; // Команда
    $player->param = $time; // Параметр
    $player->ajax = TRUE;
    $player->intCall = TRUE;
    $player->usual($out);

    return $player->json['message'];
}

/**
 * Summary of player status
 * @param mixed $host Host (default 'localhost') name or ip of terminal
 * @return  'id'              => (int), //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
 *          'name'            => (string), //Playback status. String: stopped/playing/paused/transporting/unknown
 *          'file'            => (string), //Current link for media in device. String.
 *          'track_id'        => (int)$track_id, //ID of currently playing track (in playlist). Integer. If unknown (playback stopped or playlist is empty) = -1.
 *          'length'          => (int)$length, //Track length in seconds. Integer. If unknown = 0.
 *          'time'            => (int)$time, //Current playback progress (in seconds). If unknown = 0.
 *          'state'           => (string)$state, //Playback status. String: stopped/playing/paused/unknown
 *          'volume'          => (int)$volume, // Volume level in percent. Integer. Some players may have values greater than 100.
 *          'random'          => (boolean)$random, // Random mode. Boolean.
 *          'loop'            => (boolean)$loop, // Loop mode. Boolean.
 *          'repeat'          => (boolean)$repeat, //Repeat mode. Boolean.
 */
function getPlayerStatus($host = 'localhost')
{
    if (!$terminal = getTerminalsByName($host, 1)[0]) {
        $terminal = getTerminalsByHost($host, 1)[0];
    }
    if (!$terminal) {
        return;
    }
    include_once(DIR_MODULES . 'app_player/app_player.class.php');
    $player = new app_player();
    $player->play_terminal = $terminal['NAME']; // Имя терминала
    $player->command = 'pl_get'; // Команда
    $player->ajax = TRUE;
    $player->intCall = TRUE;
    $player->usual($out);
    $terminal = array();
    if ($player->json['success'] && is_array($player->json['data'])) {
        $terminal = array_merge($terminal, $player->json['data']);
        //DebMes($player->json['data']);
    } else {
        // Если произошла ошибка, выводим ее описание
        return ($player->json['message']);
    }
    $player->command = 'status'; // Команда
    $player->ajax = TRUE;
    $player->intCall = TRUE;
    $player->usual($out);
    if ($player->json['success'] && is_array($player->json['data'])) {
        $terminal = array_merge($terminal, $player->json['data']);
        //DebMes($player->json['data']);
        return ($terminal);
    } else {
        // Если произошла ошибка, выводим ее описание
        return ($player->json['message']);
    }
}

function getMediaDurationSeconds($file)
{
    if (!defined('PATH_TO_FFMPEG')) {
        if (IsWindowsOS()) {
            define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
        } else {
            define("PATH_TO_FFMPEG", 'ffmpeg');
        }
    }
    $dur = shell_exec(PATH_TO_FFMPEG . " -i " . $file . " 2>&1");
    if (preg_match("/: Invalid /", $dur)) {
        return false;
    }
    preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
    if (!isset($duration[1])) {
        return false;
    }
    $hours = $duration[1];
    $minutes = $duration[2];
    $seconds = $duration[3];
    return $seconds + ($minutes * 60) + ($hours * 60 * 60);
}

/**
 * Summary of playMedia
 * @param mixed $path Path
 * @param mixed $host Host (default 'localhost')
 * @return int
 */
function playMedia($path, $host = 'localhost', $safe_play = FALSE)
{
    if (defined('SETTINGS_HOOK_PLAYMEDIA') && SETTINGS_HOOK_PLAYMEDIA != '') {
        eval(SETTINGS_HOOK_PLAYMEDIA);
    }

    if (!$terminal = getTerminalsByName($host, 1)[0]) {
        $terminal = getTerminalsByHost($host, 1)[0];
    }

    if (!$terminal['ID']) {
        $terminal = getTerminalsCanPlay(1)[0];
    }

    if (!$terminal['ID']) {
        $terminal = getMainTerminal();
    }

    if (!$terminal['ID']) {
        $terminal = getAllTerminals(1)[0];
    }

    if (!$terminal['ID']) {
        return 0;
    }

    $url = BASE_URL . ROOTHTML . 'ajax/app_player.html?';
    $url .= "&command=" . ($safe_play ? 'safe_play' : 'play');
    $url .= "&terminal_id=" . $terminal['ID'];
    $url .= "&param=" . urlencode($path);
    getURLBackground($url);
    return 1;
}

/**
 * Summary of stopMedia
 * @param mixed $host Host (default 'localhost')
 * @return int
 */
function stopMedia($host = 'localhost')
{
    if (!$terminal = getTerminalsByName($host, 1)[0]) {
        $terminal = getTerminalsByHost($host, 1)[0];
    }
    if (!$terminal['ID']) {
        $terminal = getTerminalsCanPlay(1)[0];
    }
    if (!$terminal['ID']) {
        $terminal = getMainTerminal();
    }
    if (!$terminal['ID']) {
        $terminal = getAllTerminals(1)[0];
    }
    if (!$terminal['ID']) {
        return 0;
    }
    $url = BASE_URL . ROOTHTML . 'ajax/app_player.html?';
    $url .= "&command=stop";
    $url .= "&terminal_id=" . $terminal['ID'];
    getURLBackground($url);
    return 1;
}

/**
 * This function change volume on the terminal
 * @param mixed $host Host (default 'localhost') name or ip of terminal
 * @param mixed $level level of volume (default 0) to positon from start time
 */
function setPlayerVolume($host = 'localhost', $level = 0)
{
    if (!$terminal = getTerminalsByName($host, 1)[0]) {
        $terminal = getTerminalsByHost($host, 1)[0];
    }
    if (!$terminal) {
        return;
    }
    include_once(DIR_MODULES . 'app_player/app_player.class.php');
    $player = new app_player();
    $player->play_terminal = $terminal['NAME']; // Имя терминала
    $player->command = 'set_volume'; // Команда
    $player->param = $level; // Параметр
    $player->ajax = TRUE;
    $player->intCall = TRUE;
    $player->usual($out);

    return $player->json['message'];
}

function setTerminalMML($host = 'localhost', $mml=0) {
    if (!$terminal = getTerminalsByName($host, 1)[0]) {
        $terminal = getTerminalsByHost($host, 1)[0];
    }
    if (!$terminal['ID']) {
        $terminal = getTerminalsCanPlay(1)[0];
    }
    if (!$terminal['ID']) {
        $terminal = getMainTerminal();
    }
    if (!$terminal['ID']) {
        $terminal = getAllTerminals(1)[0];
    }
    if (!$terminal['ID']) {
        return 0;
    }
	$terminal['MIN_MSG_LEVEL'] = $mml;
	SQLUpdate('terminals', $terminal);
	return true;
}

function postURLBackground($url, $query = array(), $cache = 0, $username = '', $password = '')
{
    //DebMes("URL: ".$url,'debug1');
    postURL($url, $query , $cache, $username, $password, true);
}

/**
 * Summary of postURL
 * @param mixed $url Url
 * @param mixed $query query
 * @param mixed $cache Cache (default 0)
 * @param mixed $username User name (default '')
 * @param mixed $password Password (default '')
 * @return mixed
 */
function postURL($url, $query = array(), $cache = 0, $username = '', $password = '', $background = false)
{
    startMeasure('postURL');
    // DebMes($url,'urls');
    $filename_part = preg_replace('/\W/is', '_', str_replace('http://', '', $url));
    if (strlen($filename_part) > 200) {
        $filename_part = substr($filename_part, 0, 200) . md5($filename_part);
    }
    $cache_file = ROOT . 'cms/cached/urls/' . $filename_part . '.html';

    if (!$cache || !is_file($cache_file) || ((time() - filemtime($cache_file)) > $cache)) {
        try {

            //DebMes('Geturl started for '.$url. ' Source: ' .debug_backtrace()[1]['function'], 'geturl');
            startMeasure('curl_prepare');
            $ch = curl_init();
            @curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // connection timeout
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);  // operation timeout 45 seconds
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            if ($background) {
                curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300);
            }

            if ($username != '' || $password != '') {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            }

            $url_parsed = parse_url($url);
            $host = $url_parsed['host'];

            $use_proxy = false;
            if (defined('USE_PROXY') && USE_PROXY != '') {
                $use_proxy = true;
            }

            if ($host == '127.0.0.1' || $host == 'localhost') {
                $use_proxy = false;
            }

            if ($use_proxy && defined('HOME_NETWORK') && HOME_NETWORK != '') {
                $p = preg_quote(HOME_NETWORK);
                $p = str_replace('\*', '\d+?', $p);
                $p = str_replace(',', ' ', $p);
                $p = str_replace('  ', ' ', $p);
                $p = str_replace(' ', '|', $p);
                if (preg_match('/' . $p . '/is', $host)) {
                    $use_proxy = false;
                }
            }

            if ($use_proxy) {
                curl_setopt($ch, CURLOPT_PROXY, USE_PROXY);
                if (defined('USE_PROXY_AUTH') && USE_PROXY_AUTH != '') {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, USE_PROXY_AUTH);
                }
            }

            $tmpfname = ROOT . 'cms/cached/cookie.txt';
            curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);

            endMeasure('curl_prepare');
            startMeasure('curl_exec');
            $result = curl_exec($ch);
            endMeasure('curl_exec');


            startMeasure('curl_post');
            if (!$background && curl_errno($ch)) {
                $errorInfo = curl_error($ch);
                $info = curl_getinfo($ch);
                $backtrace = debug_backtrace();
                $callSource = $backtrace[1]['function'];
                DebMes("GetURL to $url (source " . $callSource . ") finished with error: \n" . $errorInfo . "\n" . json_encode($info),'geturl_error');
            }
            curl_close($ch);
            endMeasure('curl_post');


        } catch (Exception $e) {
            registerError('geturl', $url . ' ' . get_class($e) . ', ' . $e->getMessage());
        }

        if ($cache > 0) {
            CreateDir(ROOT . 'cms/cached/urls');
            SaveFile($cache_file, $result);
        }
    } else {
        $result = LoadFile($cache_file);
    }


    endMeasure('postURL');

    return $result;
}
