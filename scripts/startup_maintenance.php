<?php

/*
chdir('../');
include_once("./config.php");
include_once("./lib/loader.php");


$db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
include_once("./load_settings.php");
*/
/*
 * @version 0.1 (auto-set)
 */

if (!function_exists('DebMes')) {
    chdir(dirname(__FILE__) . '/../');
    include_once("./config.php");
    include_once("./lib/loader.php");
    include_once("./load_settings.php");
}

echo "<pre>\n";

DebMes("Running maintenance script");

// BACKUP DATABASE AND FILES

if (defined('SETTINGS_BACKUP_PATH') && SETTINGS_BACKUP_PATH != '' && is_dir(SETTINGS_BACKUP_PATH)) {
    $backups_dir = SETTINGS_BACKUP_PATH;
    $target_dir = $backups_dir;
    if (substr($target_dir, -1) != '/' && substr($target_dir, -1) != '\\')
        $target_dir .= '/';
    $target_dir .= date('Ymd');
} else {
    $backups_dir = DOC_ROOT . '/backup';
    $target_dir = $backups_dir . '/' . date('Ymd');
}

$full_backup = 0;

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777);
    $full_backup = 1;
}


if (!defined('LOG_FILES_EXPIRE')) {
    define('LOG_FILES_EXPIRE', 5);
}
if (!defined('BACKUP_FILES_EXPIRE')) {
    define('BACKUP_FILES_EXPIRE', 10);
}
if (!defined('CACHED_FILES_EXPIRE')) {
    define('CACHED_FILES_EXPIRE', 30);
}


echo "Target: " . $target_dir . PHP_EOL;
echo "Full backup: " . $full_backup . PHP_EOL;

sleep(2);

//removing old log files
if (defined('SETTINGS_SYSTEM_DEBMES_PATH') && SETTINGS_SYSTEM_DEBMES_PATH != '') {
    $path = SETTINGS_SYSTEM_DEBMES_PATH;
} elseif (defined('LOG_DIRECTORY') && LOG_DIRECTORY != '') {
    $path = LOG_DIRECTORY;
} else {
    $path = ROOT . 'cms/debmes';
}

$dir = $path . "/";

foreach (glob($dir . "*") as $file) {
    if (filemtime($file) < time() - LOG_FILES_EXPIRE * 24 * 60 * 60) {
        DebMes("Removing log file " . $file, 'backup');
        @unlink($file);
    }
}

if ($full_backup) {
    DebMes("Backing up files...", 'backup');
    echo "Backing up files...";

    if (!is_dir($target_dir . '/cms')) {
        mkdir($target_dir . '/cms', 0777);
    }
    $cms_dirs = scandir(ROOT . 'cms');
    foreach ($cms_dirs as $d) {
        if ($d == '.' ||
            $d == '..' ||
            $d == 'cached' ||
            $d == 'debmes' ||
            $d == 'saverestore'
        ) continue;
        DebMes("Backing up dir " . ROOT . 'cms/' . $d . ' to ' . $target_dir . '/cms/' . $d, 'backup');
        copyTree(ROOT . 'cms/' . $d, $target_dir . '/cms/' . $d, 1);
    }

    if (defined('PATH_TO_MYSQLDUMP'))
        $mysqlDumpPath = PATH_TO_MYSQLDUMP;

    if ($mysqlDumpPath == '') {
        if (substr(php_uname(), 0, 7) == "Windows")
            $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
        else
            $mysqlDumpPath = "/usr/bin/mysqldump";
    }

    $mysqlDumpParam = " -h " . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASSWORD;
    $mysqlDumpParam .= " --no-create-db --add-drop-table --databases " . DB_NAME;
    $mysqlDumpParam .= " > " . $target_dir . "/" . DB_NAME . ".sql";

    DebMes("Backing up database " . DB_NAME . ' to ' . $target_dir . "/" . DB_NAME . ".sql", 'backup');
    exec($mysqlDumpPath . $mysqlDumpParam);


    echo "OK\n";
}


