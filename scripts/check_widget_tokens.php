<?php
/**
 * Regression check for widget tokens and adaptive cards.
 * Run from the repository root: php scripts/check_widget_tokens.php
 */
$root = dirname(__DIR__);
$checks = array();
$tokens = file_get_contents($root . '/css/theme-tokens.css');
$css = file_get_contents($root . '/css/devices.css');
$names = array(
    'surface', 'fg', 'muted', 'border', 'radius', 'padding', 'mini-padding',
    'gap', 'max-width', 'mini-width', 'compact-width', 'icon-size', 'touch',
    'header-size', 'details-size', 'offline-bg', 'offline-fg', 'offline-border',
    'on-bg', 'alert-bg', 'pressed-bg', 'status-text', 'status-muted',
    'status-ok', 'status-info'
);
$colorNames = array(
    'surface', 'fg', 'muted', 'border', 'offline-bg', 'offline-fg',
    'offline-border', 'on-bg', 'alert-bg', 'pressed-bg', 'status-text',
    'status-muted', 'status-ok', 'status-info'
);
$light = substr($tokens, 0, strpos($tokens, ':root[data-theme="dark"]'));
$dark = substr($tokens, strpos($tokens, ':root[data-theme="dark"]'));
foreach ($names as $name) {
    $token = '--mj-widget-' . $name;
    $checks['light token ' . $token] = strpos($light, $token . ':') !== false;
}
foreach ($colorNames as $name) {
    $token = '--mj-widget-' . $name;
    $checks['dark override ' . $token] = strpos($dark, $token . ':') !== false;
}
$forbidden = array(
    'min-width:250px', 'max-width:350px', 'width:150px', 'width:75px',
    'background-color:#aaaaaa', 'background-color:yellow',
    'background-color:red', 'background-color:greenyellow',
    'color:#dddddd', 'color:#666666', 'color:green;', 'color:blue;',
    'border:1px solid red'
);
foreach ($forbidden as $needle) {
    $checks['removed literal ' . $needle] = strpos($css, $needle) === false;
}
$checks['uses widget tokens'] = substr_count($css, 'var(--mj-widget-') >= 20;
$checks['boxes are border-box'] = strpos($css, 'box-sizing: border-box') !== false;
$checks['clearfix present'] = strpos($css, '.device-widget::after') !== false;
$checks['focus ring present'] = strpos($css, ':focus-visible') !== false;
$checks['dark offline override'] = strpos($css, ':root[data-theme="dark"] .device-widget.offline') !== false;
$checks['balanced braces'] = substr_count($css, '{') === substr_count($css, '}');
foreach (array('templates/common_header.html', 'pChart/index.php', '3rdparty/freeboard/index.php') as $file) {
    $content = file_get_contents($root . '/' . $file);
    $checks['cache version ' . $file] = strpos($content, 'devices.css?v=26-11-24') !== false;
}
foreach (array('css/theme-tokens.css', 'css/devices.css') as $file) {
    $content = file_get_contents($root . '/' . $file);
    $checks['no markers ' . $file] = !preg_match('/^<{7} |^>{7} |^={7}$/m', $content);
}
$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
        echo 'FAIL - ' . $name . PHP_EOL;
    }
}
echo $failed === 0
    ? 'ALL CHECKS PASSED (' . count($checks) . ')' . PHP_EOL
    : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);