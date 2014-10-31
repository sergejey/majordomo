<?php
/**
* Libraries loader
*
* Used to load required libraries
*
* @author Serge Dzheigalo <jey@activeunit.com>
* @package framework
* @copyright ActiveUnit, Inc. 2001-2004
* @version 1.0
* @modified 01-Jan-2004
*/


 Define("THIS_URL", $_SERVER['REQUEST_URI']);
// liblary modules loader

if ($lib_dir = @opendir("./lib")) {
  while (($lib_file = readdir($lib_dir)) !== false) {
    if ((preg_match("/\.php$/", $lib_file)) && ($lib_file!="loader.php")) {
     include_once("./lib/$lib_file");
    }
  }
  closedir($lib_dir);
}

// Insert the path where you unpacked log4php
require_once dirname(__FILE__) . '/log4php/Logger.php';

// Tell log4php to use our configuration file.
Logger::configure(dirname(__FILE__) . '/log4php/config.php');

?>
