<?php
/*
* @version 0.1 (auto-set)
*/

$img=urldecode($_REQUEST['img']);
$color=$_REQUEST['color'];
$close=$_REQUEST['close'];
$bgcolor=$_REQUEST['bgcolor'];

$out="<a href='#' onClick='window.close()'><img src='".$img."' border=0></a>";

if ($close!='') {
	$out.="<div align=center><a href='#' onClick='window.close()' ".(($color!='')?'style="color:#'.$color.'"':'') .'>'.$close.'</a></div>';
} 

print_r("<html><head><title>...</title></head><body style='margin:0px' bgcolor='".(($bgcolor!="")?'#'.$bgcolor:"#FFFFFF")."'>".$out."</body></html>");
?>