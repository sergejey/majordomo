<?php
/**
* Error handler
*
* Used to handle errors
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2b
*/

 class error {
/**
* @var string error code
*/
 var $code;
/**
* @var string error description
*/
  var $description;

/**
* Object constructor
*
* @access public
* @param string $description error description
* @param int $stop 0 - stop script execution, 1 - show warning and continue script execution
*/
  function error($description, $stop=0, $short=0) {
   $script='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
   $description=$script."\nError:\n".$description;

   $log = getLogger();
   $log->error($description);

   if (Defined("DEBUG_MODE")) {
    if (!$short) {
     $this->alert(nl2br($description));
    } else {
     echo (nl2br($description));
    }
   } else {
    if (!$short) {
     $this->alert("");
    } else {
     echo "Warning...<br>";
    }
   }
   sendmail("errors@".PROJECT_DOMAIN, PROJECT_BUGTRACK, "Error reporting: $script", $description);
   if ($stop) exit;
  }

/**
* Error processing
*
* used to show and log error/warning message
*
* @access private
* @param string $description error description
*/

 function alert($description) {
  echo "<html><style>body {font-family:tahoma, arial}</style><body>&nbsp;<br>";
  echo "<table border=0 cellspacing=2 cellpadding=15 bgcolor=#FF0000 align=center width=600>";
  echo "<tr><td bgcolor='#FFFFFF'><p align=center><font color=red><b>Sorry, page is temporary unavailable.<br><br>Please try again later.</b></font></p><p align='center'><a href='#' onClick='history.go(-1);'>&lt;&lt;&lt; Back to previous page</a></a></td></tr>";
  echo "<tr><td bgcolor='#FFFFFF'><p align=center><font color=red><b>$description</b></font></p></td></tr>";
  echo "</table>&nbsp;";
  echo "</body></html>";
 }

 }

/**
* Custom PHP Error Handler
*
* Used for custom handling of PHP errors
*/
function simplisticErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    if (( $errno != E_NOTICE ) ) //&& ( $errno != E_WARNING)
    {
        $err = "PHP warning: $errmsg in $filename on line $linenum\n";
        $err=new error($err, 0, 1);
    }
}

//set_error_handler("simplisticErrorHandler");


?>
