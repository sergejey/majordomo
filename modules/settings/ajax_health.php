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
function mhCycleState($name, $limit = 600)
{
    $ts = (int)gg('cycle_' . $name . 'Run');
    if (!$ts) return 'unknown';
    return (time() - $ts <= $limit) ? 'ok' : 'stale';
}
$errors = 0;
if (function_exists('SQLSelectOne')) {
    $r = SQLSelectOne("SELECT COUNT(*) AS TOTAL FROM system_errors WHERE ACTIVE=1");
    $errors = isset($r['TOTAL']) ? (int)$r['TOTAL'] : 0;
}
echo json_encode(array(
    'status' => 'ok',
    'mqtt' => mhCycleState('mqtt'),
    'connect' => mhCycleState('connect'),
    'errors' => $errors,
    'time' => date('H:i:s')
));