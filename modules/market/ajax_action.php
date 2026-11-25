<?php
/** Lightweight market actions without full page reload. Admin only. */
header('Content-Type: application/json; charset=utf-8');
chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');
$session = new session('prj');
include_once(DIR_MODULES . 'control_access/control_access.class.php');
$access = new control_access();
if (!$access->checkAccess('market')) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'msg' => 'Access denied'));
    exit;
}
$op = isset($_POST['op']) ? (string)$_POST['op'] : '';
$name = preg_replace('/[^A-Za-z0-9_-]/', '', (string)(isset($_POST['name']) ? $_POST['name'] : ''));
$value = preg_replace('/[^A-Za-z0-9_.-]/', '', (string)(isset($_POST['value']) ? $_POST['value'] : ''));
if ($op !== 'dontupdate' || !$name || !$value) {
    echo json_encode(array('status' => 'error', 'msg' => 'Invalid parameters'));
    exit;
}
SQLExec("UPDATE plugins SET CURRENT_VERSION = '" . DBSafe($value) . "' WHERE MODULE_NAME = '" . DBSafe($name) . "' LIMIT 1");
include_once(DIR_MODULES . 'market/stats.php');
mksStatsAdd('skip', $name);
echo json_encode(array('status' => 'ok', 'msg' => 'Update skipped', 'name' => $name, 'version' => $value));