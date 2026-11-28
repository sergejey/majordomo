<?php
/** Lightweight system health snapshot for the admin panel. Admin only. */
header('Content-Type: application/json; charset=utf-8');
chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');
$session = new session('prj');
include_once(DIR_MODULES . 'control_access/control_access.class.php');
$access = new control_access();
if (!$access->checkAccess('settings')) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'msg' => 'Access denied'));
    exit;
}

function mhLang($key, $fallback)
{
    $constant = 'LANG_' . $key;
    return defined($constant) ? constant($constant) : $fallback;
}

function mhModuleInstalled($name)
{
    $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    if ($name === '' || !SQLTableExists('project_modules')) {
        return false;
    }
    $rec = SQLSelectOne("SELECT ID FROM project_modules WHERE NAME='" . DBSafe($name) . "'");
    return !empty($rec['ID']);
}

function mhCycleState($name, $limit = 600)
{
    $ts = (int)gg('cycle_' . $name . 'Run');
    if (!$ts) {
        return 'unknown';
    }
    return (time() - $ts <= $limit) ? 'ok' : 'stale';
}

function mhStateLabel($state, $installed)
{
    if (!$installed) {
        return mhLang('HEALTH_NOT_INSTALLED', 'Not installed');
    }
    if ($state === 'ok') {
        return mhLang('HEALTH_ONLINE', 'Online');
    }
    if ($state === 'stale') {
        return mhLang('HEALTH_NO_DATA', 'No data');
    }
    return mhLang('HEALTH_NO_DATA', 'No data');
}

$mqtt_installed = mhModuleInstalled('mqtt');
$connect_installed = mhModuleInstalled('connect');
$mqtt = $mqtt_installed ? mhCycleState('mqtt') : 'not_installed';
$connect = $connect_installed ? mhCycleState('connect') : 'not_installed';

$errors = 0;
if (SQLTableExists('system_errors')) {
    $r = SQLSelectOne('SELECT COUNT(*) AS TOTAL FROM system_errors WHERE ACTIVE=1');
    $errors = isset($r['TOTAL']) ? (int)$r['TOTAL'] : 0;
}

echo json_encode(array(
    'status' => 'ok',
    'mqtt' => $mqtt,
    'mqtt_available' => $mqtt_installed,
    'mqtt_label' => mhStateLabel($mqtt, $mqtt_installed),
    'connect' => $connect,
    'connect_available' => $connect_installed,
    'connect_label' => mhStateLabel($connect, $connect_installed),
    'errors' => $errors,
    'errors_label' => $errors > 0 ? (string)$errors : mhLang('HEALTH_NO_ERRORS', 'No errors'),
    'time' => date('H:i:s')
));
