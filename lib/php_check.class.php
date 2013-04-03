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

function php_syntax_error($code) {
 $code.="\n echo 'zzz';";
 $code='<?'.$code.'?>';
 //echo DOC_ROOT;exit;
 $filename=md5(time().rand(0, 10000)).'.php';
 SaveFile(DOC_ROOT.'/cached/'.$filename, $code);
 if (substr(php_uname(), 0, 7) == "Windows") {
  $cmd=DOC_ROOT.'/../server/php/php -l '.DOC_ROOT.'/cached/'.$filename;
 } else {
  $cmd='php -l '.DOC_ROOT.'/cached/'.$filename;
 }
 exec($cmd, $out);
 unlink(DOC_ROOT.'/cached/'.$filename);
 if (preg_match('/no syntax errors detected/is', $out[0])) {
  return false;
 } elseif (!trim(implode("\n", $out))) {
  return false;
 } else {
  $res=implode("\n", $out);
  $res=preg_replace('/Errors parsing.+/is', '', $res);
  return trim($res)."\n";
 }
}


?>