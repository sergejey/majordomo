<?php
/** Save one setting without reloading the page. Admin only. */
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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$value = isset($_POST['value']) ? trim((string)$_POST['value']) : '';
if (!$id || $value === '') {
    echo json_encode(array('status' => 'error', 'msg' => 'Invalid parameters'));
    exit;
}

$rec = SQLSelectOne("SELECT * FROM settings WHERE ID=" . $id);
if (!$rec['ID']) {
    echo json_encode(array('status' => 'error', 'msg' => 'Setting not found'));
    exit;
}

$name = $rec['NAME'];
if ($name === 'SITE_LANGUAGE' || $name === 'VOICE_LANGUAGE') {
    if ($value === 'ua') {
        $value = 'uk';
    }
    $available = array();
    foreach (glob(ROOT . 'languages/*.php') as $file) {
        if (preg_match('/([a-z_]+)\.php$/', basename($file), $m)) {
            $available[] = $m[1];
        }
    }
    if (!in_array($value, $available, true)) {
        echo json_encode(array('status' => 'error', 'msg' => 'Unknown language'));
        exit;
    }
} elseif ($name === 'SITE_TIMEZONE') {
    if (!in_array($value, DateTimeZone::listIdentifiers(), true)) {
        echo json_encode(array('status' => 'error', 'msg' => 'Unknown time zone'));
        exit;
    }
} elseif ($name === 'THEME') {
    if (!in_array($value, array('light', 'dark', 'auto'), true)) {
        echo json_encode(array('status' => 'error', 'msg' => 'Unknown theme'));
        exit;
    }
} elseif ($rec['TYPE'] === 'select') {
    $allowed = array();
    foreach (explode('|', (string)$rec['DATA']) as $option) {
        $parts = explode('=', $option, 2);
        if (count($parts) === 2) {
            $allowed[] = $parts[0];
        }
    }
    if ($allowed && !in_array($value, $allowed, true)) {
        echo json_encode(array('status' => 'error', 'msg' => 'Value is not allowed'));
        exit;
    }
} else {
    echo json_encode(array('status' => 'error', 'msg' => 'This setting cannot be changed inline'));
    exit;
}

$rec['VALUE'] = $value;
SQLUpdate('settings', $rec);
echo json_encode(array('status' => 'ok', 'name' => $name, 'value' => $value));