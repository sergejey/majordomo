<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();
$checked_time = 0;
setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
$cycleVarName='ThisComputer.'.str_replace('.php', '', basename(__FILE__)).'Run';

echo date("H:i:s") . " running " . basename(__FILE__) . "\n";
SQLExec("DELETE FROM safe_execs");

while (1) {
    if (time() - $checked_time > 20) {
        $checked_time = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
        // saveToCache("MJD:$cycleVarName", $checked_time);
    }

    if ($exclusive = SQLSelectOne("SELECT * FROM safe_execs WHERE EXCLUSIVE = 1 ORDER BY PRIORITY DESC, ID")) {
        if (IsWindowsOS()) {
            $command = utf2win($exclusive['COMMAND']);
        } else {
            $command = $exclusive['COMMAND'];
        }
        SQLExec("DELETE FROM safe_execs WHERE ID = '" . $exclusive['ID'] . "'");
        //DebMes("Executing (exclusive): " . $command,'execs');
        try {
            exec($command);
        } catch (Exception $e) {
            DebMes('Command - '. $command . '. Error: exception ' . get_class($e) . ', ' . $e->getMessage() ,'execs');
        }    
        if ($exclusive['ON_COMPLETE']) {
            //DebMes("On complete code: ".$exclusive['ON_COMPLETE'], 'execs');
            try {
                eval($exclusive['ON_COMPLETE']);
            } catch (Exception $e) {
                DebMes('ON_COMPLETE command - '. $exclusive['ON_COMPLETE'] . ' for command - '.$command.' have error. Error: exception ' . get_class($e) . ', ' . $e->getMessage() ,'execs');
            }
        }
        continue ;
    }

    if ($safe_execs = SQLSelectOne("SELECT * FROM safe_execs ORDER BY PRIORITY DESC, ID")) {
        if (IsWindowsOS()) {
            $command = utf2win($safe_execs['COMMAND']);
        } else {
            $command = $safe_execs['COMMAND'];
        }
        SQLExec("DELETE FROM safe_execs WHERE ID = '" . $safe_execs['ID'] . "'");
        //DebMes("Executing : " . $command,'execs');
        execInBackground($command);
        if ($safe_execs['ON_COMPLETE']) {
            //DebMes("On complete code: ".$safe_execs['ON_COMPLETE'], 'execs');
            try {
                eval($safe_execs['ON_COMPLETE']);
            } catch (Exception $e) {
                DebMes('ON_COMPLETE command - '. $safe_execs['ON_COMPLETE'] . ' for command - '.$command.' have error. Error: exception ' . get_class($e) . ', ' . $e->getMessage() ,'execs');
            }
        }
        continue ;
    }

    if (isRebootRequired() || IsSet($_GET['onetime'])) {
        exit;
    }

    sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
