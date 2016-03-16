<?php

chdir('../');
include_once("./config.php");
include_once("./lib/loader.php");
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
include_once("./load_settings.php");

/*
DebMes($_SERVER['REQUEST_URI']);

$command=stripslashes($_GET['command']);
$section=stripslashes($_GET['section']);
$param=stripslashes($_GET['param']);
 */

$done = 0;

if ($command != '' && file_exists('./rc/commands/' . $command . '.bat'))
{
   $commandPath = DOC_ROOT . '/rc/commands/' . $_GET['command'] . '.bat';
   
   if ($param != '')
      $commandPath .= ' "' . $param . '"';
   
   safe_exec($commandPath);
   
   $done = 1;
}
elseif ($command != '' && file_exists('./rc/commands/' . $command . '.sh'))
{
   exec('./commands/' . $command . '.sh' . " > /dev/null &");
   $done = 1;
}
elseif ($command != '' && file_exists('./rc/scripts/' . $command . '.aut'))
{
   $commandPath = SERVER_ROOT . '/apps/autoitv3/AutoIt3.exe ' . DOC_ROOT . '/rc/scripts/' . $_GET['command'] . '.aut';
   
   if ($param != '')
      $commandPath .= ' ' . $param;

   safe_exec('start ' . $commandPath, 1);
   
   $done = 1;
}
elseif ($command != '' && file_exists('./rc/scripts/' . $command . '.au3'))
{
   $commandPath = SERVER_ROOT . '/apps/autoitv3/AutoIt3.exe ' . DOC_ROOT . '/rc/scripts/' . $_GET['command'] . '.au3';
   
   if ($param != '')
      $commandPath .= ' "' . $param . '"';

   safe_exec('start ' . $commandPath, 1);
   
   $done = 1;
}
elseif ($command != '')
{
   echo "command not found";
}

$db->Disconnect(); // closing database connection

if ($done)
{
   echo "OK";
   exit;
}
