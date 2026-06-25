<?php

chdir('../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');

/*
DebMes($_SERVER['REQUEST_URI']);

$command = stripslashes($_GET['command']);
$section = stripslashes($_GET['section']);
$param = stripslashes($_GET['param']);
*/

$done = FALSE;

// Validate command name: alphanumeric and underscores only
if (!empty($command) && !preg_match('/^[a-zA-Z0-9_]+$/', $command)) {
	echo 'invalid command name';
} elseif(!empty($command) && file_exists('./rc/commands/'.$command.'.bat')) {
	$commandPath = DOC_ROOT.'/rc/commands/'.$command.'.bat';
	if(!empty($param))
		$commandPath .= ' ' . escapeshellarg($param);

	if(safe_exec($commandPath)) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command) && file_exists('./rc/commands/'.$command.'.sh')) {
	$commandPath = DOC_ROOT.'/rc/commands/'.$command.'.sh';
	if(!empty($param))
		$commandPath .= ' ' . escapeshellarg($param);

	if(exec($commandPath.' > /dev/null &')) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command) && file_exists('./rc/scripts/'.$command.'.aut')) {
	$commandPath = SERVER_ROOT.'/apps/autoitv3/AutoIt3.exe '.DOC_ROOT.'/rc/scripts/'.$command.'.aut';
	if(!empty($param))
		$commandPath .= ' ' . escapeshellarg($param);

	if(safe_exec('start '.$commandPath, 1)) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command) && file_exists('./rc/scripts/'.$command.'.au3')) {
	$commandPath = SERVER_ROOT.'/apps/autoitv3/AutoIt3.exe '.DOC_ROOT.'/rc/scripts/'.$command.'.au3';
	if(!empty($param))
		$commandPath .= ' ' . escapeshellarg($param);
	if(safe_exec('start '.$commandPath, 1)) {
		$done = TRUE;
	} else {
		echo 'runtime error';
	}
} elseif(!empty($command)) {
	echo 'command not found';
}

if($done) {
	die('OK');
}
