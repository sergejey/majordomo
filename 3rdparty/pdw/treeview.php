<?php

require_once('functions.php');

// Rootname is name of uploadfolder
$uploadPathArr = explode("/", trim($uploadpath,"/"));
$rootname = array_pop($uploadPathArr);

// Get folders from uploadpath and create a list
$dirs = getDirTree(STARTINGPATH, false);
			
//Print treeview to screen
echo '<ul class="treeview">';
echo '   <li class="selected">';
echo '      <a class="root" href="' . $uploadpath . '">' . $rootname . '</a>';
echo 		   renderTree($dirs, $uploadpath);
echo '   </li>';
echo '</ul>';

?>