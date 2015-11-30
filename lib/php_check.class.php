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
   $code .= "\n echo 'zzz';";
   $code  = '<?' . $code . '?>';
 
   //echo DOC_ROOT;exit;
   
   $fileName = md5(time() . rand(0, 10000)) . '.php';
   $filePath = DOC_ROOT . '/cached/' . $fileName;
 
   SaveFile($filePath, $code);
 
   if (substr(php_uname(), 0, 7) == "Windows")
   {
      $cmd = DOC_ROOT . '/../server/php/php -l ' . $filePath;
   }
   else
   {
      $cmd = 'php -l ' . $filePath;
   }

   exec($cmd, $out);
   unlink($filePath);

   if (preg_match('/no syntax errors detected/is', $out[0]))
   {
      return false;
   }
   elseif (!trim(implode("\n", $out)))
   {
      return false;
   }
   else
   {
      $res = implode("\n", $out);
      $res = preg_replace('/Errors parsing.+/is', '', $res);
  
      return trim($res) . "\n";
   }
}
