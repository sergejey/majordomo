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

/**
 * Error handler
 * @category Exceptions
 * @package Framework
 * @author Serge Dzheigalo <jey@tut.by>
 * @copyright 2002 ActiveUnit
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/errors.class.php
 */
class custom_error
{
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
    *
    * @param string $description Error description
    * @param int    $stop        Stop (0 - stop script execution, 1 - show warning and continue script execution)
    * @param int    $short       Short (default 0)
    * @return void
    */
   public function __construct($description, $stop = 0, $short = 0)
   {
      $script      = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
      $description = $script . "\nError:\n" . $description;

      //$log = getLogger($this);
      //$log->error($description);
      DebMes($description.' ('.__FILE__.')');

      if (defined("DEBUG_MODE"))
      {
         if (!$short)
         {
            $this->alert(nl2br($description));
         }
         else
         {
            echo nl2br($description);
         }
      }
      else
      {
         if (!$short)
         {
            $this->alert("");
         }
         else
         {
            echo "Warning...<br>";
         }
      }
      
      sendmail("errors@" . PROJECT_DOMAIN, PROJECT_BUGTRACK, "Error reporting: $script", $description);
      
      if ($stop) exit;
   }

   /**
    * Error processing
    * used to show and log error/warning message
    *
    * @access private
    *
    * @param string $description error description
    * @return void
    */
   public function alert($description)
   {
      echo "<html><head><style>body {font-family:tahoma, arial}</style></head><body>";
      echo "&nbsp;<br>";
      echo "<table border=0 cellspacing=2 cellpadding=15 bgcolor=#FF0000 align=center width=600>";
      echo "<tr><td bgcolor='#FFFFFF'>";
      echo "<p align=center><font color=red><b>Sorry, page is temporary unavailable.<br>";
      echo "<br>Please try again later.</b></font></p>";
      echo "<p align='center'><a href='#' onClick='history.go(-1);'>&lt;&lt;&lt; Back to previous page</a></p>";
      echo "</td></tr>";
      echo "<tr><td bgcolor='#FFFFFF'><p align=center><font color=red><b>$description</b></font></p></td></tr>";
      echo "</table></body></html>";
   }
}

/**
 * Custom PHP Error Handler
 * Used for custom handling of PHP errors
 *
 * @param mixed $errno    Error number
 * @param mixed $errmsg   Error message
 * @param mixed $filename File name
 * @param mixed $linenum  Line num
 * @param mixed $vars     Variables
 * @return void
 */
function simplisticErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
   if (( $errno != E_NOTICE ) ) //&& ( $errno != E_WARNING)
   {
      $err = "PHP warning: $errmsg in $filename on line $linenum\n";
      $err = new custom_error($err, 0, 1);
   }
}

//set_error_handler("simplisticErrorHandler");
