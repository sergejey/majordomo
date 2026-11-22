<?php
/**
 * Compatibility audit for custom scripts.
 *
 * Read-only: scans project PHP/JS/template files and reports calls that are
 * removed or deprecated in the current PHP / jQuery / Bootstrap environment.
 * It never executes the scanned code and never modifies files.
 */

include_once('./config.php');
include_once('./lib/loader.php');

$session = new session('prj');
include_once('./load_settings.php');

if (empty($session->data['AUTHORIZED'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$rules = array(
    array(
        'id' => 'create_function',
        'severity' => 'error',
        'ext' => array('php'),
        'pattern' => '/\bcreate_function\s*\(/i',
        'recommendation' => 'PHP 8: create_function() removed. Use an anonymous function / closure.',
        'command' => "grep -rn --include='*.php' 'create_function(' modules templates js",
    ),
    array(
        'id' => 'each',
        'severity' => 'error',
        'ext' => array('php'),
        'pattern' => '/\beach\s*\(/i',
        'recommendation' => 'PHP 8: each() removed. Use foreach() with key()/next() or array keys.',
        'command' => "grep -rn --include='*.php' 'each(' modules templates js",
    ),
    array(
        'id' => 'mysql_extension',
        'severity' => 'error',
        'ext' => array('php'),
        'pattern' => '/\bmysql_(connect|query|fetch_|escape_string|real_escape_string)/i',
        'recommendation' => 'PHP 7+: ext/mysql removed. Use the core $db object (mysqli).',
        'command' => "grep -rn --include='*.php' 'mysql_' modules templates js",
    ),
    array(
        'id' => 'ereg_split',
        'severity' => 'error',
        'ext' => array('php'),
        'pattern' => '/\b(ereg|ereg_replace|eregi|split|spliti)\s*\(/i',
        'recommendation' => 'PHP 7+: POSIX regex functions removed. Use preg_match()/preg_split().',
        'command' => "grep -rnE --include='*.php' '(ereg|split)[a-z_]*\\(' modules templates js",
    ),
    array(
        'id' => 'magic_quotes',
        'severity' => 'error',
        'ext' => array('php'),
        'pattern' => '/\bget_magic_quotes_(gpc|runtime)\s*\(/i',
        'recommendation' => 'PHP 8: magic quotes functions removed. Remove the conditional stripslashes() calls.',
        'command' => "grep -rn --include='*.php' 'get_magic_quotes' modules templates js",
    ),
    array(
        'id' => 'curly_offset',
        'severity' => 'warning',
        'ext' => array('php'),
        'pattern' => '/\$[A-Za-z_][A-Za-z0-9_]*\{[^\}]{1,30}\}/',
        'recommendation' => 'PHP 8: curly brace string/array offsets are deprecated. Use square brackets.',
        'command' => "grep -rnE --include='*.php' '\\$[A-Za-z_]+\\{' modules templates js",
    ),
    array(
        'id' => 'jquery_live',
        'severity' => 'warning',
        'ext' => array('js', 'html'),
        'pattern' => '/\.live\s*\(/',
        'recommendation' => 'jQuery 1.9+: .live() removed. Use delegated .on().',
        'command' => "grep -rn --include='*.js' --include='*.html' '.live(' templates js modules",
    ),
    array(
        'id' => 'jquery_browser',
        'severity' => 'warning',
        'ext' => array('js', 'html'),
        'pattern' => '/\$\.browser\b/',
        'recommendation' => 'jQuery 1.9+: $.browser removed. Use feature detection.',
        'command' => "grep -rn --include='*.js' --include='*.html' '$.browser' templates js modules",
    ),
    array(
        'id' => 'jquery_size',
        'severity' => 'warning',
        'ext' => array('js', 'html'),
        'pattern' => '/\.size\s*\(\s*\)/',
        'recommendation' => 'jQuery 3+: .size() removed. Use .length.',
        'command' => "grep -rn --include='*.js' --include='*.html' '.size()' templates js modules",
    ),
    array(
        'id' => 'old_jquery',
        'severity' => 'info',
        'ext' => array('html', 'php'),
        'pattern' => '/jquery-3\.3\.1/',
        'recommendation' => 'Update the bundled jQuery reference to jquery-3.7.1.min.js.',
        'command' => "grep -rn 'jquery-3.3.1' . --exclude-dir=3rdparty",
    ),
    array(
        'id' => 'old_bootstrap',
        'severity' => 'info',
        'ext' => array('html', 'php'),
        'pattern' => '/bootstrap(\/3\.3\.7|\.min\.(css|js))/',
        'recommendation' => 'Update Bootstrap 3.3.7 references to bootstrap-3.4.1.',
        'command' => "grep -rn 'bootstrap/3.3.7\\|bootstrap.min.' . --exclude-dir=3rdparty",
    ),
    array(
        'id' => 'old_moment',
        'severity' => 'info',
        'ext' => array('html', 'php'),
        'pattern' => '/moment\.min\.js/',
        'recommendation' => 'Update the bundled Moment.js reference to moment-2.31.0.min.js.',
        'command' => "grep -rn 'moment.min.js' . --exclude-dir=3rdparty",
    ),
);

$scanDirs = array(ROOT . 'modules', ROOT . 'templates', ROOT . 'js');
$maxPerRule = 30;
$issues = array();
foreach ($rules as $rule) {
    $issues[$rule['id']] = array();
}

foreach ($scanDirs as $scanDir) {
    if (!is_dir($scanDir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getSize() > 1048576) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, array('php', 'js', 'html'))) {
            continue;
        }
        if (strpos($file->getPathname(), DIRECTORY_SEPARATOR . '3rdparty' . DIRECTORY_SEPARATOR) !== false) {
            continue;
        }
        $lines = @file($file->getPathname(), FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            continue;
        }
        foreach ($rules as $rule) {
            if (!in_array($ext, $rule['ext']) || count($issues[$rule['id']]) >= $maxPerRule) {
                continue;
            }
            foreach ($lines as $number => $line) {
                if (preg_match($rule['pattern'], $line)) {
                    $issues[$rule['id']][] = array(
                        'file' => ltrim(str_replace(ROOT, '', $file->getPathname()), '/\\'),
                        'line' => $number + 1,
                        'snippet' => trim(substr($line, 0, 160)),
                    );
                    if (count($issues[$rule['id']]) >= $maxPerRule) {
                        break;
                    }
                }
            }
        }
    }
}

function audit_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$versions = '';
// PHP version
$versions .= '<li>' . audit_escape(LANG_AUDIT_PHP_VERSION) . ': <code>' . audit_escape(PHP_VERSION) . '</code></li>';
// jQuery
$jQueryFiles = glob(ROOT . '3rdparty/jquery/jquery-*.min.js');
$versions .= '<li>jQuery: <code>' . audit_escape($jQueryFiles ? basename($jQueryFiles[0]) : '-') . '</code></li>';
// Bootstrap
$bootstrapFiles = glob(ROOT . '3rdparty/bootstrap/css/bootstrap*.min.css');
$versions .= '<li>Bootstrap: <code>' . audit_escape($bootstrapFiles ? basename($bootstrapFiles[0]) : '-') . '</code></li>';
// Moment
$momentFiles = glob(ROOT . '3rdparty/moment/*.min.js');
$versions .= '<li>Moment.js: <code>' . audit_escape($momentFiles ? basename($momentFiles[0]) : '-') . '</code></li>';

$total = 0;
foreach ($issues as $list) {
    $total += count($list);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo audit_escape(LANG_AUDIT_TITLE); ?></title>
    <link rel="stylesheet" href="<?php echo ROOTHTML; ?>3rdparty/bootstrap/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo ROOTHTML; ?>css/admin.css" type="text/css">
    <style>
        body { padding: 20px; }
        .audit-summary { margin-bottom: 20px; }
        .audit-rule { margin-bottom: 18px; }
        .audit-rule h4 { margin-bottom: 4px; }
        .audit-severity-error { color: #a94442; }
        .audit-severity-warning { color: #8a6d3b; }
        .audit-severity-info { color: #31708f; }
        .audit-locations { max-height: 220px; overflow: auto; }
        .audit-locations code { white-space: pre-wrap; word-break: break-all; }
        pre.audit-command { background: #f5f5f5; padding: 8px; white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="container-fluid">
    <h1><?php echo audit_escape(LANG_AUDIT_TITLE); ?></h1>
    <p class="lead"><?php echo audit_escape(LANG_AUDIT_DESCRIPTION); ?></p>

    <div class="panel panel-default audit-summary">
        <div class="panel-heading"><?php echo audit_escape(LANG_AUDIT_ENVIRONMENT); ?></div>
        <ul class="list-group"><?php echo $versions; ?></ul>
    </div>

    <p><a class="btn btn-primary" href="<?php echo audit_escape(ROOTHTML); ?>audit.php"><?php echo audit_escape(LANG_AUDIT_SCAN_AGAIN); ?></a></p>
    <p class="text-muted"><?php echo audit_escape(LANG_AUDIT_MASS_FIX_NOTE); ?></p>

    <?php if (!$total): ?>
        <div class="alert alert-success"><?php echo audit_escape(LANG_AUDIT_NO_ISSUES); ?></div>
    <?php endif; ?>

    <?php foreach ($rules as $rule): ?>
        <?php $list = isset($issues[$rule['id']]) ? $issues[$rule['id']] : array(); ?>
        <?php if (!$list) continue; ?>
        <div class="panel panel-default audit-rule">
            <div class="panel-heading">
                <strong class="audit-severity-<?php echo audit_escape($rule['severity']); ?>">
                    <?php echo audit_escape($rule['id']); ?> (<?php echo count($list); ?>)
                </strong>
            </div>
            <div class="panel-body">
                <p><?php echo audit_escape($rule['recommendation']); ?></p>
                <p><strong><?php echo audit_escape(LANG_AUDIT_COMMAND); ?>:</strong></p>
                <pre class="audit-command"><?php echo audit_escape($rule['command']); ?></pre>
                <div class="audit-locations">
                    <?php foreach ($list as $item): ?>
                        <div>
                            <code><?php echo audit_escape($item['file']); ?>:<?php echo (int)$item['line']; ?></code>
                            <code><?php echo audit_escape($item['snippet']); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
