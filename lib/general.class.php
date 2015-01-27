<?php
/**
* General functions
*
* Frequiently Used Functions
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.3
*/


if (Defined('HOME_NETWORK') && HOME_NETWORK!='' 
    && 
     !$argv[0] 
    && 
     (!(preg_match('/\/gps\.php/is', $_SERVER['REQUEST_URI']) || preg_match('/\/trackme\.php/is', $_SERVER['REQUEST_URI']) || preg_match('/\/btraced\.php/is', $_SERVER['REQUEST_URI'])) || $_REQUEST['op']!='')
    &&
     !preg_match('/\/rss\.php/is', $_SERVER['REQUEST_URI']) 
    &&
    1) {
 $p=preg_quote(HOME_NETWORK);
 $p=str_replace('\*', '\d+?', $p);
 $p=str_replace(',', ' ', $p);
 $p=str_replace('  ', ' ', $p);
 $p=str_replace(' ', '|', $p);
 $remoteAddr = getenv('HTTP_X_FORWARDED_FOR')?getenv('HTTP_X_FORWARDED_FOR'):$_SERVER["REMOTE_ADDR"];
 if (!preg_match('/'.$p.'/is', $remoteAddr) && $remoteAddr!='127.0.0.1') {
  // password required
  //echo "password required for ".$remoteAddr;exit;
  //DebMes("checking access for ".$remoteAddr);

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header("WWW-Authenticate: Basic realm=\"".PROJECT_TITLE."\"");
    header("HTTP/1.0 401 Unauthorized");
    echo "Authorization required\n";
    exit;
  } else {
    if ($_SERVER['PHP_AUTH_USER']!=EXT_ACCESS_USERNAME || $_SERVER['PHP_AUTH_PW']!=EXT_ACCESS_PASSWORD) {
 //    header("Location:$PHP_SELF\n\n");
     header("WWW-Authenticate: Basic realm=\"".PROJECT_TITLE."\"");
     header("HTTP/1.0 401 Unauthorized");
     echo "Authorization required\n";
     exit;
    }
  }


 }
}

$blocked=array('_SERVER', '_COOKIE', 'HTTP_POST_VARS', 'HTTP_GET_VARS', 'HTTP_SERVER_VARS', '_FILES', '_REQUEST', '_ENV');
if ($_SERVER['REQUEST_METHOD']=="POST") {
 $blocked[]='_GET';
} else {
 $blocked[]='_POST';
}
foreach($blocked as $b) {
 unset($_GET[$b]);
 unset($_POST[$b]);
 unset($_REQUEST[$b]);
} 


function stripit(&$a)
{
 if (is_array($a))
 {
   foreach ($a as $fname => $fval)
   {
     if (is_array($fval))
       stripit($a[$fname]);
     else
       $a[$fname]=stripslashes($fval);
   }
 }
}

 if ($_SERVER['REQUEST_METHOD']=="POST") {
  $params=$_POST;
 } else {
  $params=$_GET;
 }

if (get_magic_quotes_gpc())
{
 stripit($params);
}


 foreach($params as $k=>$v) {
  ${$k}=$v;
 }

 if (count($_FILES)>0) {
  $ks=array_keys($_FILES);
  for($i=0;$i<count($ks);$i++) {
   $k=$ks[$i];
   ${"$k"}=$_FILES[$k]['tmp_name'];
   ${"$k"."_name"}=$_FILES[$k]['name'];
  }
 }



// --------------------------------------------------------------------   
 function redirect($url, $owner="", $no_sid=0) {
  // redirect inside module
  global $session;

  if (Is_Object($owner)) {
   $owner->redirect($url);
  } else {
   $param_str="";
   if (!$no_sid) {
    $url=str_replace('?', $_SERVER['PHP_SELF'].'?'.session_name().'='.session_id().'&pd='.$param_str.'&md='.$owner->name.'&inst='.$owner->instance.'&', $url);
   }
   $url="Location:$url\n\n";
   $session->save();
   header($url);
   exit;
  }


 }

/**
* Loads file
*
*
* @param string File name
* @return file content
* @access public
*/
function LoadFile($filename) {
 // loading file
 $f=fopen("$filename", "r");
 $data="";
 $fsize=filesize($filename);
 if ($f && $fsize>0) {
  $data=fread($f, $fsize);
  fclose($f);
 }
 return $data;
}