// removing old files from cms/saverestore
if (is_dir(ROOT . 'cms/saverestore')) {
    $files = scandir(ROOT . 'cms/saverestore');
    foreach ($files as $file) {
        $path = ROOT . 'cms/saverestore/' . $file;
        if (is_file($path)
            && (preg_match('/\.tgz$/', $file) || preg_match('/\.tar\.gz$/', $file) || preg_match('/\.zip\.gz$/', $file))
            && filemtime($path) < time() - BACKUP_FILES_EXPIRE * 24 * 60 * 60
        ) {
            echonow("Removing $path");
            DebMes("Removing $path.", 'backup');
            @unlink($path);
        }
    }
}
// removing old backus
if (is_dir($backups_dir)) {
    $backups = scandir($backups_dir);
    foreach ($backups as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = $backups_dir . '/' . $file;
        if (is_dir($path) && filemtime($path) < time() - BACKUP_FILES_EXPIRE * 24 * 60 * 60) {
            echonow("Removing $path");
            DebMes("Removing $path.", 'backup');
            removeTree($path);
        }
    }
    echo "OK";
} else {
    echo $backups_dir . " not found";
}


// CHECK/REPAIR/OPTIMIZE TABLES
$tables = SQLSelect("SHOW TABLES FROM `" . DB_NAME . "`");
$total = count($tables);
for ($i = 0; $i < $total; $i++) {
    $table = $tables[$i]['Tables_in_' . DB_NAME];
    echo 'Checking table [' . $table . '] ...';
    if ($result = SQLExec("CHECK TABLE " . $table . ";")) {
        echo "OK\n";
    } else {
        echo " broken ... repair ...";
        SQLExec("REPAIR TABLE " . $table . ";");
        echo "OK\n";
    }
}

setGlobal('ThisComputer.started_time', time());
if (time() >= getGlobal('ThisComputer.started_time')) {
    SQLExec("DELETE FROM events WHERE ADDED > NOW()");
    SQLExec("DELETE FROM phistory WHERE ADDED > NOW()");
    SQLExec("DELETE FROM history WHERE ADDED > NOW()");
    SQLExec("DELETE FROM shouts WHERE ADDED > NOW()");
    SQLExec("DELETE FROM jobs WHERE PROCESSED = 1");
    SQLExec("DELETE FROM history WHERE (TO_DAYS(NOW()) - TO_DAYS(ADDED)) >= 5");
}


// removing incorrect pvalues
$sqlQuery = "SELECT pvalues.*, properties.ID AS PROP_ID, objects.ID as OBJ_ID  FROM `pvalues` LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID";
$data = SQLSelect($sqlQuery);
$total = count($data);
$found_pvalues=array();
for ($i = 0; $i < $total; $i++) {
    if (!$data[$i]['PROP_ID'] || !$data[$i]['OBJ_ID']) {
     echo "Removing incorrect property value: " . $data[$i]['PROPERTY_NAME'] . PHP_EOL;
     SQLExec("DELETE FROM phistory WHERE VALUE_ID=" . $data[$i]['ID']);
     SQLExec("DELETE FROM pvalues WHERE ID=" . $data[$i]['ID']);
    } else {
        $found_pvalues[]=$data[$i]['ID'];
    }
}
if (isset($found_pvalues[0])) {
    $sqlQuery = "DELETE FROM phistory WHERE VALUE_ID NOT IN (".implode(',',$found_pvalues).")";
    $data=SQLExec($sqlQuery);
}

// fixing property names
$sqlQuery = "SELECT pvalues.*, objects.TITLE AS OBJECT_TITLE, properties.TITLE AS PROPERTY_TITLE
               FROM pvalues
               JOIN objects ON pvalues.OBJECT_ID = objects.id
               JOIN properties ON pvalues.PROPERTY_ID = properties.id
              WHERE pvalues.PROPERTY_NAME != CONCAT_WS('.', objects.TITLE, properties.TITLE)";

