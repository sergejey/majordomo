<?php

/*
* @version 0.2 (auto-set)
*/

include_once("./config.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

// closing database connection
$db->Disconnect();
