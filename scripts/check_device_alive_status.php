<?php
/**
 * Regression check for alive / batteryWarning consumers.
 * Run from the MajorDoMo root: php scripts/check_device_alive_status.php
 */
$root = dirname(__FILE__) . '/..';
$files = array(
    'modules/devices/SDevices_keepAlive.php',
    'modules/devices/devices_structure.inc.php',
    'modules/devices/system_checkstate.php',
    'modules/devices/devices_search.inc.php',
    'modules/xray/xray.class.php',
    'modules/devices/devices.class.php',
);

$checks = array();
foreach ($files as $file) {
    $path = $root . '/' . $file;
    $out = array();
    $code = 0;
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
    $checks['syntax: ' . $file] = $code === 0;
}

$keepAlive = file_get_contents($root . '/modules/devices/SDevices_keepAlive.php');
$checks['explicit zero disables timer'] = strpos($keepAlive, 'if ($alive_timeout > 0)') !== false
    && strpos($keepAlive, 'if ($alive_timeout==0)') === false;

$structure = file_get_contents($root . '/modules/devices/devices_structure.inc.php');
$checks['alive has default 1'] = strpos($structure, "'alive' => array('DESCRIPTION' => 'Alive', '_CONFIG_DEFAULT' => 1)") !== false;

$state = file_get_contents($root . '/modules/devices/system_checkstate.php');
$checks['system status uses numeric alive'] = strpos($state, "(int)gg(\$devices[\$idv]['LINKED_OBJECT'] . '.alive') === 0") !== false;
$checks['system status counts both warnings'] = strpos($state, '(int)$batteryWarning >= 1') !== false;

$search = file_get_contents($root . '/modules/devices/devices_search.inc.php');
$checks['battery_low filter uses >= 1'] = strpos($search, "getObjectsByProperty('batteryWarning', '>=', 1)") !== false;

$xray = file_get_contents($root . '/modules/xray/xray.class.php');
$checks['xray uses numeric alive'] = strpos($xray, '(int)$v === 0') !== false;

$devices = file_get_contents($root . '/modules/devices/devices.class.php');
$checks['dashboard guards device types'] = strpos($devices, 'isset($this->device_types[') !== false;
$checks['dashboard uses numeric alive'] = strpos($devices, "(int)gg(\$devices[\$idv]['LINKED_OBJECT'] . '.alive') === 0") !== false;

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
