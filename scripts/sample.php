<?
/*
* @version 0.2 (auto-set)
*/


 include_once("./config.php");
 include_once("./lib/loader.php");


 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database


 // ...

 $db->Disconnect(); // closing database connection

?>
