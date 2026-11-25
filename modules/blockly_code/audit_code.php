<?php
/** Code audit through the installed gptchat module. Admin only. */
header('Content-Type: application/json; charset=utf-8');
chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');

$session = new session('prj');
include_once(DIR_MODULES . 'control_access/control_access.class.php');
$access = new control_access();
if (!$access->checkAccess(gr('md'))) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'msg' => 'Access denied'));
    exit;
}
$file = DIR_MODULES . 'gptchat/gptchat.class.php';
if (!is_file($file)) {
    $msg = defined('LANG_CODEEDITOR_AUDIT_NO_MODULE') ? constant('LANG_CODEEDITOR_AUDIT_NO_MODULE') : 'gptchat module is not installed.';
    echo json_encode(array('status' => 'skip', 'msg' => $msg));
    exit;
}
$code = (string)(isset($_POST['code']) ? $_POST['code'] : '');
$language = strtolower((string)(isset($_POST['language']) ? $_POST['language'] : 'php'));
if (strlen($code) > 200000) {
    echo json_encode(array('status' => 'error', 'msg' => 'Code is too large (200 KB limit).'));
    exit;
}
include_once($file);
$gptchat = new gptchat();
if (!method_exists($gptchat, 'auditCode')) {
    $msg = defined('LANG_CODEEDITOR_AUDIT_NO_METHOD') ? constant('LANG_CODEEDITOR_AUDIT_NO_METHOD') : 'Installed gptchat version does not support code audit.';
    echo json_encode(array('status' => 'skip', 'msg' => $msg));
    exit;
}
try {
    $answer = $gptchat->auditCode($code, $language);
} catch (Throwable $e) {
    echo json_encode(array('status' => 'error', 'msg' => $e->getMessage()));
    exit;
}
if ($answer === '') {
    $msg = defined('LANG_CODEEDITOR_AUDIT_NO_CHAT') ? constant('LANG_CODEEDITOR_AUDIT_NO_CHAT') : 'No audit chat is configured in gptchat.';
    echo json_encode(array('status' => 'skip', 'msg' => $msg));
    exit;
}
echo json_encode(array('status' => 'ok', 'msg' => $answer));