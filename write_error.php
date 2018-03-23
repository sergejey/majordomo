<?php

/**
 * Write Error script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by>
 * @url http://smartliving.ru/
 * @version 1.1
 */

include_once("./config.php");
include_once("./lib/loader.php");

if ($error)
{
   DebMes($error,'javascript_errors');
   //DebMes("JAVASCRIPT Error: " . $error);
}
