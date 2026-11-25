<?php
/**
 * Local hint provider with an optional network fallback.
 * No external request is made when a local file exists.
 */
header('Content-Type: application/json; charset=utf-8');
chdir(dirname(__FILE__) . '/../../');
include_once('./config.php');
include_once('./lib/loader.php');
include_once('./load_settings.php');

$session = new session('prj');

$name = isset($_GET['name']) ? (string)$_GET['name'] : '';
$lang = isset($_GET['lang']) ? (string)$_GET['lang'] : 'en';
if (!preg_match('/^[A-Za-z0-9_-]+$/', $name) || !preg_match('/^[a-z_]+$/', $lang)) {
    echo json_encode(array('status' => 'error', 'html' => ''));
    exit;
}

$hintDir = ROOT . 'languages/hints/';
$candidates = array(
    $hintDir . $lang . '/' . $name . '.html',
    $hintDir . 'en/' . $name . '.html',
    $hintDir . 'default/' . $name . '.html'
);
foreach ($candidates as $file) {
    if (is_file($file)) {
        echo json_encode(array('status' => 'ok', 'html' => file_get_contents($file), 'source' => 'local'));
        exit;
    }
}

if (defined('HINTS_OFFLINE') && HINTS_OFFLINE) {
    echo json_encode(array('status' => 'offline', 'html' => ''));
    exit;
}

$remote = 'https://ru.majordomohome.com/Hints/' . rawurlencode($name) . '?skin=hint';
if (function_exists('getURL')) {
    $html = @getURL($remote, 24 * 60 * 60, '', '', false, array(CURLOPT_TIMEOUT => 2, CURLOPT_CONNECTTIMEOUT => 2));
    if ($html) {
        if (preg_match('/<div id=\'wikitext\'>(.*?)<\/div>/s', $html, $matches)) {
            $html = '<div class="mj-hint-content">' . $matches[1] . '</div>';
        }
        if ($lang !== 'ru') {
            $note = defined('LANG_HINT_RUSSIAN_ONLY') ? constant('LANG_HINT_RUSSIAN_ONLY') : 'This hint is available only in Russian.';
            $html = '<p><small>' . htmlspecialchars($note, ENT_QUOTES, 'UTF-8') . '</small></p>' . $html;
        }
        $dir = $hintDir . $lang;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $local = $dir . '/' . $name . '.html';
        $handle = @fopen($local, 'c');
        if ($handle) {
            if (flock($handle, LOCK_EX)) {
                rewind($handle);
                ftruncate($handle, 0);
                fwrite($handle, $html);
                fflush($handle);
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        }
        echo json_encode(array('status' => 'ok', 'html' => $html, 'source' => 'network'));
        exit;
    }
}

echo json_encode(array('status' => 'offline', 'html' => ''));