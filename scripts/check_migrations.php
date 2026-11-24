<?php
/**
 * Verification of all post-update migrations.
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
$ids = array(
    'cached_values_expire_20210321',
    'pinghosts_google_20240508',
    'checkstate_int_cast_20251221',
    'command97_roothtml_20261122',
    'rooms_activity_status_20261124',
);
foreach ($ids as $id) {
    $checks['migration registered: ' . $id] = strpos($migrationsSrc, "'" . $id . "'") !== false;
    $mark = @SQLSelectOne("SELECT VALUE FROM settings WHERE NAME='MJ_MIGRATION_" . $id . "'");
    $checks['migration applied: ' . $id] = isset($mark['VALUE']) && $mark['VALUE'] !== '';
}

$checks['cached_values has no EXPIRE'] = count(@SQLSelect("SHOW COLUMNS FROM cached_values LIKE 'EXPIRE'")) === 0;

$host = @SQLSelectOne("SELECT HOSTNAME FROM pinghosts WHERE ID=1");
$checks['ping host updated'] = isset($host['HOSTNAME']) && $host['HOSTNAME'] === 'www.google.com';

$oldCasts = @SQLSelect("SELECT ID FROM methods WHERE TITLE IN ('checkState','object17_method59','object17_method73') AND CODE LIKE '%time()-$tm>%'");
$checks['cycle timestamps cast'] = count($oldCasts) === 0;

$command = @SQLSelectOne("SELECT CODE FROM commands WHERE ID=97");
$checks['State command uses ROOTHTML'] = isset($command['CODE'])
    && strpos($command['CODE'], 'src="<#ROOTHTML#>img/icons/') !== false;

$class = @SQLSelectOne("SELECT ID FROM classes WHERE TITLE='Rooms'");
if (!empty($class['ID'])) {
    $method = @SQLSelectOne("SELECT CODE FROM methods WHERE TITLE='updateActivityStatus' AND CLASS_ID=" . (int)$class['ID']);
    $checks['Rooms method patched'] = isset($method['CODE'])
        && strpos($method['CODE'], 'Rooms_updateActivityStatus.php') !== false
        && strpos($method['CODE'], 'function rooms_cmp') === false;
} else {
    $checks['Rooms method patched'] = true;
}

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
