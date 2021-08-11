<?php
/**
 * Timer Cycle script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.4
 */
chdir(dirname(__FILE__));
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./load_settings.php");

resetRebootRequired();

set_time_limit(0);

$db_filename = ROOT . 'database_backup/db.sql';
$db_history_filename = ROOT . 'database_backup/db_history.sql';
$connected = false;
$total_restarts = 0;

while (!$connected) {
    echo "Connecting to database..." . PHP_EOL;
    $connected = $db->Connect();
    if (!$connected) {
        if (file_exists($db_filename) && !IsWindowsOS() && $total_restarts < 3) {
            echo "Restarting mysql service..." . PHP_EOL;
            DebMes('Restarting mysql service...');
            exec("sudo service mysql restart"); // trying to restart mysql
            $total_restarts++;
            sleep(10);
        } else {
            sleep(5);
        }
    }
}
echo "CONNECTED TO DB" . PHP_EOL;
$old_mask = umask(0);

// moving some folders to ./cms/
$move_folders = array(
    'debmes',
    'saverestore',
    'sounds',
    'texts'
);
foreach ($move_folders as $folder) {
    if (is_dir(ROOT . $folder)) {
        echo "Moving " . ROOT . $folder . ' to ' . ROOT . 'cms/' . $folder . "\n";
        DebMes('Moving ' . ROOT . $folder . ' to ' . ROOT . 'cms/' . $folder);
        copyTree(ROOT . $folder, ROOT . 'cms/' . $folder);
        removeTree(ROOT . $folder);
    }
}
// removing some 3rd-party directories
$check_folders = array(
    'blockly' => '3rdparty/blockly',
    'bootstrap' => '3rdparty/bootstrap',
    'js/codemirror' => '3rdparty/codemirror',
    'freeboard' => '3rdparty/freeboard',
    'jquerymobile' => '3rdparty/jquerymobile',
    'jpgraph' => '3rdparty/jpgraph',
    'js/threejs' => '3rdparty/threejs',
    'pdw' => '3rdparty',
    '3rdparty/pdw' => '3rdparty'
);
foreach ($check_folders as $k => $v) {
    if (is_dir(ROOT . $v) && is_dir(ROOT . $k)) {
        echo "Removing " . ROOT . $k . "\n";
        DebMes('Removing ' . ROOT . $k);
        removeTree(ROOT . $k);
    }
}
// check/recreate folders
$dirs_to_check = array(
    ROOT . 'backup',
    ROOT . 'cms/debmes',
    ROOT . 'cms/cached',
    ROOT . 'cms/cached/voice',
    ROOT . 'cms/cached/urls',
    ROOT . 'cms/cached/templates_c'
);
if (defined('SETTINGS_SYSTEM_DEBMES_PATH') && SETTINGS_SYSTEM_DEBMES_PATH != '') {
    $path = SETTINGS_SYSTEM_DEBMES_PATH;
} else {
    $path = ROOT . 'cms/debmes';
}
$dirs_to_check[] = $path;
if (defined('SETTINGS_BACKUP_PATH') && SETTINGS_BACKUP_PATH != '') {
    $dirs_to_check[] = SETTINGS_BACKUP_PATH;
}
foreach ($dirs_to_check as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0777);
    } else {
        chmod($d, 0777);
    }
}

//restoring database backup (if was saving periodically)
if (file_exists($db_filename)) {
    echo "Running: mysql main db restore from file: " . $db_filename . PHP_EOL;
    DebMes("Running: mysql main db restore from file: " . $db_filename);
    $mysql_path = (substr(php_uname(), 0, 7) == "Windows") ? SERVER_ROOT . "/server/mysql/bin/mysql" : 'mysql';
    $mysqlParam = " -u " . DB_USER;
    if (DB_PASSWORD != '')
        $mysqlParam .= " -p" . DB_PASSWORD;
    $mysqlParam .= " " . DB_NAME . " <" . $db_filename;
    exec($mysql_path . $mysqlParam);
    if (file_exists($db_history_filename)) {
        echo "Running: mysql history db restore from file: " . $db_history_filename . PHP_EOL;
        DebMes("Running: mysql history db restore from file: " . $db_history_filename);
        $mysql_path = (substr(php_uname(), 0, 7) == "Windows") ? SERVER_ROOT . "/server/mysql/bin/mysql" : 'mysql';
        $mysqlParam = " -u " . DB_USER;
        if (DB_PASSWORD != '')
            $mysqlParam .= " -p" . DB_PASSWORD;
        $mysqlParam .= " " . DB_NAME . " <" . $db_history_filename;
        exec($mysql_path . $mysqlParam);
    }
}
echo "Checking modules.\n";
//force check installed data
$source = ROOT . 'modules';
if ($dir = @opendir($source)) {
    while (($file = readdir($dir)) !== false) {
        if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
            @unlink(ROOT . "cms/modules_installed/" . $file . ".installed");
        }
    }
}
@unlink(ROOT . "cms/modules_installed/control_modules.installed");

