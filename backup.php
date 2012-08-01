<?
/**
* This file is part of MajorDoMo system. More details at http://smartliving.ru/
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/

 include_once("./config.php");
 include_once("./lib/loader.php");

 startMeasure('TOTAL'); // start calculation of execution time

 include_once(DIR_MODULES."application.class.php");

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database

 include_once(DIR_MODULES.'backup/backup.class.php');
 $b=new backup();
 $b->create_backup();
 echo "DONE";

 $db->Disconnect(); // closing database connection

 endMeasure('TOTAL'); // end calculation of execution time
 performanceReport(); // print performance report

?>