/**
* Save file
*
*
* @param string File name
* @param string Content
* @access public
*/

function SaveFile($filename, $data) {
 // saving file
 $f=fopen("$filename", "w+");
 if ($f) {
  flock ($f,2);
  fwrite($f, $data);
  flock ($f,3);
  fclose($f);
  @chmod($filename, 0666);
  return 1;
 } else {
  return 0;
 }
}

// --------------------------------------------------------------------   
function outHash($var, &$hash) {
 // merge hash keys and values
 if (is_array($var)) {
  foreach($var as $k=>$v) {
   $hash[$k]=$v;
  }
 }
}

/**
* Paging
*
* This function used to split array into pages and creates some additional output:<br>
* $out['PAGE'] - current page (int) \n
* $out['CURRENT_PAGE'] - current page (int) \n
* $out['NEXTPAGE'] - next page (mixed) \n
* $out['PREVPAGE'] - previous page (mixed) \n
* $out['TOTAL'] - array count \n
* $out['TOTAL_PAGES'] - pages created \n
* $out['ON_PAGE'] - items per page
*
* @param mixed Array to split on pages
* @param int Items per page
* @param mixed Output hash
* @access public
*/
function paging(&$data, $onPage, &$out) {
 // split array using current value of $page parameter
 global $page;

 if (!IsSet($page)) {
  $page=1;
 }

 if (!$onPage) {
  $onPage=30;
 }

 $total_data=count($data);
 $tatal_pages=0;


 if ($page=="last") {
  $page=ceil($total_data/$onPage);
 }


 $out['PAGE']=$page;
 $from=($page-1)*$onPage;
 $selPage=9999;
 $pages=array();


 for($i=0, $j=1; $i<$total_data;$i+=$onPage, $j++) {
   $pages[$j-1]["NUM"]=(int)($i/$onPage)+1;
   $tatal_pages++;
   if ($selPage==($j-1)) {
    $out["NEXTPAGE"]=$pages[$j-1];
    $pages[$j-1]['NEXTPAGE']=1;
   }
     if (($from>=$i) && ($from<($i+$onPage))) {
      $pages[$j-1]['SELECTED']=1;
      if ($j>=2) {
       $pages[$j-2]['PREVPAGE']=1;
       $out["PREVPAGE"]=$pages[$j-2];
      }
      $selPage=$j;
     }
   }

  if (count($pages)>1) {
   $out["PAGES"]=$pages;
  }
 

         $total=$total_data;
         $out["TOTAL"]=$total;
         $out['TOTAL_PAGES']=$tatal_pages;
         $out['CURRENT_PAGE']=$page;
         $out['ON_PAGE']=$onPage;

         $data=array_splice($data, $from, $onPage);
 
}


// --------------------------------------------------------------------   
function checkEmail($email) {
 if (!preg_match("/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/" , strtolower($email))) {
  return false;
 }
 return true;
}

// --------------------------------------------------------------------   
function checkPassword($psw) {
 // checking valid password field
 if (strlen($psw)>=4) {
  return 1;
 } else {
  return 0;
 }
}

// --------------------------------------------------------------------   
function checkGeneral($field) {
 // checking valid general field
 if (strlen($field)>=2) {
  return 1;
 } else {
  return 0;
 }
}

// --------------------------------------------------------------------   
function SendMail($from, $to, $subj, $body, $attach="") {

 $mail = new htmlMimeMail();
 $mail->setFrom($from);
 $mail->setSubject($subj);
 $mail->setText($body);
 $mail->setTextCharset('windows-1251');
 if ($attach!='') {
   $attach_data=$mail->getFile($attach);
   $mail->addAttachment($attach_data, basename($attach), '');
 }
 $result = $mail->send(array($to));
 return $result;

}

