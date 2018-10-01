<?php

chdir('../');
include_once('./config.php');
include_once('./lib/loader.php');
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
include_once('./load_settings.php');

/*
DebMes($_SERVER['REQUEST_URI']);

$command = stripslashes($_GET['command']);
$section = stripslashes($_GET['section']);
$param = stripslashes($_GET['param']);
*/

$done = FALSE;

if(!empty($command) && file_exists('./rc/commands/'.$command.'.bat')) {
	$commandPath = DOC_ROOT.'/rc/commands/'.$_GET['command'].'.bat';
	if(!empty($param))
		$commandPath .= ' "'.$param.'"';
	
	if(safe_exec($commandPath)) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command) && file_exists('./rc/commands/'.$command.'.sh')) {
	$commandPath = DOC_ROOT.'/rc/commands/'.$_GET['command'].'.sh';
	if(!empty($param))
		$commandPath .= ' "'.$param.'"';
	
	if(exec($commandPath.' > /dev/null &')) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command) && file_exists('./rc/scripts/'.$command.'.aut')) {
	$commandPath = SERVER_ROOT.'/apps/autoitv3/AutoIt3.exe '.DOC_ROOT.'/rc/scripts/'.$_GET['command'].'.aut';
	if(!empty($param))
		$commandPath .= ' "'.$param.'"';
	
	if(safe_exec('start '.$commandPath, 1)) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command) && file_exists('./rc/scripts/'.$command.'.au3')) {
	$commandPath = SERVER_ROOT.'/apps/autoitv3/AutoIt3.exe '.DOC_ROOT.'/rc/scripts/'.$_GET['command'].'.au3';
	if(!empty($param))
		$commandPath .= ' "'.$param.'"';
	if(safe_exec('start '.$commandPath, 1)) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command)) {
	echo 'command not found';
}

$db->Disconnect(); // closing database connection

if($done) {
	die('OK');
}
