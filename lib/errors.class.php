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

        if (defined('ALLOW_RUNNING_WITH_ERRORS')) {
            global $system_errors_detected;
            if (!isset($system_errors_detected)) $system_errors_detected = array();
            if (!in_array($description, $system_errors_detected)) $system_errors_detected[] = $description;
            return;
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
          <div class="alert alert-danger"><b>$script</b><br/><br/>$description</div>
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

function majordomoGetErrorType($error_level = 0) {
    $error_names = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    ];
    if (isset($error_names[$error_level])) {
        return $error_names[$error_level];
    } else {
        return '';
    }
}

function majordomoGetErrorDetails($e = 0)
{

    if (isset($_SERVER['REQUEST_URI'])) {
        $message = "URL: " . $_SERVER['REQUEST_URI'];
    } else {
        $message = 'commandline';
        global $argv;
        if (isset($argv[0])) {
            $message .= "\nArguments:" . implode(' ', $argv);
        }
    }



    $error = error_get_last();
    if (is_array($error)) {
        $errorCode = $error['type'];
        $errorType = majordomoGetErrorType($errorCode);
        $message .= "\nPHP exception (code $errorCode, $errorType):\n" . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];
    }

    if (is_object($e)) {
        $errorCode = $e->getCode();
        $errorType = majordomoGetErrorType($errorCode);
        $message .= "\nPHP exception (code $errorCode, $errorType): " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\nBacktrace: " . $e->getTraceAsString();
    }

    $included_files = get_included_files();
    if (is_array($included_files)) {
        $message .= "\nLatest file included: " . end($included_files);
    }

    $evalCode = getEvalCode();
    if ($evalCode != '') {
        $message .= "\nEval code:\n" . $evalCode;
    }

    $performance_data = PerformanceReport(1);
    $message .= "\nPerformance:\n" . implode("\n", $performance_data);

    return $message;
}

function majordomoSaveError($details, $type)
{
    DebMes($details, $type);
    if (isset($_SERVER['REQUEST_URI'])) {
        return;
    }
    dprint($type . ': ' . $details, false);
}

function majordomoExceptionHandler($e)
{
    $message = majordomoGetErrorDetails($e);
    majordomoSaveError($message, 'php_exceptions');
    return true;
}

function majordomoErrorHandler($errno, $errmsg, $filename, $linenum)
{
    if (error_reporting() === 0 || error_reporting() == 4437) return; //whitevast: отключаем ошибки, заглушенные @
    if (in_array($errno, array(E_NOTICE, E_DEPRECATED))) return;

    $errorCode = $errno;
    $errorType = majordomoGetErrorType($errorCode);
    $message = "PHP error level $errorCode $errorType in $filename (line $linenum):\n" . $errmsg . "\n";

    $message .= majordomoGetErrorDetails();
    if ($errno == E_WARNING) {
        if (defined('LOG_PHP_WARNINGS') && LOG_PHP_WARNINGS) {
            majordomoSaveError($message, 'php_warnings');
        }
    } else {
        majordomoSaveError($message, 'php_errors');
    }
}

function phpShutDownFunction()
{
    $error = error_get_last();
    if (!is_array($error)) {
        return;
    }
    $message = majordomoGetErrorDetails();
    if ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR) {
        majordomoSaveError($message, 'php_errors_shutdown');
        $err = new custom_error(nl2br($message));
    } elseif ($error['type'] === E_WARNING) {
        if (defined('LOG_PHP_WARNINGS') && LOG_PHP_WARNINGS) {
            majordomoSaveError($message, 'php_warnings_shutdown');
        }
    }
}

register_shutdown_function('phpShutDownFunction');
set_error_handler("majordomoErrorHandler");
set_exception_handler('majordomoExceptionHandler');
