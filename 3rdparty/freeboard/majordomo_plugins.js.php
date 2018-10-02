<?php

chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

?>
(function()
{
	<?php
	$modules=SQLSelect("SELECT ID,NAME FROM project_modules");
	$total = count($modules);
	for ($i = 0; $i < $total; $i++) {
		$path=DIR_MODULES.$modules[$i]['NAME'].'/widgets.inc.php';
		if (file_exists($path)) {
			//$code=LoadFile($path);
			//$code=preg_replace('/<script.+?>/is','',$code);
			//$code=preg_replace('/<\/script>/is','',$code);
			include_once($path);
		}
	}
	?>
}());
