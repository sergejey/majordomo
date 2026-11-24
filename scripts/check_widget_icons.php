<?php
/** Regression check for local widget icons. Run: php scripts/check_widget_icons.php */
$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/devices.css');
$tokens = file_get_contents($root . '/css/theme-tokens.css');
$compat = file_get_contents($root . '/css/fontawesome-compat.css');
$header = file_get_contents($root . '/templates/common_header.html');
$checks = array();
$checks['no legacy raster refs'] = strpos($css, 'cms/scenes/styles') === false;
$checks['local FA font family'] = strpos($css, 'Font Awesome 7 Free') !== false;
$checks['icon mappings'] = substr_count($css, '--mj-icon:') >= 18;
$checks['glyph uses token'] = strpos($css, 'content: var(--mj-icon,') !== false;
$checks['balanced braces'] = substr_count($css, '{') === substr_count($css, '}');
foreach (array('icon-fg', 'icon-on', 'icon-off') as $name) {
    $token = '--mj-widget-' . $name;
    $checks['token ' . $token] = strpos($tokens, $token . ':') !== false;
}
$checks['compat imports locally'] = strpos($compat, '@import url("../3rdparty/fontawesome/css/all.min.css")') !== false && strpos($compat, 'http://') === false && strpos($compat, 'https://') === false;
$checks['compat included'] = strpos($header, 'css/fontawesome-compat.css') !== false;
$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) { $failed++; echo 'FAIL - ' . $name . PHP_EOL; }
}
echo $failed === 0 ? 'ALL CHECKS PASSED (' . count($checks) . ')' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);