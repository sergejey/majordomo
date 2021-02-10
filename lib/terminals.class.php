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
