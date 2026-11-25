<?php
/** Save saverestore settings without reloading the page. Admin only. */
header('Content-Type: application/json; charset=utf-8');
chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');
$session = new session('prj');
include_once(DIR_MODULES . 'control_access/control_access.class.php');
$access = new control_access();
if (!$access->checkAccess('saverestore')) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'msg' => 'Access denied'));
    exit;
}
include_once(DIR_MODULES . 'saverestore/saverestore.class.php');
$module = new saverestore();
$module->getConfig();
$url = trim((string)(isset($_POST['set_update_url']) ? $_POST['set_update_url'] : ''));
if ($url && !preg_match('/^https?:\/\//i', $url)) {
    echo json_encode(array('status' => 'error', 'msg' => 'Invalid update URL'));
    exit;
}
if ($url !== '') {
    $module->config['MASTER_UPDATE_URL'] = $url;
}
$module->config['UPDATE_AUTO'] = !empty($_POST['update_auto']) ? 1 : 0;
$module->config['UPDATE_AUTO_DELAY'] = isset($_POST['update_auto_delay']) ? (int)$_POST['update_auto_delay'] : 14;
$module->config['UPDATE_AUTO_TIME'] = isset($_POST['update_auto_time']) ? (int)$_POST['update_auto_time'] : 2;
$module->config['UPDATE_AUTO_PLUGINS'] = !empty($_POST['update_auto_plugins']) ? 1 : 0;
$module->saveConfig();
if ($module->config['UPDATE_AUTO']) {
    if (function_exists('subscribeToEvent')) { subscribeToEvent('saverestore', 'HOURLY'); }
} else {
    if (function_exists('unsubscribeFromEvent')) { unsubscribeFromEvent('saverestore', 'HOURLY'); }
}
echo json_encode(array('status' => 'ok', 'msg' => 'Settings saved', 'update_url' => $module->getUpdateURL()));