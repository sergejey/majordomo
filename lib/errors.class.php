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
     * @param int $stop Stop (0 - stop script execution, 1 - show warning and continue script execution)
     * @param int $short Short (default 0)
     * @return void
     */
    public function __construct($description, $stop = 0)
    {
        $script = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        if (!mb_detect_encoding($description, 'UTF-8', true)) {
            $description = iconv('windows-1251', 'UTF-8', $description);
        }

        $e = new \Exception;
        if (defined("DEBUG_MODE")) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $content = <<<FF
         <html>
          <head>
          <title>Error</title>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>          
          </head>
          <body>
          <div class="container">
          <h1>Error</h1>
          <h3>Details</h3>
          <div class="alert alert-danger">$script<br/>$description</div>
          <h3>Backtrace</h3>
          <div><pre>{$e->getTraceAsString()}</pre></div>
          <div>
           <a href="#" class="btn btn-default" onclick="window.history.go(-1);return false;">&lt;&lt;&lt; Back</a>          
           <a href="/diagnostic.php" class="btn btn-success">Submit Diagnostic info</a>
           <a href="#" class="btn btn-default" onclick="window.location.reload();return false;">Reload page</a>
           <a href="/admin.php?md=panel&action=saverestore" class="btn btn-default">Go to Backup section</a>
          </div>
          </div>
          </body>
         </html>
FF;
            } else {
                $content = "ERROR: $script\n$description\n\n";
            }
            echo $content;
        }
        //sendmail("errors@" . PROJECT_DOMAIN, PROJECT_BUGTRACK, "Error reporting: $script", $description);
        if ($stop) exit;
    }

}

/**
 * Custom PHP Error Handler
 * Used for custom handling of PHP errors
 *
 * @param mixed $errno Error number
 * @param mixed $errmsg Error message
 * @param mixed $filename File name
 * @param mixed $linenum Line num
 * @param mixed $vars Variables
 * @return void
 */
function simplisticErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    if (($errno != E_NOTICE)) //&& ( $errno != E_WARNING)
    {
        $err = "PHP warning: $errmsg in $filename on line $linenum\n";
        $err = new custom_error($err, 0, 1);
    }
}

function phpShutDownFunction() {
    $error = error_get_last();
    $e = new \Exception;
    $backtrace = $e->getTraceAsString();
    if ($error['type'] === E_ERROR) {
        DebMes($_SERVER['REQUEST_URI']."\nPHP shutdown error: ".$error['message']."\nBacktrace: ".$backtrace,'errors');
        $err = new custom_error($error['message']);
    }
}

register_shutdown_function('phpShutDownFunction');

//set_error_handler("simplisticErrorHandler");
