<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$last_backup_main = time();
$last_backup_history = time();

if (defined('SETTINGS_SYSTEM_DB_MAIN_SAVE_PERIOD') && SETTINGS_SYSTEM_DB_MAIN_SAVE_PERIOD > 0) {
    $timeout_main = SETTINGS_SYSTEM_DB_MAIN_SAVE_PERIOD * 60; // get from settings
} else {
    $timeout_main = 15 * 60; // 15 minutes
}

if (defined('SETTINGS_SYSTEM_DB_HISTORY_SAVE_PERIOD') && SETTINGS_SYSTEM_DB_HISTORY_SAVE_PERIOD > 0) {
    $timeout_history = SETTINGS_SYSTEM_DB_HISTORY_SAVE_PERIOD * 60; // get from settings
} else {
    $timeout_history = 60 * 60; // 1 hour
}


if (!is_dir(ROOT . 'database_backup')) {
    umask(0);
    mkdir(ROOT . 'database_backup', 0777);
}


$filename_main = ROOT . 'database_backup/db.sql';
$filename_history = ROOT . 'database_backup/db_history.sql';

if (defined('PATH_TO_MYSQLDUMP')) {
    $mysqlDumpPath = PATH_TO_MYSQLDUMP;
} else {
    if (IsWindowsOS())
        $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
    else
        $mysqlDumpPath = "/usr/bin/mysqldump";
}

$mysqlDumpParam = " -h " . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASSWORD;
$mysqlDumpParam .= " --no-create-db --add-drop-table " . DB_NAME;

$backups_in_row = 0;

while (1) {
    setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

    if ((time() - $last_backup_main) > $timeout_main || isRebootRequired()) {
        echo "Running main db save...";
        if (file_exists($filename_main)) rename($filename_main, $filename_main . '.prev');
        $add_params = '--ignore-table=' . DB_NAME . '.phistory --ignore-table=' . DB_NAME . '.cached_values';
        $dump_file = $filename_main . '.tmp';
        exec($mysqlDumpPath . $mysqlDumpParam . " " . $add_params . "> " . $dump_file);
        if (file_exists($dump_file) && filesize($dump_file) > 0) {
            rename($filename_main . '.tmp', $filename_main);
            $last_backup_main = time();
            $backups_in_row++;
            echo "OK\n";
        } else {
            echo "DB Backup failed\n";
            debmes('DB Backup failed', 'db_backup');
            unlink($filename_main . '.tmp');
        }
        if ($backups_in_row >= 4 && is_dir('/tmp/mysql')) {
            safe_exec('cp -rf /tmp/mysql/* /var/lib/mysql');
            $backups_in_row = 0;
        }
    }
    if ((time() - $last_backup_history) > $timeout_history || isRebootRequired()) {
        echo "Running history db save...";
        if (file_exists($filename_history)) rename($filename_history, $filename_history . '.prev');
        $add_params = 'phistory';
        $dump_file = $filename_history . '.tmp';
        exec($mysqlDumpPath . $mysqlDumpParam . " " . $add_params . "> " . $dump_file);
        if (file_exists($dump_file) && filesize($dump_file) > 0) {
            rename($dump_file, $filename_history);
            $last_backup_history = time();
            echo "OK\n";
        } else {
            echo "DB History Backup failed\n";
            debmes('DB History Backup failed', 'db_backup');
        }
    }
    if (isRebootRequired() || isset($_GET['onetime'])) {
        exit;
    }
    sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