// continue startup
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();

//removing cached data
echo "Clearing the cache.\n";

clearCacheData();

if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    // split data into multiple tables
    $phistory_values = SQLSelect("SELECT VALUE_ID, COUNT(*) AS TOTAL FROM phistory GROUP BY VALUE_ID");
    $total = count($phistory_values);
    for ($i = 0; $i < $total; $i++) {
        $value_id = $phistory_values[$i]['VALUE_ID'];
        $total_data = $phistory_values[$i]['TOTAL'];
        DebMes("Processing data for value $value_id ($total_data) ... ");
        echo "Processing data for value $value_id ($total_data) ... ";
        $table_name = createHistoryTable($value_id);
        moveDataFromMainHistoryToTable($value_id);
        DebMes("Processing of $value_id finished.");
        echo "OK\n";
    }
} else {
    //combine data into single table
    $data = SQLSelect("SHOW TABLES;");
    $tables = array();
    foreach ($data as $v) {
        foreach ($v as $k => $v2) {
            $tables[] = $v2;
        }
    }
    foreach ($tables as $table) {
        if (preg_match('/phistory_value_(\d+)/', $table, $m)) {
            $value_id = $m[1];
            echo "Processing table: $table ($value_id) ...\n";
            DebMes("Processing data for value $value_id ($table) ... ");
            moveDataFromTableToMainHistory($value_id);
            DebMes("Processing of $value_id finished.");
            echo "OK\n";
        }
    }
}

// Removing cycles properties
$qry = "TITLE LIKE 'cycle%Run' OR TITLE LIKE 'cycle%Control' OR TITLE LIKE 'cycle%Disabled' OR TITLE LIKE 'cycle%AutoRestart'";
$thisCompObject = getObject('ThisComputer');
$cycles_records = SQLSelect("SELECT properties.* FROM properties WHERE $qry ORDER BY TITLE");
$total = count($cycles_records);
for ($i = 0; $i < $total; $i++) {
    $property = $cycles_records[$i]['TITLE'];
    $property_id = $thisCompObject->getPropertyByName($property, $thisCompObject->class_id, $thisCompObject->id);
    DebMes("Removing property ThisComputer.$property (object " . $thisCompObject->id . ")", 'threads');
    echo "Removing ThisComputer.$property (object " . $thisCompObject->id . ")";
    //DebMes("Property id: $property_id",'threads');
    if ($property_id) {
        $sqlQuery = "SELECT ID FROM pvalues WHERE PROPERTY_ID = " . (int) $property_id;
        $pvalue = SQLSelectOne($sqlQuery);
        if ($pvalue['ID']) {
            DebMes("Deleting Pvalue: " . $pvalue['ID'], 'threads');
            SQLExec("DELETE FROM phistory WHERE VALUE_ID=" . $pvalue['ID']);
            SQLExec("DELETE FROM pvalues WHERE ID=" . $pvalue['ID']);
        } else {
            DebMes("NO Pvalue for " . $property_id, 'threads');
        }
        SQLExec("DELETE FROM properties WHERE ID=" . $property_id);
        DebMes("REMOVED $property_id", 'threads');
        echo " REMOVED $property_id\n";
    } else {
        DebMes("No property record found for $property", 'threads');
        echo " FAILED\n";
    }
}

clearCacheData();

$cycles = array();

