<?php

/**
 * This file is part of MajorDoMo system. More details at http://smartliving.ru/
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.1
 */

include_once("./config.php");
include_once("./lib/loader.php");

// start calculation of execution time
startMeasure('TOTAL');

include_once(DIR_MODULES . "application.class.php");

include_once("./load_settings.php");
include_once(DIR_MODULES . 'backup/backup.class.php');

$b = new backup();
$b->create_backup();

echo "DONE";

// end calculation of execution time
endMeasure('TOTAL');

// print performance report
performanceReport();

