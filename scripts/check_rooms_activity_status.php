<?php
/**
 * Regression check for Rooms.updateActivityStatus.
 * Run from the MajorDoMo root: php scripts/check_rooms_activity_status.php
 */

chdir(dirname(__FILE__) . '/..');
include 'config.php';
include 'lib/loader.php';
include 'load_settings.php';

$checks = array();

$roomClass = SQLSelectOne("SELECT ID FROM classes WHERE TITLE='Rooms'");
if (empty($roomClass['ID'])) {
    echo "SKIP - no Rooms class in this installation\n";
    exit(0);
}

$method = SQLSelectOne("SELECT ID, CODE FROM methods WHERE TITLE='updateActivityStatus' AND CLASS_ID=" . (int)$roomClass['ID']);
$checks['method uses shared file'] = isset($method['ID'])
    && strpos($method['CODE'], 'Rooms_updateActivityStatus.php') !== false;
$checks['no global rooms_cmp in method'] = isset($method['ID'])
    && strpos($method['CODE'], 'function rooms_cmp') === false;

$room = SQLSelectOne("SELECT TITLE FROM objects WHERE CLASS_ID=" . (int)$roomClass['ID'] . " LIMIT 1");
if (!empty($room['TITLE'])) {
    // Two calls in one process used to trigger "Cannot redeclare rooms_cmp()".
    callMethod($room['TITLE'] . '.updateActivityStatus');
    callMethod($room['TITLE'] . '.updateActivityStatus');
    $checks['double call completes'] = true;
    $checks['somebodyHomeText set'] = (string)getGlobal('somebodyHomeText') !== '';
} else {
    echo "SKIP - no Rooms objects to call the method\n";
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
