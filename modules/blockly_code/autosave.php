<?php
chdir (dirname (__FILE__) . '/../../');

include_once ('./config.php');
include_once ('./lib/loader.php');

$action = $_POST['action'];
$id = $_POST['id'];
$md = $_POST['md'];
	
if($action == 'save' && !empty($id) && !empty($md)) {
	$code = urldecode($_POST['code']);

	$dir = DOC_ROOT . '/cms/cached/codemirror';
	if(!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}

	$fileName = 'autosave_'.$md.'_'.$id.'.cdm';
	$filePath = $dir . '/' . $fileName;
	SaveFile($filePath, $code);
	
	echo date('d.m.Y H:i:s');
} else if($action == 'restore' && !empty($id) && !empty($md)) {
	echo 'restore';
} else {
	http_response_code(404);
	die();
}



