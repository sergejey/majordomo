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

if (defined('HOME_NETWORK') && HOME_NETWORK != '' && !isset($argv[0]) && (!(preg_match('/\/gps\.php/is', $_SERVER['REQUEST_URI']) || preg_match('/\/trackme\.php/is', $_SERVER['REQUEST_URI']) || preg_match('/\/btraced\.php/is', $_SERVER['REQUEST_URI']) || preg_match('/\/rss\.php/is', $_SERVER['REQUEST_URI'])) || $_REQUEST['op'] != '')) {
    $p = preg_quote(HOME_NETWORK);
    $p = str_replace('\*', '\d+?', $p);
    $p = str_replace(',', ' ', $p);
    $p = str_replace('  ', ' ', $p);
    $p = str_replace(' ', '|', $p);
    
    $remoteAddr = getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') : $_SERVER["REMOTE_ADDR"];
    
    if (!preg_match('/' . $p . '/is', $remoteAddr) && $remoteAddr != '127.0.0.1' && trim($remoteAddr) != '::1') {
        if (defined('EXT_ACCESS_USERNAME') && defined('EXT_ACCESS_PASSWORD') && $_SERVER['PHP_AUTH_USER'] == EXT_ACCESS_USERNAME && $_SERVER['PHP_AUTH_PW'] == EXT_ACCESS_PASSWORD) {
            $data = $_SERVER['REMOTE_ADDR'] . " " . date("[d/m/Y:H:i:s]") . " Username and/or password valid. Login: " . $_SERVER['PHP_AUTH_USER'] . " Password: " . $_SERVER['PHP_AUTH_PW'] . "\n";
            DebMes($data, 'auth');
        } elseif (!defined('EXT_ACCESS_USERNAME') && !defined('EXT_ACCESS_PASSWORD')) {
            $data = $_SERVER['REMOTE_ADDR'] . " " . date("[d/m/Y:H:i:s]") . " Username and/or password dont defined and dont needed" . "\n";
            DebMes($data, 'auth');
        } else {
            // header("Location:$PHP_SELF\n\n");
            header("WWW-Authenticate: Basic realm=\"" . PROJECT_TITLE . "\"");
            header("HTTP/1.0 401 Unauthorized");
            echo "Authorization required\n";
            $data = $_SERVER['REMOTE_ADDR'] . " " . date("[d/m/Y:H:i:s]") . " Username and/or password invalid. Login: " . $_SERVER['PHP_AUTH_USER'] . " Password: " . $_SERVER['PHP_AUTH_PW'] . "\n";
            DebMes($data, 'auth');
            exit;
        }
    }
}

if (isset($_SERVER['REQUEST_METHOD']))
{
   $blocked = array('_SERVER', '_COOKIE', 'HTTP_POST_VARS', 'HTTP_GET_VARS', 'HTTP_SERVER_VARS',
                    '_FILES', '_REQUEST', '_ENV');

   if ($_SERVER['REQUEST_METHOD'] == "POST")
   {
      $blocked[] = '_GET';
   }
   else
   {
      $blocked[] = '_POST';
   }

   foreach ($blocked as $b)
   {
      unset($_GET[$b]);
      unset($_POST[$b]);
      unset($_REQUEST[$b]);
   }

   /**
    * Summary of stripit
    * @param mixed $a A
    * @return void
    */
   function stripit(&$a)
   {
      if (is_array($a))
      {
         foreach ($a as $fname => $fval)
         {
            if (is_array($fval))
               stripit($a[$fname]);
            else
               $a[$fname] = stripslashes($fval);
         }
      }
   }

   if ($_SERVER['REQUEST_METHOD'] == "POST")
   {
      $params = $_POST;
   }
   else
   {
      $params = $_GET;
   }

   if (get_magic_quotes_gpc())
   {
      stripit($params);
   }

   foreach ($params as $k => $v)
   {
      ${$k} = $v;
   }

   if (isset($_FILES) && count($_FILES) > 0)
   {
      $ks    = array_keys($_FILES);
      $ksCnt = count($ks);

      for ($i = 0; $i < $ksCnt; $i++)
      {
         $k = $ks[$i];

         ${"$k"}           = $_FILES[$k]['tmp_name'];
         ${"$k" . "_name"} = $_FILES[$k]['name'];
      }
   }
}


