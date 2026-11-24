<?php
/**
 * Quick regression check for modules/app_player/libs/castv2/Chromecast.php.
 * Run from the MajorDoMo root: php scripts/check_chromecast_timeouts.php
 */

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
$file = $root . 'modules' . DIRECTORY_SEPARATOR . 'app_player' . DIRECTORY_SEPARATOR
    . 'libs' . DIRECTORY_SEPARATOR . 'castv2' . DIRECTORY_SEPARATOR . 'Chromecast.php';
$src = file_get_contents($file);

$checks = array(
    'socket timeout is 3s'        => strpos($src, 'stream_set_timeout($this->socket, 3, 0)') !== false,
    'no OR in media loop'         => strpos($src, 'time() > $zeit60') === false,
    'no 10us spin'                => strpos($src, 'usleep(10)') === false,
    'autoplay is boolean'         => strpos($src, '"autoplay":true') !== false,
    'launch/status/load timeouts' => strpos($src, '$wait_launch') !== false
        && strpos($src, '$wait_status') !== false
        && strpos($src, '$wait_load') !== false,
);

chdir(dirname($file));
require_once 'Chromecast.php';

class SilentCast extends GChromecast
{
    public function __construct() {}
    function getStatus() { return null; }
    function launch($appid = '') {}
    function connect() {}
    function sendMessage($ns, $msg) {}
    function getCastMessage() { return ''; }
}

$start = microtime(true);
$cast = new SilentCast();
$cast->getMediaSession();
$elapsed = round(microtime(true) - $start, 2);
$checks['silent device returns in <= 6s (got ' . $elapsed . 's)'] = $elapsed <= 6;

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