if (is_dir(DOC_ROOT . "/scripts/")) {
    if ($lib_dir = opendir(DOC_ROOT . "/scripts/")) {
        $i = 0;
        while (($lib_file = readdir($lib_dir)) !== false) {
            if ((preg_match('/^(cycle_.+?)\.php/is', $lib_file, $m))) {
                $cycle_name = $m[1];
                // запускаем цикл - результат запуска сам позже отобразится
                newThread(DOC_ROOT . '/scripts/' . $lib_file, $cycle_name);
            }
        }
        closedir($lib_dir);
    }
}

echo "ALL CYCLES STARTED" . "\n";

$last_cycles_control_cycle = time();
$last_cycles_control_hung = time();
$last_cycles_control_check = time();
$cycles_control_restart = time();

// циклы которые надо рестартовать каждый 1 час
// убраны из файла конфиг.пхп
$restart_threads=array('cycle_connect', 'cycle_states',);
$restart_threads=array();

while (True) {
    // chek the cycles and set status if hung каждые 5 минут
    if ((time() - $last_cycles_control_cycle) >= 300) {
        $last_cycles_control_cycle = time();
        foreach ($cycles as $cycle) {
            // проверяем все запущенные циклы
            if ($cycle['state'] == 'Run') {
                // если цикл не отвечает закрываем его и задаем статус hung (повис)
                if (False == ($result = iterationThread($cycle['pipe'], $cycle['name']))) {
                    closeThread($cycle['process'], $cycle['name']);
                }
                // необходима задержка после проверки -
                // ибо на следующей проверке не успевает переключится на другой пайп да и на всякий случай
                usleep(200000);
            }
        }
    }
	//  перезапуск циклов которые необходимо рестортовать автоматически находятся в массиве $restart_threads
    if ((time() - $cycles_control_restart) >= 360 * 60) {
        $cycles_control_restart = time();
        foreach ($cycles as $cycle) {
            if (in_array($cycle['name'], $restart_threads) && $cycle['state'] == 'Exit') {
                newThread($cycle['path'], $cycle['name']);
            }
        }
    }
	
    //  перезапуск всех циклов со статусом hung (повис) каждые 10 минут
    if ((time() - $last_cycles_control_hung) >= 10 * 60) {
        $last_cycles_control_hung = time();
        foreach ($cycles as $cycle) {
            if ($cycle['state'] == 'Hung') {
                newThread($cycle['path'], $cycle['name']);
            }
        }
    }
    // управляем циклами с вебморды
    if ((time() - $last_cycles_control_check) >= 30) {
        $last_cycles_control_check = time();
        foreach ($cycles as $cycle) {
            $control = getGlobal($cycle['name'] . 'Control');
            // если дали команду старт то запустим цикл
            if ($control == 'start') {
                newThread($cycle['path'], $cycle['name']);
            }
            // если дали команду рестарт то остановим весь цикл и по-новой запустим
            if ($control == 'restart') {
                closeThread($cycle['process'], $cycle['name']);
                newThread($cycle['path'], $cycle['name']);
            }
            // если дали команду стоп  - то остановим весь цикл
            if ($control == 'stop' and ($cycle['state'] == 'Run' or $cycle['state'] == 'Hung')) {
                closeThread($cycle['process'], $cycle['name']);
            }
        }
    }

    sleep(1);
	
	if (isRebootRequired() || IsSet($_GET['onetime'])) {
        exit;
    }
	
}

resetRebootRequired();

