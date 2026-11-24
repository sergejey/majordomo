<?php
/**
 * Regression check for blocked sensor keepAlive ordering.
 * Run from the MajorDoMo root: php scripts/check_blocked_sensor_keepalive.php
 */
$path = dirname(__FILE__) . '/../modules/devices/SSensors_valueUpdated.php';
$src = file_get_contents($path);

$out = array();
$code = 0;
exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
$checks = array('syntax' => $code === 0);

$keepAlivePos = strpos($src, "callMethodSafe('keepAlive')");
$blockedPos = strpos($src, "if (\$is_blocked)");
$checks['keepAlive before blocked check'] = $keepAlivePos !== false
    && $blockedPos !== false
    && $keepAlivePos < $blockedPos;
$checks['single keepAlive call'] = substr_count($src, "callMethodSafe('keepAlive')") === 1;

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