$data = SQLSelect($sqlQuery);
$total = count($data);

for ($i = 0; $i < $total; $i++) {
    $objectProperty = $data[$i]['OBJECT_TITLE'] . "." . $data[$i]['PROPERTY_TITLE'];
    if ($data[$i]['PROPERTY_NAME'])
        echo "Incorrect: " . $data[$i]['PROPERTY_NAME'] . " should be $objectProperty" . PHP_EOL;
    else
        echo "Missing: " . $objectProperty . PHP_EOL;

    $sqlQuery = "SELECT *
                  FROM pvalues
                 WHERE ID = '" . $data[$i]['ID'] . "'";

    $rec = SQLSelectOne($sqlQuery);

    $rec['PROPERTY_NAME'] = $data[$i]['OBJECT_TITLE'] . "." . $data[$i]['PROPERTY_TITLE'];

    SQLUpdate('pvalues', $rec);
}

// Removing duplicates when we have both class property and object property with the same name
include_once(DIR_MODULES . 'classes/classes.class.php');
$cls_module = new classes();

$problems_found = 0;
$properties = SQLSelect("SELECT * FROM properties WHERE OBJECT_ID!=0");
$total = count($properties);
$classes = array();
for ($i = 0; $i < $total; $i++) {
    $prop_title = $properties[$i]['TITLE'];
    $object_id = $properties[$i]['OBJECT_ID'];
    $object_rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . $object_id);
    $class_id = $object_rec['CLASS_ID'];
    if ($class_id) {
        $class_property = array();
        $parent_props = $cls_module->getParentProperties($class_id, '', true);
        foreach ($parent_props as $class_prop) {
            if ($class_prop['TITLE'] == $prop_title) {
                $class_property = $class_prop;
            }
        }
        if ($class_property['ID']) {
            $object_pvalue = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID=" . $properties[$i]['ID'] . " AND OBJECT_ID=" . $properties[$i]['OBJECT_ID']);
            $class_pvalue = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID=" . $class_property['ID'] . " AND OBJECT_ID=" . $properties[$i]['OBJECT_ID']);
            /*
            echo $object_rec['TITLE'].'.'.$properties[$i]['TITLE']."<br/>";
            echo "OBJECT PROPERTY:";
            dprint($properties[$i],false);
            echo "VALUE:";
            dprint($object_pvalue,false);
            echo "CLASS PROPERTY:";
            dprint($class_property,false);
            echo "VALUE:";
            dprint($class_pvalue,false);
            */
            if (!$class_pvalue['ID']) {
                $object_pvalue['PROPERTY_ID'] = $class_property['ID'];
                SQLUpdate('pvalues', $object_pvalue);
            } else {
                SQLExec("DELETE FROM phistory WHERE VALUE_ID=" . $object_pvalue['ID']);
                SQLExec("DELETE FROM pvalues WHERE ID=" . $object_pvalue['ID']);
            }
            SQLExec("DELETE FROM properties WHERE ID=" . $properties[$i]['ID']);
            $problems_found++;
        }
    }
}

clearCacheData();

// removing old errors
if (defined('SETTINGS_ERRORS_KEEP_HISTORY') && SETTINGS_ERRORS_KEEP_HISTORY>0) {
    SQLExec("DELETE FROM system_errors_data WHERE ADDED<'".date('Y-m-d H:i:s',time()-SETTINGS_ERRORS_KEEP_HISTORY*24*60*60)."'");
}

// SET SERIAL
$serial_data = '';
$current_serial = gg('ThisComputer.Serial');
if (IsWindowsOS()) {

} else {
    $serial_data = trim(exec("cat /proc/cpuinfo | grep Serial | cut -d ':' -f 2"));
    $serial_data = ltrim($serial_data, '0');
}

if ($serial_data != '') {
    sg('ThisComputer.Serial', $serial_data);
}