function gr($var_name,$type='trim') {
   if (isset($_REQUEST[$var_name])) {
      $value = $_REQUEST[$var_name];
      if (get_magic_quotes_gpc()) {
         stripit($value);
      }
   } else {
      $value='';
   }
   if ($type=='int') {
      $value=(int)$value;
   } elseif ($type=='float') {
      $value=(float)$value;
   } elseif ($type=='trim' && !is_array($value)) {
      $value=trim($value);
   }
   return $value;
}

/**
 * Summary of redirect
 * @param mixed $url    Url
 * @param mixed $owner  Owner (default '')
 * @param mixed $no_sid No sid (default 0)
 * @return void
 */
function redirect($url, $owner = "", $no_sid = 0)
{
   // redirect inside module
   global $session;

   if (is_object($owner))
   {
      $owner->redirect($url);
   }
   else
   {
      $param_str = "";

      if (!$no_sid)
      {
         $replaceStr  = $_SERVER['PHP_SELF'] . '?' . session_name() . '=' . session_id();
         $replaceStr .= '&pd=' . $param_str . '&md=' . $owner->name . '&inst=' . $owner->instance . '&';

         $url = str_replace('?', $replaceStr, $url);
      }

      $url = "Location:$url\n\n";
      $session->save();
      header($url);
      exit;
   }
}

/**
 * Summary of LoadFile
 *
 * @access public
 *
 * @param mixed $filename File name
 * @return string
 */
function LoadFile($filename)
{
   // loading file
   $f     = fopen($filename, "r");
   $data  = "";
   if ($f) {
      $fsize = filesize($filename);
      if ($fsize>0) {
         $data = fread($f, $fsize);
      }
      fclose($f);
   }
   return $data;
}

/**
 * Summary of SaveFile
 * @access public
 *
 * @param mixed $filename File name
 * @param mixed $data     Content
 * @return int
 */
function SaveFile($filename, $data)
{
   // saving file
   $f = fopen("$filename", "w+");

   if ($f)
   {
      flock($f,2);
      fwrite($f, $data);
      flock($f,3);
      fclose($f);
      @chmod($filename, 0666);

      return 1;
   }

   return 0;
}

/**
 * Summary of outHash
 * @param mixed $var  Var
 * @param mixed $hash Hash
 * @return void
 */