function newThread($filename, $cycle_name) {
    global $cycles;
    echo date('H:i:s') . " Starting " . $cycle_name . " ... ";
    if (!file_exists($filename)) {
        echo " Error cannot find file   ... " . "\n";
        DebMes(" Error. Cannot find cycle  ... " . $cycle_name . ' ... ', 'threads');
        return False;
    }
    $descriptorspec = array(
        0 => array(
            'pipe',
            'r'
        ),
        1 => array(
            'pipe',
            'w'
        )
    );
    $command = PATH_TO_PHP . ' -q ' . $filename;
    $process = proc_open($command, $descriptorspec, $pipes);
    // SET CYCLES ARRAY DATA
    $cycles[$cycle_name]['name'] = $cycle_name;
    $cycles[$cycle_name]['started'] = time();
    $cycles[$cycle_name]['updated'] = $cycles[$cycle_name]['started'];
    $cycles[$cycle_name]['path'] = $filename;
    $cycles[$cycle_name]['process'] = $process;
    $cycles[$cycle_name]['pipe'] = $pipes[1];
    $cycles[$cycle_name]['cycle_output'] = '';
    stream_set_timeout($cycles[$cycle_name]['pipe'], 5);
    stream_set_blocking($cycles[$cycle_name]['pipe'], False);
    if ($process) {
        $cycles[$cycle_name]['state'] = 'Run';
        DebMes('Cycle ' . $cycle_name . " is STARTING ... ", 'threads');
        echo "OK" . "\n";
        // set cycle is run
        setGlobal($cycle_name . 'Run', time());
        setGlobal($cycle_name . 'Control', '');
    } else {
        $cycles[$cycle_name]['state'] = 'Bad';
        DebMes('Cycle ' . $cycle_name . " is NOT STARTING have error in code ... ", 'threads');
        echo " have ERROR in code ...  " . "\n";
        // set cycle is stop
        setGlobal($cycle_name . 'Run', '');
        setGlobal($cycle_name . 'Control', '');
    }
    return True;
}

function iterationThread($pipe = null, $cycle_name = '') {
    global $cycles;
    if (IsWindowsOS()) {
        $stat = fstat($pipe);
        //echo $cycle_name . ' - name stat - '  ;
        //echo serialize($stat) . "\n";
        if ($stat === False) {
            return False;
        } else {
            $size = $stat['size'];
        }
    } else {
        $size = 10240;
    }
    if ($size > 0) {
        if ($result = fread($pipe, $size)) {
            $cycles[$cycle_name]['updated']=time();
            // если есть названия цикла в ответе из STDOUT то цикл работает
            if (stristr($result, $cycle_name)) {
                // запишем ответ в файл
                $cycles[$cycle_name]['cycle_output'] = $result;
                //echo "Result " .  serialize($result) . "  ... ";
                //echo "OK" . "\n";
                return True;
            }
        }
    }
    // chek old variant
    if (time() - getGlobal($cycle_name . 'Run') < 60) {
        $cycles[$cycle_name]['updated']=time();
        return True;
    }
    return False;
}

function closeThread($process = null, $cycle_name = '') {
    global $cycles;
    $pstatus = proc_get_status($process);
    $pid = $pstatus['pid'];
    $exit_code = $pstatus['exitcode'];
    // SET CYCLE STOP 
    setGlobal($cycle_name . 'Run', '');
    // clear CYCLE  state
    setGlobal($cycle_name . 'Control', '');
    // если получил статус код выхода из цикла - значит он заверешен сам по себе - тогда его надо просто остановить
    // -1 это код работающего цикла - но не отвечающего на запросы
    if ($exit_code != -1) {
        $cycles[$cycle_name]['state'] = 'Exit';
        // запишем выданные данные цикла в файл - файл по имени цикла 
        // будет содержать вывод есно из соответствующего цикла
        echo date('H:i:s') . " Thread $cycle_name with have are 'exit' command and stopped " . " ... ";
        DebMes('Cycle ' . $cycle_name . " with have are 'exit' command and stopped.", 'threads');
        DebMes($cycles[$cycle_name]['cycle_output'], $cycle_name);
        echo "OK" . "\n";
        return True;
    } else {
        // указываем что цикл завис - и есть возможность его рестартовать
        $cycles[$cycle_name]['state'] = 'Hung';
        registerError('cycle_hang ', $cycle_name);
        // запишем выданные данные цикла в файл - файл по имени цикла 
        // будет содержать вывод есно из соответствующего цикла
        echo date('H:i:s') . " Thread $cycle_name is hung. Close with pid N $pid" . " ... ";
        DebMes('Cycle ' . $cycle_name . " is hung. Close with pid N $pid.", 'threads');
        DebMes($cycles[$cycle_name]['cycle_output'], $cycle_name);
        
		// останавливаем все процессы - для того чтобы полностью остановить цикл
        DebMes('Killing ' . $cycle_name . " with pid N $pid.", 'threads');
        if (IsWindowsOS()) {
            $exec_str = "taskkill /F /T /PID $pid";
        } else {
            $exec_str = "kill -9 $pid";
        }
        $output = array();
        $result = exec($exec_str, $output);
        echo "OK" . "\n";
        return True;
    }
}