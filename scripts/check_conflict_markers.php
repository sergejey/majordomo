<?php
/**
 * Regression check: no unresolved merge markers in first-party sources.
 * Run from the repository root: php scripts/check_conflict_markers.php
 */
$root = dirname(__DIR__);
$skip = array('3rdparty', '.git', 'cms', 'templates_alt');
$files = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $item) {
    if (!$item->isFile()) {
        continue;
    }
    $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
    $parts = explode('/', $relative);
    if (in_array($parts[0], $skip, true)) {
        continue;
    }
    if (!preg_match('/\.(css|html|php|tpl|js|sql|sh)$/', $relative)) {
        continue;
    }
    $files[] = $relative;
}
sort($files);

$failed = 0;
$markers = array();
foreach ($files as $relative) {
    $lines = file($root . '/' . $relative);
    foreach ($lines as $number => $line) {
        $trimmed = rtrim($line, "\r\n");
        if (preg_match('/^<{7} |^>{7} |^={7}$/', $trimmed)) {
            $markers[] = $relative . ':' . ($number + 1);
        }
    }
}
if ($markers) {
    $failed++;
    echo 'FAIL - unresolved markers: ' . implode(', ', $markers) . PHP_EOL;
} else {
    echo 'PASS - no unresolved markers in ' . count($files) . ' files' . PHP_EOL;
}

foreach (array('css/admin.css', 'css/theme-tokens.css') as $css) {
    $text = file_get_contents($root . '/' . $css);
    $open = substr_count($text, '{');
    $close = substr_count($text, '}');
    $ok = $open === $close;
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - balanced braces: ' . $css . PHP_EOL;
}

echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
