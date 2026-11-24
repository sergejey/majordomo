<?php
$root = dirname(__DIR__);
$files = glob($root . '/templates/classes/views/*.html');
$n = 0; $c = 0; $bad = array();
foreach ($files as $f) {
  $h = file_get_contents($f);
  if (!preg_match('/<div[^>]*device-widget[^>]*>/s', $h, $m)) continue;
  $n++;
  $name = basename($f);
  $tag = $m[0];
  if (strpos($tag, 'aria-label') === false) $bad[] = 'aria ' . $name;
  if (strpos($tag, 'data-state=') === false) $bad[] = 'state ' . $name;
  if (stripos($tag, 'onclick=') !== false) {
    $c++;
    if (strpos($tag, 'tabindex="0"') === false || strpos($tag, 'role="button"') === false) $bad[] = 'keyboard ' . $name;
  }
}
$js = file_get_contents($root . '/modules/devices/devices_widgets.js.php');
if (strpos($js, 'keydown') === false) $bad[] = 'keydown handler';
$css = file_get_contents($root . '/css/devices.css');
if (strpos($css, '.device-widget.controller:focus,') === false) $bad[] = 'focus fallback';
if ($n < 35) $bad[] = 'roots ' . $n;
if ($c < 15) $bad[] = 'clickable ' . $c;
$out = array(); $code = 0;
exec('php -l ' . escapeshellarg($root . '/modules/devices/devices_widgets.js.php') . ' 2>&1', $out, $code);
if ($code !== 0) $bad[] = 'php syntax';
if ($bad) { echo 'FAIL - ' . implode(', ', $bad) . PHP_EOL; exit(1); }
echo 'ALL CHECKS PASSED (roots=' . $n . ', clickable=' . $c . ')' . PHP_EOL;