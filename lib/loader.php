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

include_once('./lib/perfmonitor.class.php');
$ignore_libs[] = 'perfmonitor.class.php';
$ignore_libs[] = 'loader.php';
$ignore_libs[] = 'threads.php';


// liblary modules loader
if ($lib_dir = @opendir("./lib")) {
    $files_loaded = array();
    while (($lib_file = readdir($lib_dir)) !== false) {
        //if ($lib_file=='perfmonitor.class.php' && function_exists('startMeasure')) continue;
        if ((preg_match("/\.php$/", $lib_file))  && !in_array($lib_file, $ignore_libs)) {
            include_once("./lib/$lib_file");
            $files_loaded[] = "./lib/$lib_file";
        }
    }
    closedir($lib_dir);

    /*
    $data = '';
    foreach ($files_loaded as $file) {
        $content = LoadFile($file);
        $content = preg_replace('/^<\?php/', '', $content);
        $content = preg_replace('/\?>$/', '', $content);
        $data .= "\n\n" . $content;
    }
    $data="<?php\n".$data;
    SaveFile('./lib/merged/libraries.php', $data);
    */
}

if (defined('DB_HOST') && DB_HOST) {
    startMeasure('db_connection');
    global $db;
    $db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
    endMeasure('db_connection');
}
