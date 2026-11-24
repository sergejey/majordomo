<?php
/**
 * Verification of the post-update migration mechanism.
 * Run from the MajorDoMo root: php scripts/check_migrations.php
 */
chdir(dirname(__FILE__) . '/..');
include 'config.php';
include 'lib/loader.php';
include 'load_settings.php';

$checks = array();

$settingsSrc = file_get_contents('load_settings.php');
$checks['load_settings runs migrations'] = strpos($settingsSrc, 'mjd_run_migrations()') !== false;

$migrationsSrc = file_get_contents('lib/mjd_migrations.php');
$checks['migration id registered'] = strpos($migrationsSrc, 'rooms_activity_status_20261124') !== false;
$checks['migration is idempotent'] = strpos($migrationsSrc, 'mjd_migration_is_applied') !== false;

$seed = file_get_contents('db_terminal.sql');
$needle = "require(DIR_MODULES.\\'devices/Rooms_updateActivityStatus.php\\');";
if (strpos($seed, $needle) === false) {
    $needle = "require(DIR_MODULES.'devices/Rooms_updateActivityStatus.php');";
}
$checks['fresh install uses shared file'] = strpos($seed, $needle) !== false;

// Safe to call again: applied ids are skipped.
if (function_exists('mjd_run_migrations')) {
    mjd_run_migrations();
    $checks['runner available'] = true;
} else {
    $checks['runner available'] = false;
}

$class = @SQLSelectOne("SELECT ID FROM classes WHERE TITLE='Rooms'");
if (!empty($class['ID'])) {
    $method = @SQLSelectOne("SELECT ID, CODE FROM methods WHERE TITLE='updateActivityStatus' AND CLASS_ID=" . (int)$class['ID']);
    $checks['existing method patched'] = isset($method['ID'])
        && strpos($method['CODE'], 'Rooms_updateActivityStatus.php') !== false
        && strpos($method['CODE'], 'function rooms_cmp') === false;
} else {
    $checks['existing method patched'] = true; // nothing to patch in this installation
}

$mark = @SQLSelectOne("SELECT VALUE FROM settings WHERE NAME='MJ_MIGRATION_rooms_activity_status_20261124'");
$checks['migration marked applied'] = isset($mark['VALUE']) && $mark['VALUE'] !== '';

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
