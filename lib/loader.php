<?php
/**
 * Libraries loader
 *
 * Used to load required libraries
 *
 * @author Serge Dzheigalo <jey@activeunit.com>
 * @package framework
 * @copyright ActiveUnit, Inc. 2001-2004
 * @version 1.0
 * @modified 01-Jan-2004
 */

if (isset($_SERVER['REQUEST_URI'])) {
    Define("THIS_URL", $_SERVER['REQUEST_URI']);
}

$ignore_libs = array();
if (function_exists('mysqli_connect')) {
    $ignore_libs[] = 'mysql.class.php';
} else {
    $ignore_libs[] = 'mysqli.class.php';
}

$preload_libraries = array(
    'perfmonitor.class.php',
    'general.class.php',
    'module.class.php',
    'errors.class.php',
    'objects.class.php',
    'common.class.php',
    'caching.class.php'
);
foreach($preload_libraries as $lib_file) {
    if (file_exists('./lib/'.$lib_file)) include_once('./lib/'.$lib_file);
    $ignore_libs[] = $lib_file;
}

$ignore_libs[] = 'loader.php';
$ignore_libs[] = 'threads.php';

// liblary modules loader
if ($lib_dir = @opendir("./lib")) {
    while (($lib_file = readdir($lib_dir)) !== false) {
        if ((preg_match("/\.php$/", $lib_file)) && !in_array($lib_file, $ignore_libs)) {
            include_once('./lib/'.$lib_file);
        }
    }
    closedir($lib_dir);

}

if (defined('DB_HOST') && DB_HOST) {
    startMeasure('db_connection');
    global $db;
    $db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
    endMeasure('db_connection');
}
