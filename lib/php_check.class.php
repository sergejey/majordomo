<?php
/**
* PHP Syntax check
*
*
* @package framework
* @author Serge Dzheigalo <jey@activeunit.com>
* @copyright Serge J. 2012
* @version 1.1
*/

/**
 * Summary of php_syntax_error
 * @param mixed $code Code
 * @return bool|string
 */
function php_syntax_error($code)
{
	if (isItPythonCode($code)) {
		return python_syntax_error($code);
	} else {
		$code .= "\n echo 'welldone';";
		$code  = '<?php ' . $code . '?>';

		$fileName = md5(time() . rand(0, 10000)) . '.php';
		$filePath = DOC_ROOT . '/cms/cached/' . $fileName;
		SaveFile($filePath, $code);
		if (substr(php_uname(), 0, 7) == "Windows") {
			if (defined('PATH_TO_PHP')) {
				$cmd = PATH_TO_PHP . ' -l ' . $filePath;
			} else {
				$cmd = DOC_ROOT . '/../server/php/php -l ' . $filePath;
			}
		} else {
			$cmd = 'php -d display_errors=1 -l ' . $filePath.' 2>&1';
		}
		exec($cmd, $out);
		unlink($filePath);
		$res = implode("\n", $out);
		
		if(preg_match("/\.php on line \b/i", $res)) 
			return trim($res) . "\n";
		if(preg_match("/\Errors parsing\b/i", $res)) 
			return trim($res) . "\n";
		if (preg_match("/welldone\b/i", $res)) {
			return false;
		}
	}
}