function outHash($var, &$hash)
{
   // merge hash keys and values
   if (is_array($var))
   {
      foreach ($var as $k => $v)
      {
         $hash[$k] = $v;
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
 * @access public
 *
 * @param mixed $data   Array to split on pages
 * @param int   $onPage Items per page
 * @param mixed $out    Output hash
 * @return void
 */
function paging(&$data, $onPage, &$out)
{
   // split array using current value of $page parameter
   global $page;

   if (!isset($page))
      $page = 1;

   if (!$onPage)
      $onPage = 30;

   $total_data  = count($data);
   $tatal_pages = 0;

   if ($page == "last")
      $page = ceil($total_data / $onPage);

   $out['PAGE'] = $page;
   $from        = ($page - 1) * $onPage;
   $selPage     = 9999;
   $pages       = array();

   for ($i = 0, $j = 1; $i < $total_data; $i += $onPage, $j++)
   {
      $pages[$j - 1]["NUM"] = (int)($i / $onPage) + 1;
      $tatal_pages++;

      if ($selPage == ($j - 1))
      {
         $out["NEXTPAGE"]           = $pages[$j - 1];
         $pages[$j - 1]['NEXTPAGE'] = 1;
      }

      if (($from >= $i) && ($from < ($i + $onPage)))
      {
         $pages[$j - 1]['SELECTED'] = 1;

         if ($j >= 2)
         {
            $pages[$j - 2]['PREVPAGE'] = 1;

            $out["PREVPAGE"] = $pages[$j - 2];
         }

         $selPage = $j;
      }
   }

   if (count($pages) > 1)
      $out["PAGES"] = $pages;

   $total = $total_data;

   $out["TOTAL"]        = $total;
   $out['TOTAL_PAGES']  = $tatal_pages;
   $out['CURRENT_PAGE'] = $page;
   $out['ON_PAGE']      = $onPage;

   $data = array_splice($data, $from, $onPage);
}

/**
 * Summary of checkEmail
 * @param mixed $email Email
 * @return bool
 */
function checkEmail($email)
{
   $pattern = "/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/";

   if (preg_match($pattern , strtolower($email)))
      return true;

   return false;
}

/**
 * Summary of checkPassword
 * @param mixed $psw Password
 * @return int
 */
function checkPassword($psw)
{
   // checking valid password field
   return (strlen($psw) >= 4) ? 1 : 0;
}

/**
 * Summary of checkGeneral
 * @param mixed $field Field
 * @return int
 */
function checkGeneral($field)
{
   // checking valid general field
   return (strlen($field) >= 2) ? 1 : 0;
}

/**
 * Summary of SendMail
 * @param mixed $from   From
 * @param mixed $to     To
 * @param mixed $subj   Subject
 * @param mixed $body   Body
 * @param mixed $attach Attachement (default '')
 * @return bool
 */
function SendMail($from, $to, $subj, $body, $attach = "")
{
   $mail = new htmlMimeMail();
   $mail->setHeadCharset('UTF-8');
   $mail->setTextCharset('UTF-8');
   $mail->setFrom($from);
   $mail->setSubject($subj);
   $mail->setText($body);

   if ($attach != '')
   {
      $attach_data = $mail->getFile($attach);
      $mail->addAttachment($attach_data, basename($attach), '');
   }

   $result = $mail->send(array($to));

   return $result;
}

/**
 * Summary of SendMail_HTML
 * @param mixed $from   From
 * @param mixed $to     To
 * @param mixed $subj   Subject
 * @param mixed $body   Body
 * @param mixed $attach Attache (default '')
 * @return bool
 */
function SendMail_HTML($from, $to, $subj, $body, $attach = "")
{
   $mail = new htmlMimeMail();

   $mail->setHeadCharset('UTF-8');
   $mail->setHTMLCharset('UTF-8');
   $mail->setFrom($from);
   $mail->setSubject($subj);
   $mail->setHTML($body);

   if (is_array($attach))
   {
      $total = count($attach);
      for ($i = 0; $i < $total; $i++)
      {
         if (file_exists($attach[$i]))
         {
            $attach_data = $mail->getFile($attach[$i]);
            $mail->addAttachment($attach_data, basename($attach[$i]), '');
         }
      }
   }
   elseif ((file_exists($attach)) && ($attach != ""))
   {
      $attach_data = $mail->getFile($attach);
      $mail->addAttachment($attach_data, basename($attach), '');
   }
   $result = $mail->send(array($to));
   if(!$result)
   {
    DebMes('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo.' ('.__FILE__.')');
    //getLogger(__FILE__)->error('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
   }
   else
   {
       DebMes('Message has been sent');
    //getLogger(__FILE__)->debug('Message has been sent');
   }
   return $result;
}

/**
 * Summary of genPassword
 * @param mixed $len Length (default 5)
 * @return string
 */
function genPassword($len = 5)
{
   // make password
   $str = crypt(rand());
   $str = preg_replace("/\W/", "", $str);
   $str = strtolower($str);
   $str = substr($str, 0, $len);

   return $str;
}

/**
 * Summary of recLocalTime
 * @param mixed $table Table
 * @param mixed $id    Id
 * @param mixed $gmt   Gmt
 * @param mixed $field Field
 * @return void
 */
function recLocalTime($table, $id, $gmt, $field = "ADDED")
{
   // UPDATES TIMESTAMP FIELD USING GMT
   $rec = SQLSelectOne("SELECT ID, DATE_FORMAT($field, '%Y-%m-%d %H:%i') as DAT FROM $table WHERE ID='$id'");

   if (isset($rec["ID"]))
   {
      $new_dat = setLocalTime($rec['DAT'], $gmt);
      SQLExec("UPDATE $table SET $field='$new_dat' WHERE ID='$id'");
   }
}

/**
 * Summary of getLocalTime
 * @param mixed $diff (default 0)
 * @return string
 */
function getLocalTime($diff = 0)
{
   // LOCALTIME (with GMT offset)
   $cur_dif = date("Z");
   $nowgm   = gmdate("U") - $cur_dif;
   $now     = $nowgm + $diff * 60 * 60;
   $res     = date("Y-m-d H:i:s", $now);

   return $res;
}

/**
 * Summary of setLocalTime
 * @param mixed $now_date Current time
 * @param mixed $diff     Diff (default 0)
 * @return string
 */
function setLocalTime($now_date, $diff = 0)
{
   // CONVERT FROM CURRENT TIME TO CORRECT TIME USING GMT
   $cur_dif = date("Z");

   if ($now_date == "")
   {
      $nowgm = date("U") - $cur_dif;
   }
   else
   {
      $nowgm = strtotime($now_date) - $cur_dif;
   }

   $now = $nowgm + $diff * 60 * 60;
   $res = date("Y-m-d H:i:s", $now);

   return $res;
}

/**
 * Write Exceptions
 * @param string $errorMessage string Exception message
 * @param string $logLevel     exception level, default=debug
 * @return void
 */
function DebMes($errorMessage, $logLevel = "debug")
{

   if (defined('SETTINGS_SYSTEM_DISABLE_DEBMES') && SETTINGS_SYSTEM_DISABLE_DEBMES==1) return;

   if (defined('SETTINGS_SYSTEM_DEBMES_PATH') && SETTINGS_SYSTEM_DEBMES_PATH!='') {
      $path = SETTINGS_SYSTEM_DEBMES_PATH;
   } elseif (defined('LOG_DIRECTORY') && LOG_DIRECTORY!='') {
      $path = LOG_DIRECTORY;
   } else {
      $path = ROOT . 'cms/debmes';
   }

   if (defined('LOG_MAX_SIZE') && LOG_MAX_SIZE>0) {
      $max_log_size = LOG_MAX_SIZE*1024*1024; // Mb
   } else {
      $max_log_size = 5*1024*1024; // 5 Mb, default
   }

   // DEBUG MESSAGE LOG
   if (!is_dir($path))
   {
      umask(0);
      mkdir($path, 0777);
   }
   if (is_array($errorMessage) || is_object($errorMessage)) {
      $errorMessage=json_encode($errorMessage, JSON_PRETTY_PRINT);
   }

   if ($logLevel!='debug') {
      $today_file=$path.'/'.date('Y-m-d').'_'.$logLevel.'.log';
   } else {
      $today_file=$path.'/'.date('Y-m-d').'.log';
   }

   if (file_exists($today_file) && filesize($today_file)>$max_log_size) return;

   $f=fopen($today_file, "a+");
   if ($f) {
                $tmp=explode(' ', microtime());
                fputs($f, date("H:i:s").' '.$tmp[0]);
                fputs($f, " ".$errorMessage."\n");
                fclose($f);
                @chmod($today_file, 0666);
   }
}

function dprint($data = 0, $stop = 1, $show_history = 0) {
   if (isset($_SERVER['REQUEST_METHOD'])) {
      echo "<pre>";
   } else {
      echo "\n".date('Y-m-d H:i:s ');
   }
   if ($data!==0) {
      if (is_array($data)) {
         print_r($data);
      } elseif (is_object($data)) {
         var_dump($data);
      } else {
         echo $data;
      }
   } else {
      echo date('Y-m-d H:i:s');
   }

   if ($show_history) {
      $e = new \Exception;
      echo ' ('.$e->getTraceAsString().')';
   }

   if (isset($_SERVER['REQUEST_METHOD'])) {
      echo "</pre><hr/>";
      echo str_repeat(' ',4096);
      flush();flush();
      echo "<script type='text/javascript'>window.scrollTo(0,document.body.scrollHeight);</script>";
   } else {
      echo "\n---------------------------------\n";
   }

   if ($stop) {
      exit;
   }
}

/**
 * Method returns logger with meaningful name. In this case much easy to enable\disable
 * logs depending on requirements
 *
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
 * @param mixed $context Context (default null)
 * @return Logger
 */
function getLogger($context = null)
{
/*
   if (empty($context))
      return Logger::getRootLogger();
   elseif (is_string($context))
   {
      if (is_file($context))
         return Logger::getLogger('page.' . basename($context, '.php'));
      else
         return Logger::getLogger($context);
   }
   elseif (is_a($context, 'objects'))
      return Logger::getLogger("class.object.$context->object_title");
   elseif (is_a($context, 'module'))
      return Logger::getLogger("class.module.$context->name");
   elseif (is_object($context))
      return Logger::getLogger('class.' . get_class($context));
   else
      return Logger::getRootLogger();
      */
      return false;
}

/**
 * making hash-table array from plain array
 * @param mixed $title Title
 * @param mixed $ar    Input array
 * @param mixed $out   Output array
 * @return void
 */
function outArray($title, $ar, &$out)
{
   $arCnt = count($ar);

   for ($i = 0; $i < $arCnt; $i++)
   {
      $rec = array();

      $rec['NUM']    = $i + 1;
      $rec['TITLE']  = $ar[$i];
      $out[$title][] = $rec;
   }

   if ($arCnt > 1)
   {
      if ($arCnt % 2 == 0)
      {
         $out[$title][(ceil($arCnt) / 2) - 1]['HALF'] = 1;
      }
      else
      {
         $out[$title][(int)($arCnt / 2)]['HALF'] = 1;
      }
   }
}

/**
 * Summary of multipleArraySelect
 * @param mixed $in    In
 * @param mixed $ar    Ar
 * @param mixed $field Field (defaul 'SELECTED')
 * @return void
 */
function multipleArraySelect($in, &$ar, $field = "SELECTED")
{
   $arCnt = count($ar);

   // support for multiple select form elements
   if ((count($in) == 0) || ($arCnt == 0))
      return;

   for ($i = 0; $i < $arCnt; $i++)
   {
      if (in_array($ar[$i]['TITLE'], $in))
         $ar[$i][$field] = 1;
   }
}

/**
 * Summary of colorizeArray
 * @param mixed $ar    Array
 * @param mixed $every Step (default 2)
 * @return void
 */
function colorizeArray(&$ar, $every = 2)
{
   $arCnt = count($ar);

   for ($i = 0; $i < $arCnt; $i++)
   {
      if (($i + 1) % $every == 0)
      {
         $ar[$i]["NEW_COLOR"] = 1;
      }
   }
}

/**
 * Summary of clearCache
 * @param mixed $verbose Verbode (default 0)
 * @return void
 */
function clearCache($verbose = 0)
{
   if ($handle = opendir(ROOT . 'cms/cached'))
   {
      while (false !== ($file = readdir($handle)))
      {
         if (is_file(ROOT . 'cms/cached/' . $file))
         {
            @unlink(ROOT . 'cms/cached/' . $file);

            if ($verbose)
            {
               echo "File : " . $file . " <b>removed</b><br>\n";
            }
         }
      }

      closedir($handle);
   }
}

/**
 * Summary of checkBadwords
 * @param mixed $s       String
 * @param mixed $replace Replace (default 1)
 * @return mixed
 */
function checkBadwords($s, $replace = 1)
{
   global $badwords;

   if (!isset($badwords))
   {
      $tmp   = SQLSelect("SELECT TITLE FROM badwords");
      $total = count($tmp);

      for ($i = 0; $i < $total; $i++)
      {
         $badwords[] = strtolower($tmp[$i]['TITLE']);
      }
   }

   $total = count($badwords);

   for ($i = 0; $i < $total; $i++)
   {
      $badwords[$i] = str_replace('*', '\w+', $badwords[$i]);

      if (preg_match('/\W' . $badwords[$i] . '\W/is', $s)
          || preg_match('/\W' . $badwords[$i] . '$/is', $s)
          || preg_match('/^' . $badwords[$i] . '\W/is', $s)
          || preg_match('/^' . $badwords[$i] . '$/is', $s))
      {
         if ($replace)
         {
            $s = preg_replace('/^' . $badwords[$i] . '$/is', ' ... ', $s);
            $s = preg_replace('/^' . $badwords[$i] . '\W/is', ' ... ', $s);
            $s = preg_replace('/\W' . $badwords[$i] . '\W/is', ' ... ', $s);
            $s = preg_replace('/\W' . $badwords[$i] . '$/is', ' ... ', $s);
         }
         else
         {
            return 1;
         }
      }
   }

   if ($replace)
   {
      return $s;
   }
   else
   {
      return 0;
   }
}

/**
 * Ping host
 * @param mixed $host Host address
 * @return bool
 */
function ping($host)
{
   if (IsWindowsOS())
      exec(sprintf('ping -n 1 %s', escapeshellarg($host)), $res, $rval);
   elseif (substr(php_uname(), 0, 7) === "FreeBSD")
      exec(sprintf('ping -c 1 -t 5 %s', escapeshellarg($host)), $res, $rval);
   else
      exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($host)), $res, $rval);

   return $rval === 0 && preg_match('/ttl/is', join('', $res));
}

/**
 * Transliterate string
 * @param mixed $string String
 * @return string
 */
function transliterate($string)
{
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

/**
 * Create dir if not exists
 * @param mixed $dirPath Directory path
 * @return void
 */
function CreateDir($dirPath)
{
   if (!is_dir($dirPath))
      @mkdir($dirPath, 0777);
}
