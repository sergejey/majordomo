<?php
/**
 * Quick code execution for the MajorDoMo code editor.
 * Admin-only. Returns JSON.
 */
header('Content-Type: application/json; charset=utf-8');

chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');

$session = new session('prj');
include_once(DIR_MODULES . 'control_access/control_access.class.php');
$access = new control_access();
$md = (string)gr('md');
if (!$access->checkAccess($md)) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'output' => 'Access denied'));
    exit;
}

$type = strtolower((string)(isset($_POST['type']) ? $_POST['type'] : 'php'));
if ($type !== 'php') {
    $msg = defined('LANG_CODEEDITOR_ONLY_PHP') ? constant('LANG_CODEEDITOR_ONLY_PHP') : 'Only PHP code can be executed on the server.';
    echo json_encode(array('status' => 'error', 'output' => $msg));
    exit;
}

$code = (string)(isset($_POST['code']) ? $_POST['code'] : '');
if (strlen($code) > 200000) {
    echo json_encode(array('status' => 'error', 'output' => 'Code is too large (200 KB limit).'));
    exit;
}

@set_time_limit(10);
$hasPhpTag = (bool)preg_match('/<\?/', $code);
$code = preg_replace('/^\s*<\?php/i', '', $code);
$code = preg_replace('/^\s*<\?/', '', $code);
$code = preg_replace('/\?>\s*$/', '', $code);
$evalCode = $hasPhpTag ? '?>' . $code : $code;

$output = '';
$error = '';
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
ob_start();
try {
    eval($evalCode);
} catch (Throwable $e) {
    $error = get_class($e) . ': ' . $e->getMessage() . ' (line ' . $e->getLine() . ')';
}
$output = ob_get_clean();
restore_error_handler();

if (strlen($output) > 200000) {
    $output = substr($output, 0, 200000) . "\n... [output truncated]";
}

echo json_encode(array(
    'status' => $error === '' ? 'ok' : 'error',
    'output' => $output,
    'error' => $error,
));