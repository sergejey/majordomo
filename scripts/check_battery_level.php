<?php
/**
 * Regression check for SDevices_batteryLevelUpdated.
 * Run from the MajorDoMo root: php scripts/check_battery_level.php
 */
chdir(dirname(__FILE__) . '/..');
include 'config.php';
include 'lib/loader.php';
include 'load_settings.php';

class FakeBatteryDevice
{
    public $props = array();
    public $called = array();
    public $object_title = 'TestBattery';

    function getProperty($name) { return isset($this->props[$name]) ? $this->props[$name] : false; }
    function setProperty($name, $value) { $this->props[$name] = $value; }
    function callMethod($name) { $this->called[] = $name; }

    function run()
    {
        include DIR_MODULES . 'devices/SDevices_batteryLevelUpdated.php';
    }
}

function check_case($label, $props, $expected_warning, $expected_keepalive)
{
    $d = new FakeBatteryDevice();
    $d->props = $props;
    $d->run();
    $warning = (int)$d->getProperty('batteryWarning');
    $kept = in_array('keepAlive', $d->called, true);
    $ok = $warning === $expected_warning && $kept === $expected_keepalive;
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $label
        . ' (warning=' . $warning . ', keepAlive=' . ($kept ? 'yes' : 'no') . ')' . PHP_EOL;
    return $ok;
}

$ok = true;
$ok = check_case('unknown level is not critical', array('batteryOperated' => 1, 'batteryLevel' => ''), 0, true) && $ok;
$ok = check_case('10% is critical', array('batteryOperated' => 1, 'batteryLevel' => 10), 1, true) && $ok;
$ok = check_case('30% is low', array('batteryOperated' => 1, 'batteryLevel' => 30), 2, true) && $ok;
$ok = check_case('80% is normal', array('batteryOperated' => 1, 'batteryLevel' => 80), 0, true) && $ok;
$ok = check_case('mains device has no warning', array('batteryOperated' => 0, 'batteryLevel' => 10), 0, false) && $ok;

echo $ok ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED' . PHP_EOL;
exit($ok ? 0 : 1);
