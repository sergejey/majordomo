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

$filename_main = ROOT . 'database_backup/db.sql';
$filename_history = ROOT . 'database_backup/db_history.sql';

$backups_in_row = 0;
DebMes('DB Backup script started.', 'db_backup');

while (1) {
    setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

    if ((time() - $last_backup_main) > $timeout_main || isRebootRequired()) {
        $last_backup_main = time();
        debmes('DB Backup started (main db)', 'db_backup');
        echo "Running main db save...";
        if (file_exists($filename_main)) rename($filename_main, $filename_main . '.prev');
        if (SQLMakeDBDump($filename_main, array('phistory', 'cached_values'))) {
            $backups_in_row++;
            debmes('Main db save OK (in a row: ' . $backups_in_row . ')', 'db_backup');
            echo "OK\n";
        } else {
            debmes('Main db save failed.', 'db_backup');
        }
        if ($backups_in_row >= 10 && is_dir('/tmp/mysql')) {
            debmes('Copying /tmp/mysql to /var/lib/mysql', 'db_backup');
            safe_exec('sudo cp -rf /tmp/mysql/* /var/lib/mysql');
            $backups_in_row = 0;
        }
    }
    if ((time() - $last_backup_history) > $timeout_history || isRebootRequired()) {
        debmes('DB Backup started (history)', 'db_backup');
        echo "Running history db save...";
        if (file_exists($filename_history)) rename($filename_history, $filename_history . '.prev');
        if (SQLMakeTableDump($filename_history, 'phistory')) {
            $last_backup_history = time();
            echo "OK\n";
            debmes('History db save OK', 'db_backup');
        } else {
            debmes('History db save failed.', 'db_backup');
        }
    }
    if (isRebootRequired() || isset($_GET['onetime'])) {
        exit;
    }
    sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