// --------------------------------------------------------------------   
function SendMail_HTML($from, $to, $subj, $body, $attach="") {
 // sending email as html
 //global $SERVER_SOFTWARE;

 $mail = new htmlMimeMail();
 $mail->setFrom($from);
 $mail->setSubject($subj);
 $mail->setHTML($body);
 $mail->setHTMLCharset('windows-1251');
 $mail->setHeadCharset('windows-1251');


 if (is_array($attach)) {
  $total=count($attach);
  for($i=0;$i<$total;$i++) {
   if (file_exists($attach[$i])) {
    $attach_data=$mail->getFile($attach[$i]);
    $mail->addAttachment($attach_data, basename($attach[$i]), '');
   }
  }
 } elseif ((file_exists($attach)) && ($attach!="")) {
  $attach_data=$mail->getFile($attach);
  $mail->addAttachment($attach_data, basename($attach), '');
 }
        

 $result = $mail->send(array($to));
 return $result;


}

// --------------------------------------------------------------------   
function genPassword($len=5) {
 // make password
 $str=crypt(rand());
 $str=preg_replace("/\W/", "", $str);
 $str=strtolower($str);
 $str=substr($str, 0, $len);
 return $str;
}

// --------------------------------------------------------------------   
function recLocalTime($table, $id, $gmt, $field="ADDED") {
 // UPDATES TIMESTAMP FIELD USING GMT
 $rec=SQLSelectOne("SELECT ID, DATE_FORMAT($field, '%Y-%m-%d %H:%i') as DAT FROM $table WHERE ID='$id'");
 if (IsSet($rec["ID"])) {
  $new_dat=setLocalTime($rec['DAT'], $gmt);
  SQLExec("UPDATE $table SET $field='$new_dat' WHERE ID='$id'");
 }
}

// --------------------------------------------------------------------   
function getLocalTime($diff=0) {
 // LOCALTIME (with GMT offset)
 $cur_dif=date("Z");
 $nowgm=gmdate("U")-$cur_dif;
 $now=$nowgm+$diff*60*60;
 $res=date("Y-m-d H:i:s", $now);
 return $res;
}

// --------------------------------------------------------------------   
function setLocalTime($now_date, $diff=0) {
 // CONVERT FROM CURRENT TIME TO CORRECT TIME USING GMT
 $cur_dif=date("Z");
 if ($now_date=="") {
  $nowgm=date("U")-$cur_dif;
 } else {
  $nowgm=strtotime($now_date)-$cur_dif;
 }
 $now=$nowgm+$diff*60*60;
 $res=date("Y-m-d H:i:s", $now);
 return $res;
}

// ---------------------------------------------------------
   /**
    * Write Exceptions
    * @param $errorMessage string Exception message
    * @param $logLevel string exception level, default=debug
    */
   function DebMes($errorMessage, $logLevel = "debug")
   {
      // DEBUG MESSAGE LOG
      if (!is_dir(ROOT . 'debmes'))
      {
         mkdir(ROOT . 'debmes');
      }

      $log = Logger::getRootLogger();

      if (defined('SETTINGS_LOGGER_DESTINATION')) {
       $errorDestination = strtolower(SETTINGS_LOGGER_DESTINATION);
       if ($errorDestination == "database") $log = Logger::getLogger('dblog');
       if ($errorDestination == "both") $log = Logger::getLogger('db_and_file');
      }


      //$dbLog = Logger::getLogger('dblog');
      switch ($logLevel) 
      {
         case "trace":
            $log->trace($errorMessage);
            //$dbLog->trace($errorMessage);
            break;
         case "fatal":
            $log->fatal($errorMessage);
            //$dbLog->fatal($errorMessage);
            break;
         case "error":
            $log->error($errorMessage);
            //$dbLog->error($errorMessage);
            break;    
         case "warn":
            $log->warn($errorMessage);
            //$dbLog->warn($errorMessage);
            break;  
         case "info":
            $log->info($errorMessage);
            //$dbLog->info($errorMessage);
            break;
         default:
            $log->debug($errorMessage);
            //$dbLog->debug($errorMessage);
      }
   }

/**
 * Method returns logger with meaningful name. In this case much easy to enable\disable
 * logs depending on requirements
 * @param $context
 * If $context is empty or null, then return root logger
 * If $context is filename or filepath, then return logger with name 'page.filename'
 * If $context is string, then return logger with name $context
 * If $context is object, then depending from object class it returns:
 *  - 'class.object.objectname'
 *  - 'class.module.modulename'
 *  - 'class.objectclass'
 * Example of usage:
 *  - $log = getLogger();
 *  - $log = getLogger('MyLogger');
 *  - $log = getLogger(__FILE__);
 *  - $log = getLogger($this);
 * @return Logger
 */
