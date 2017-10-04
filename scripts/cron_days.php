<?php
/**
 *
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */
chdir(__DIR__ . '/../');

include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../lib/loader.php');
include_once(__DIR__ . '/../lib/threads.php');


// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once('./load_settings.php');


$sqlQuery = "SELECT *
               FROM classes
              WHERE TITLE LIKE 'timer'";

$timerClass = SQLSelectOne($sqlQuery);
$o_qry = 1;

if ($timerClass['SUB_LIST'] != '') {
    $o_qry .= " AND (CLASS_ID IN (" . $timerClass['SUB_LIST'] . ")";
    $o_qry .= "  OR CLASS_ID = " . $timerClass['ID'] . ")";
} else {
    $o_qry .= " AND 0";
}


echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;


$sqlQuery = "SELECT ID, TITLE
                     FROM objects
                    WHERE $o_qry";

$objects = SQLSelect($sqlQuery);
$total = count($objects);



for ($i = 0; $i < $total; $i++) {
    echo date('H:i:s') . ' ' . $objects[$i]['TITLE'] . "->onNewDay\n";
    getObject($objects[$i]['TITLE'])->raiseEvent("onNewDay");
}

processSubscriptions('DAYLY');
