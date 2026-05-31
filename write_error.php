<?php

/**
 * Write Error script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <sergejey@gmail.com>
 * @url https://majordomohome.com/
 * @version 1.1
 */

include_once("./config.php");
include_once("./lib/loader.php");

$error = gr('error');
if ($error) {
   DebMes($error,'javascript_errors');
}
