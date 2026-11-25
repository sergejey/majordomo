<?php
/** Mark system errors as read without reloading the page. Admin only. */
header('Content-Type: application/json; charset=utf-8');
chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');

$session = new session('prj');
include_once(DIR_MODULES . 'control_access/control_access.class.php');
$access = new control_access();
if (!$access->checkAccess('system_errors')) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'msg' => 'Access denied'));
    exit;
}
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id > 0) {
    SQLExec("UPDATE system_errors SET ACTIVE=0 WHERE ID=" . $id);
    SQLExec("DELETE FROM system_errors_data WHERE ERROR_ID=" . $id);
} else {
    SQLExec("UPDATE system_errors SET ACTIVE=0");
}
echo json_encode(array('status' => 'ok', 'id' => $id));