function getLogger($context = null)
{
  if (empty($context))
    return Logger::getRootLogger();
  elseif (is_string($context)) {
    if (is_file($context))
      return Logger::getLogger('page.'.basename($context, '.php'));
    else
      return Logger::getLogger($context);
  }
  elseif (is_a($context, 'objects'))
    return Logger::getLogger("class.object.$context->object_title");
  elseif (is_a($context, 'module'))
    return Logger::getLogger("class.module.$context->name");
  elseif (is_object($context))
    return Logger::getLogger('class.'.get_class($context));
  else
    return Logger::getRootLogger();
}

// ---------------------------------------------------------
function outArray($title, $ar, &$out) {
 // making hash-table array from plain array
 for($i=0;$i<count($ar);$i++) {
  $rec=array();
  $rec['NUM']=$i+1;
  $rec['TITLE']=$ar[$i];
  $out[$title][]=$rec;
 }
 if (count($ar)>1) {
  if (count($ar)%2==0) {
    $out[$title][(ceil(count($ar))/2)-1]['HALF']=1;
  } else {
    $out[$title][(int)(count($ar)/2)]['HALF']=1;
  }
 }
}

// --------------------------------------------------------------------
function multipleArraySelect($in, &$ar, $field="SELECTED") {
 // support for multiple select form elements
 if ((count($in)==0) || (count($ar)==0)) return;
 for($i=0;$i<count($ar);$i++) {
  if (in_array($ar[$i]['TITLE'], $in)) $ar[$i][$field]=1;
 }
}

// --------------------------------------------------------------------
function colorizeArray(&$ar, $every=2) {
 for($i=0;$i<count($ar);$i++) {
  if (($i+1)%$every==0) {
   $ar[$i]["NEW_COLOR"]=1;
  }
 }
}

// --------------------------------------------------------------------

function clearCache($verbose=0) {
 if ($handle = opendir(ROOT.'cached')) { 
   while (false !== ($file = readdir($handle))) { 
       if (is_file(ROOT.'cached/'.$file)) {
        @unlink(ROOT.'cached/'.$file);
        if ($verbose) {
         echo "File : ".$file." <b>removed</b><br>\n";
        }
       }
   } 
   closedir($handle); 
 } 
}


 function checkBadwords($s, $replace=1) {
  global $badwords;
  if (!isset($badwords)) {
   $tmp=SQLSelect("SELECT TITLE FROM badwords");
   $total=count($tmp);
   for($i=0;$i<$total;$i++) {
    $badwords[]=strtolower($tmp[$i]['TITLE']);
   }
  }

  $total=count($badwords);
  for($i=0;$i<$total;$i++) {
   $badwords[$i]=str_replace('*', '\w+', $badwords[$i]);
   if (preg_match('/\W'.$badwords[$i].'\W/is', $s) 
       || preg_match('/\W'.$badwords[$i].'$/is', $s) 
       || preg_match('/^'.$badwords[$i].'\W/is', $s) 
       || preg_match('/^'.$badwords[$i].'$/is', $s)) {
    if ($replace) {
     $s=preg_replace('/^'.$badwords[$i].'$/is', ' ... ', $s);
     $s=preg_replace('/^'.$badwords[$i].'\W/is', ' ... ', $s);
     $s=preg_replace('/\W'.$badwords[$i].'\W/is', ' ... ', $s);
     $s=preg_replace('/\W'.$badwords[$i].'$/is', ' ... ', $s);
    } else {
     return 1;
    }
   }
  }
  if ($replace) {
   return $s;
  } else {
   return 0;
  }
 }

  function ping($host) {
    if (substr(php_uname(), 0, 7) == "Windows"){
    exec(sprintf('ping -n 1 %s', escapeshellarg($host)), $res, $rval);
    } else {
     exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($host)), $res, $rval);
    }
    return $rval === 0 && preg_match('/ttl/is', join('', $res));
  }

 function transliterate($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
 }

?>