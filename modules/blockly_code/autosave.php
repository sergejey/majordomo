<?php
chdir (dirname (__FILE__) . '/../../');

include_once ('./config.php');
include_once ('./lib/loader.php');

$dir = DOC_ROOT . '/cms/cached/codemirror';

$action = $_POST['action'];
$id = $_POST['id'];
$md = $_POST['md'];
	
$code = $_POST['code'];
	
//$code = str_replace("!amp;", "&", $code);
//$code = str_replace("!lt;", "<", $code);
//$code = str_replace("!gt;", ">", $code);
//$code = str_replace("!quot;", '"', $code);
//$code = str_replace("!#039;", "'", $code);
	
if($action == 'save' && !empty($id) && !empty($md)) {
	if(!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}

	$fileName = 'autosave_'.$md.'_'.$id.'_'.time().'.cdm';
	$filePath = $dir . '/' . $fileName;
	SaveFile($filePath, $code);
	
	echo json_encode(array('status' => 'ok', 'msg' => date('d.m.Y H:i:s'),));
	
	//Удалим старое
	foreach(@scandir($dir) as $value) {
		if($value == '.' || $value == '..') continue;
		$filename = substr($value, 0, -4);
		$filename = (int) explode('_', $filename)[3];
		if(($filename+86400) < time()) {
			@unlink($dir.'/'.$value);
		}
	}
} else if($action == 'restore' && !empty($id) && !empty($md)) {
	//Выгружаем файлы
	$files = @scandir($dir);
	//Выкидываем, все что не относится к запросу
	foreach($files as $key => $value) {
		if($value == '.' || $value == '..') unset($files[$key]);
		$filename = substr($value, 0, -4);
		$filename = explode('_', $filename);
		if($filename[1] != $md || $filename[2] != $id) unset($files[$key]);
	}
	rsort($files);
	
	$restoreCode = [];
	
	if(!is_dir($dir) || count($files) == 0) {
		echo json_encode(array('status' => 'error', 'msg' => 'Нет доступных для восстановления файлов!',));
		die();
	}
	
	foreach($files as $key => $value) {
		if($value == '.' || $value == '..') continue;
		$filename = substr($value, 0, -4);
		$filename = explode('_', $filename);
		$addtime = (int) $filename[3];
		
		$restoreCode[$key]['name'] = $filename[1].'_'.$filename[2].'_'.$filename[3];
		$restoreCode[$key]['create'] = date('d.m.Y H:i:s', $addtime);
		$restoreCode[$key]['code'] = LoadFile($dir.'/'.$value);
	}
	
	echo json_encode(array('status' => 'ok', 'msg' => $restoreCode,));
} else if($action == 'checkcode' && !empty($code)) {
	$code = str_replace("!amp", "&", $code);
	$code = str_replace("!lt", "<", $code);
	$code = str_replace("!gt", ">", $code);
	$code = str_replace("!quot", '"', $code);
	$code = str_replace("!039", "'", $code);
	$code = str_replace("!lpar", "(", $code);
	$code = str_replace("!rpar", ")", $code);
	$code = str_replace("!bsol", "\\", $code);
	$code = str_replace("!num", "#", $code);
	$code = str_replace("!endline", ";", $code);
	$code = str_replace("!newline", "\n", $code);
	$code = str_replace("!plus", "+", $code);
	$code = str_replace("!minus", "-", $code);
	
	$errors = php_syntax_error($code);
	
	echo json_encode(array('status' => 'ok', 'msg' => $errors,));
} else {
	echo 'error';
	http_response_code(404);
	die();
}





