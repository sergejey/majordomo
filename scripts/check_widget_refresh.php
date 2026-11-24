<?php
$root = dirname(__DIR__);
$js = file_get_contents($root . '/modules/devices/devices_widgets.js.php');
$css = file_get_contents($root . '/css/devices.css');
$bad = array();
if (strpos($js, 'function mjApplyWidgetHTML') === false) $bad[] = 'targeted updater';
if (strpos($js, 'mjPendingUpdates') === false) $bad[] = 'update queue';
if (strpos($js, 'setTimeout(mjFlushWidgetUpdates, 120)') === false) $bad[] = 'debounce';
if (strpos($js, 'op=get_devices&ids=') === false) $bad[] = 'batch request';
if (strpos($js, "document.visibilityState === 'hidden'") === false) $bad[] = 'hidden tab guard';
if (strpos($js, "$('#device'+device_id).html(html)") !== false) $bad[] = 'old full replacement';
if (strpos($css, '@keyframes mj-widget-flash') === false) $bad[] = 'flash animation';
if (strpos($css, 'prefers-reduced-motion: reduce') === false) $bad[] = 'reduced motion';
$out = array(); $code = 0;
exec('php -l ' . escapeshellarg($root . '/modules/devices/devices_widgets.js.php') . ' 2>&1', $out, $code);
if ($code !== 0) $bad[] = 'js php syntax';
$out = array(); $code = 0;
exec('php -l ' . escapeshellarg($root . '/modules/devices/devices.class.php') . ' 2>&1', $out, $code);
if ($code !== 0) $bad[] = 'devices class syntax';
if ($bad) { echo 'FAIL - ' . implode(', ', $bad) . PHP_EOL; exit(1); }
echo 'ALL CHECKS PASSED' . PHP_EOL;