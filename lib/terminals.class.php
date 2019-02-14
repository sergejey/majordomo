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

