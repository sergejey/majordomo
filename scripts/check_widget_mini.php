<?php
$root = dirname(__DIR__);
$files = glob($root . '/templates/classes/views/*_mini.html');
$checks = array();
$checks['mini templates found'] = count($files) >= 10;
$iconOnly = array('SCameras_mini.html','SLeak_mini.html','SMotions_mini.html','SOpenable_mini.html','SOpenClose_mini.html','SSmoke_mini.html');
foreach ($files as $file) {
 $name = basename($file);
 $html = file_get_contents($file);
 $checks['title '.$name] = strpos($html, 'title=') !== false;
 $checks['no inline color '.$name] = strpos($html, 'color:black') === false;
 if (in_array($name, $iconOnly, true)) { $checks['icon-only '.$name] = strpos($html, 'mini icon-only') !== false; }
}
$css = file_get_contents($root . '/css/devices.css');
$checks['icon-only rule'] = strpos($css, '.device-widget.mini.icon-only') !== false;
$checks['no fixed mini width'] = strpos($css, 'width:150px') === false && strpos($css, 'width:75px') === false;
$failed = 0;
foreach ($checks as $name => $ok) { if (!$ok) { $failed++; echo 'FAIL - '.$name.PHP_EOL; } }
echo $failed === 0 ? 'ALL CHECKS PASSED ('.count($checks).')'.PHP_EOL : 'FAILED: '.$failed.PHP_EOL;
exit($failed === 0 ? 0 : 1);