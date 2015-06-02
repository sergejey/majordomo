<?php
/*
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.7
 */


/**
 * Say input text with voice level
 * @param mixed $ph Text to say
 * @param mixed $level Voice level
 */
function say($ph, $level = 0)
{
   global $commandLine;
   global $voicemode;
   global $noPatternMode;
   global $ignorePushover;
   global $ignorePushbullet;
   global $ignoreGrowl;
   global $ignoreTwitter;

   $rec = array();
   $rec['MESSAGE']   = $ph;
   $rec['ADDED']     = date('Y-m-d H:i:s');
   $rec['ROOM_ID']   = 0;
   $rec['MEMBER_ID'] = 0;
   
   if ($level > 0)
      $rec['IMPORTANCE'] = $level;
   
   $rec['ID'] = SQLInsert('shouts', $rec);

   if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY != '') 
      eval(SETTINGS_HOOK_BEFORE_SAY);
   
   global $ignoreVoice;
   if ($level >= (int)getGlobal('minMsgLevel') && !$ignoreVoice) 
   { 
      $lang = 'en';
      $google_file = false;
      
      if (defined('SETTINGS_SITE_LANGUAGE')) 
         $lang = SETTINGS_SITE_LANGUAGE;
      
      if (defined('SETTINGS_VOICE_LANGUAGE')) 
         $lang = SETTINGS_VOICE_LANGUAGE;
      
      if (!defined('SETTINGS_TTS_GOOGLE') || SETTINGS_TTS_GOOGLE) 
         $google_file = GoogleTTS($ph, $lang);
      
      if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL=='1') 
      {
         $passed = time() - (int)getGlobal('lastSayTime');
         
         // play intro-sound only if more than 20 seconds passed from the last one
         if ($passed > 20)  
         {
            setGlobal('lastSayTime', time());
            playSound('dingdong', 1, $level);
         }
      }

      if ($google_file)
      {
         @touch($google_file);
         playSound($google_file, 1, $level);
      }
      else
      {
         safe_exec('cscript ' . DOC_ROOT . '/rc/sapi.js ' . $ph, 1, $level);
      }
   }

   if (!$noPatternMode) 
   {
      include_once(DIR_MODULES . 'patterns/patterns.class.php');
      $pt = new patterns();
      $pt->checkAllPatterns();
   }

   if (defined('SETTINGS_PUSHOVER_USER_KEY') && SETTINGS_PUSHOVER_USER_KEY && !$ignorePushover)
   {
      include_once(ROOT . 'lib/pushover/pushover.inc.php');
      
      if (defined('SETTINGS_PUSHOVER_LEVEL'))
      {
         if($level >= SETTINGS_PUSHOVER_LEVEL) 
            postToPushover($ph);
      } 
      elseif ($level > 0) 
      {
         postToPushover($ph);
      }
   }

   if (defined('SETTINGS_PUSHBULLET_KEY') && SETTINGS_PUSHBULLET_KEY && !$ignorePushbullet) 
   {
      include_once(ROOT . 'lib/pushbullet/pushbullet.inc.php');
      
      $prefix = '';
      
      if (defined('SETTINGS_PUSHBULLET_PREFIX') && SETTINGS_PUSHBULLET_PREFIX) 
         $prefix = SETTINGS_PUSHBULLET_PREFIX . ' ';

      if (defined('SETTINGS_PUSHBULLET_LEVEL'))
      {
         if($level >= SETTINGS_PUSHBULLET_LEVEL) 
            postToPushbullet($prefix . $ph);
      }
      elseif ($level > 0) 
      {
         postToPushbullet($prefix.$ph);
      }
   }

   if (defined('SETTINGS_GROWL_ENABLE') && SETTINGS_GROWL_ENABLE && $level >= SETTINGS_GROWL_LEVEL && !$ignoreGrowl) 
   {
      include_once(ROOT . 'lib/growl/growl.gntp.php');
      
      $growl = new Growl(SETTINGS_GROWL_HOST, SETTINGS_GROWL_PASSWORD);
      $growl->setApplication('MajorDoMo', 'Notifications');
      $growl->notify($ph);
   }

   if (defined('SETTINGS_TWITTER_CKEY') && SETTINGS_TWITTER_CKEY && !$ignoreTwitter) 
      postToTwitter($ph);

   if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '') 
      eval(SETTINGS_HOOK_AFTER_SAY);
}

/**
 * @access public
 */
function processCommand($command)
{
   global $pattern_matched;

   if (!$pattern_matched) 
      getObject("ThisComputer")->callMethod("commandReceived", array("command"=>$command));
}

/**
 * Convert DateTime string to unix timespamp or return input string back
 * @param mixed $tm DateTime string
 * @return int|string
 */
function timeConvert($tm)
{
   $tm = trim($tm);
   $trueTime = time();
   
   if (preg_match('/^(\d+):(\d+)$/', $tm, $m)) 
   {
      $hour = $m[1];
      $minute = $m[2];
      $trueTime = mktime($hour, $minute, 0, (int)date('m'), (int)date('d'), (int)date('Y'));
   }
   elseif (preg_match('/^(\d+)$/', $tm, $m))
   {
      $trueTime = $tm;
   }
   
   return $trueTime;
}


/**
 * Title
 *
 * Description
 *
 * @access public
 */
function timeNow($tm = 0)
{
   if (!$tm)
      $tm = time();
   
   $h = (int)date('G', $tm);
   
   $hw = 'часов';
   $ms = "";
   
   if ($h == 1)
      $hw = 'час';
   elseif ($h < 5)
      $hw='часа';
   elseif ($h < 21)
      $hw = 'часов';
   elseif ($h == 21)
      $hw = 'час';
   elseif ($h >= 21)
      $hw = 'часа';
   
   $m = (int)date('i', $tm);
   
   if ($m == 1 || $m == 21 || $m == 31 || $m == 41 || $m == 51)
      $ms = " минута";
   elseif ($m >= 5 && $m <= 20 || $m >= 25 && $m <= 30 || $m >= 35 && $m <= 40 || $m >= 45 && $m <= 50 || $m >= 55 && $m <= 59)
      $ms = " минут";
   elseif ($m >= 22 && $m <= 24 || $m >= 32 && $m <= 34 || $m >= 42 && $m <= 44 || $m >= 52 && $m <= 54)
      $ms = " минуты";
   
   $ms = $m . $ms;
   
   $res = "$h " . ($hw) . " " . ($ms);
   
   return $res;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function isWeekEnd() 
{
   // sunday, saturday
   if (date('w') == 0 || date('w') == 6)
      return true;
   
   return false;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function isWeekDay()
{
   if (IsWeekEnd())
      return false;
   
   return true;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function timeIs($tm)
{
   return (date('H:i') == $tm);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function timeBefore($tm)
{
   $trueTime = timeConvert($tm);
   
   return (time() <= $trueTime);
}
/**
 * Title
 *
 * Description
 *
 * @access public
 */
function timeAfter($tm)
{
   $trueTime = timeConvert($tm);
   
   return (time() >= $trueTime);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function timeBetween($tm1, $tm2)
{
   $trueTime1 = timeConvert($tm1);
   $trueTime2 = timeConvert($tm2);
   $timeInterval = 24*60*60;
   if ($trueTime1 > $trueTime2)
   {
      if ($trueTime2 < time()) 
         $trueTime2 += $timeInterval;
      else
         $trueTime1 -= $timeInterval;
   }

   if ((time() >= $trueTime1) && (time() <= $trueTime2)) 
      return true;
   
   return false;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function addScheduledJob($title, $commands, $datetime, $expire = 60)
{
   $rec = array();
   $rec['TITLE'] = $title;
   $rec['COMMANDS'] = $commands;
   $rec['RUNTIME'] = date('Y-m-d H:i:s', $datetime);
   $rec['EXPIRE'] = date('Y-m-d H:i:s', $datetime + $expire);
   $rec['ID'] = SQLInsert('jobs', $rec);
   
   return $rec['ID'];
}

/**
 * Clear scheduled job by title
 * @param mixed $jobTitle 
 */
function clearScheduledJob($jobTitle)
{
   SQLExec("DELETE 
              FROM jobs 
             WHERE TITLE LIKE '" . DBSafe($jobTitle) . "'");
}

/**
 * Delete scheduled Job
 * @param mixed $jobID Job Id
 */
function deleteScheduledJob($jobID)
{
   SQLExec("DELETE 
              FROM jobs 
             WHERE ID = " . (int)$jobID);
}


/**
 * Title
 *
 * Description
 *
 * @access public
 */
function setTimeOut($title, $commands, $timeout)
{
   clearTimeOut($title);
   return addScheduledJob($title,$commands, time() + $timeout);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function clearTimeOut($title) 
{
   return clearScheduledJob($title);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function timeOutExists($jobTitle) 
{
   $job = SQLSelectOne("SELECT ID 
                          FROM jobs 
                         WHERE PROCESSED = 0 
                           AND TITLE LIKE '" . DBSafe($jobTitle) . "'");
   return (int)$job['ID'];
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function runScheduledJobs()
{
   $jobDate = date('Y-m-d H:i:s');
   
   SQLExec("DELETE 
              FROM jobs 
             WHERE EXPIRE <= '" . $jobDate . "'");
   
   $jobs = SQLSelect("SELECT * 
                        FROM jobs 
                       WHERE PROCESSED = 0 
                         AND EXPIRED   = 0 
                         AND RUNTIME   <= '" . $jobDate . "'");
   
   $total = count($jobs);
   
   for($i = 0; $i < $total; $i++)
   {
      echo "Running job: " . $jobs[$i]['TITLE'] . PHP_EOL;
      
      $jobs[$i]['PROCESSED']= 1;
      $jobs[$i]['STARTED'] = $jobDate;
      
      SQLUpdate('jobs', $jobs[$i]);
      
      $url = BASE_URL . '/objects/?job = ' . $jobs[$i]['ID'];
      $result = trim(getURL($url, 0));
      
      if ($result != 'OK')
         DebMes("Error executing job " . $jobs[$i]['TITLE'] . " (" . $jobs[$i]['ID'] . "): " . $result);
   }
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function textToNumbers($text)
{
   return ($text);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function recognizeTime($text, &$newText) 
{
   $result = 0;
   $found = 0;

   $newTime = time();

   if (preg_match('/через (\d+) секунд.?/isu', textToNumbers($text), $m)) 
   {
      $newTime = time() + $m[1];
      $newText = trim(str_replace($m[0], '', textToNumbers($text)));
      $found = 1;
   }
   elseif (preg_match('/через (\d+) минут.?/isu', textToNumbers($text), $m)) 
   {
      $newTime = time() + $m[1] * 60;
      $newText = trim(str_replace($m[0], '', textToNumbers($text)));
      $found = 1;
   } 
   elseif (preg_match('/через (\d+) час.?/isu', textToNumbers($text), $m)) 
   {
      $newTime = time() + $m[1] * 60 * 60;
      $newText = trim(str_replace($m[0], '', textToNumbers($text)));
      $found = 1;
   }
   elseif (preg_match('/в (\d+):(\d+)/isu', textToNumbers($text), $m)) 
   {
      $newTime = mktime($m[1], $m[2], 0, (int)date('m'), (int)date('d'), (int)date('Y'));
      $newText = trim(str_replace($m[0], '', textToNumbers($text)));
      $found = 1;
   }

   $newText = textToNumbers($newText);
   
   if ($found)
      $result = $newTime;

   return $result;
}


/**
 * Title
 *
 * Description
 *
 * @access public
 */
function registerEvent($eventName, $eventDetails = '', $eventExpire = 365) 
{
   $eventExpire = $eventExpire * 24 * 60 * 60;
   
   $rec = array();
   $rec = SQLSelectOne("SELECT * 
                          FROM events 
                         WHERE EVENT_NAME = '" . DBSafe($eventName) . "' 
                           AND EVENT_TYPE = 'system' 
                         ORDER BY ID DESC LIMIT 1");
   
   $rec['EVENT_NAME'] = $eventName;
   $rec['EVENT_TYPE'] = 'system';
   $rec['DETAILS'] = $eventDetails;
   $rec['ADDED'] = date('Y-m-d H:i:s');
   $rec['EXPIRE'] = date('Y-m-d H:i:s', time() + $eventExpire);
   $rec['PROCESSED'] = 1;
   
   if ($rec['ID'])
   {
      SQLUpdate('events', $rec);
      SQLExec("DELETE 
                 FROM events 
                WHERE EVENT_NAME = '" . $rec['EVENT_NAME'] . "' 
                  AND EVENT_TYPE = '" . $rec['EVENT_TYPE'] . "' 
                  AND ID != " . $rec['ID']);
   } 
   else 
   {
      $rec['ID'] = SQLInsert('events', $rec);
   }
   
   return $rec['ID'];
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function registeredEventTime($eventName)
{
   $rec = SQLSelectOne("SELECT ID, UNIX_TIMESTAMP(ADDED) as TM 
                          FROM events 
                         WHERE EVENT_TYPE = 'system' 
                           AND EVENT_NAME = '" . DBSafe($eventName) . "' 
                         ORDER BY ADDED DESC LIMIT 1");
   if ($rec['ID'])
      return $rec['TM'];
 
   return -1;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function getRandomLine($fileName)
{
   $randomLine = round(rand(0, strlen($fileName)-1));
   
   if (file_exists(ROOT . 'texts/' . $fileName . '.txt')) 
      $fileName = ROOT . 'texts/' . $fileName . '.txt';

   if (file_exists($fileName))
   {
      $data = LoadFile($fileName);
      $data = str_replace("\r", '', $data);
      $data = str_replace("\n\n", "\n", $data);
      $lines = mb_split("\n", $data);
      $total = count($lines);
      $line = $lines[round(rand(0, $total-1))];
      
      if ($line != '')
         $randomLine = $line;
   }

   return $randomLine;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function playSound($filename, $exclusive = 0, $priority = 0)
{
   global $ignoreSound;

   if (file_exists(ROOT.'sounds/'.$filename.'.mp3'))
      $filename = ROOT .'sounds/'.$filename.'.mp3';
   elseif (file_exists(ROOT.'sounds/'.$filename))
      $filename=ROOT.'sounds/'.$filename;

   if (defined('SETTINGS_HOOK_BEFORE_PLAYSOUND') && SETTINGS_HOOK_BEFORE_PLAYSOUND != '')
      eval(SETTINGS_HOOK_BEFORE_PLAYSOUND);

   if (!$ignoreSound)
   {
      if (file_exists($filename))
      {
         if (IsWindowsOS())
            safe_exec(DOC_ROOT.'/rc/madplay.exe '.$filename, $exclusive, $priority);
         else
            safe_exec('mplayer ' . $filename, $exclusive, $priority);
      }
   }

   if (defined('SETTINGS_HOOK_AFTER_PLAYSOUND') && SETTINGS_HOOK_AFTER_PLAYSOUND != '')
      eval(SETTINGS_HOOK_AFTER_PLAYSOUND);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function playMedia($path, $host='localhost')
{

   if (defined('SETTINGS_HOOK_PLAYMEDIA') && SETTINGS_HOOK_PLAYMEDIA != '')
      eval(SETTINGS_HOOK_PLAYMEDIA);

   $terminal = SQLSelectOne("SELECT * 
                               FROM terminals 
                              WHERE HOST LIKE  '" . DBSafe($host) . "' 
                                 OR NAME LIKE  '" . DBSafe($host) . "' 
                                 OR TITLE LIKE '" . DBSafe($host) . "'");
   if (!$terminal['ID']) 
   {
      $terminal = SQLSelectOne("SELECT * 
                                  FROM terminals 
                                 WHERE CANPLAY = 1 
                                 ORDER BY ID");
   }
   
   if (!$terminal['ID']) 
   {
      $terminal = SQLSelectOne("SELECT * 
                                  FROM terminals 
                                 WHERE 1 
                                 ORDER BY ID");
   }

   if (!$terminal['ID']) 
      return 0;

   include_once(DIR_MODULES . 'app_player/app_player.class.php');
   
   $player = new app_player();
   $player->terminal_id = $terminal['ID'];
   $player->play = $path;

   global $ajax;
   $ajax = 1;

   global $command;
   $command = 'refresh';

   $player->intCall = 1;
   $player->usual($out);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function runScript($id, $params = '')
{
   include_once(DIR_MODULES . 'scripts/scripts.class.php');
   
   $sc = new scripts();
   
   return $sc->runScript($id, $params);
}


function callScript($id, $params = '') 
{
   runScript($id, $params);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function getURL($url, $cache = 600, $username = '', $password = '')
{
   $cacheFile = ROOT . 'cached/urls/' . preg_replace('/\W/is', '_', str_replace('http://', '', $url)) . '.html';
   $tmpfname  = ROOT . 'cached/cookie.txt';
   $userAgent = 'Opera/9.80 (Windows NT 6.1; WOW64) Presto/2.12.388 Version/12.14';
   
   $result = null;
   
   if (!is_file($cacheFile) || ((time() - filemtime($cacheFile)) > $cache)) 
   {
      //download
      try
      {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
         curl_setopt($ch, CURLOPT_TIMEOUT, 15);
         
         if ($username != '' && $password != '')
         {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password); 
         }
         
         curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
         curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);

         $result = curl_exec($ch);
         
         if ($cache > 0)
         {
            if (!is_dir(ROOT . 'cached/urls'))
               @mkdir(ROOT . 'cached/urls', 0777);
            
            SaveFile($cacheFile, $result);
         }
      }
      catch(Exception $ex)
      {
         registerError('geturl', $url . ' ' . get_class($ex) . ', ' . $ex->getMessage());
      }
   }
   else
   {
      $result = LoadFile($cacheFile);
   }
   
   return $result;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function safe_exec($command, $exclusive = 0, $priority = 0)
{
   $rec = array();
   $rec['ADDED'] = date('Y-m-d H:i:s');
   $rec['COMMAND'] = $command;
   $rec['EXCLUSIVE'] = $exclusive;
   $rec['PRIORITY'] = $priority;
   $rec['ID'] = SQLInsert('safe_execs', $rec);
   
   return $rec['ID'];
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function execInBackground($cmd)
{
   if (IsWindowsOS())
   {
      try
      {
         system($cmd);
      }
      catch(Exception $ex)
      {
         DebMes('Error: exception ' . get_class($ex) . ', ' . $ex->getMessage() . '.');
      }
   }
   else
   {
      exec($cmd . " > /dev/null &");
   }
} 

function getFilesTree($destination, $sort = 'name')
{
   if (substr($destination, -1) == '/' || substr($destination, -1) == '\\')
      $destination = substr($destination, 0, strlen($destination) -1);

   $res = array();

   if (!is_dir($destination))
      return $res;
   
   if ($dir = @opendir($destination)) 
   { 
      while (($file = readdir($dir)) !== false) 
      { 
         if (is_dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) 
         {
            $tmp = getFilesTree($destination . "/" . $file);
            
            if (is_array($tmp)) 
            {
               foreach($tmp as $elem) 
                  $res[] = $elem;
            }
         }
         elseif (is_file($destination . "/" . $file))
         {
            $res[] = ($destination . "/" . $file);
         }
      }
      
      closedir($dir); 
   }

   if ($sort == 'name') 
      sort($res, SORT_STRING);

   return $res;
}


/**
 * Title
 *
 * Description
 *
 * @access public
 */
function isOnline($host)
{
   $rec = SQLSelectOne("SELECT * 
                          FROM pinghosts 
                         WHERE HOSTNAME LIKE '" . DBSafe($host) . "' 
                            OR TITLE LIKE '" . DBSafe($host) . "'");
   
   if (!$rec['STATUS'] || $rec['STATUS'] == 2)
      return 0;
   
   return 1;
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function checkAccess($objectType, $objectID)
{
   include_once(DIR_MODULES . 'security_rules/security_rules.class.php');
   
   $sc = new security_rules();
   
   return $sc->checkAccess($objectType, $objectID);
}

/**
 * Title
 *
 * Description
 *
 * @access public
 */
function registerError($code = 'custom', $details = '')
{
   $code = trim($code);
   
   if (!$code)
      $code = 'custom';
   
   $error_rec = SQLSelectOne("SELECT * 
                                FROM system_errors 
                               WHERE CODE LIKE '" . DBSafe($code) . "'");
   
   if (!$error_rec['ID']) 
   {
      $error_rec['CODE'] = $code;
      $error_rec['KEEP_HISTORY'] = 1;
      $error_rec['ID'] = SQLInsert('system_errors', $error_rec);
   }
   
   $error_rec['LATEST_UPDATE'] = date('Y-m-d H:i:s');
   $error_rec['ACTIVE'] = (int)$error_rec['ACTIVE'] + 1;
   
   SQLUpdate('system_errors', $error_rec);

   $history_rec = array();
   $history_rec['ERROR_ID'] = $error_rec['ID'];
   $history_rec['COMMENTS'] = $details;
   $history_rec['ADDED'] = $error_rec['LATEST_UPDATE'];

   $history_rec['ID'] = SQLInsert('system_errors_data', $history_rec);

   if (!$error_rec['KEEP_HISTORY'])
   {
      SQLExec("DELETE 
                 FROM system_errors_data 
                WHERE ID != '" . $history_rec['ID'] . "'");
   }
}

/**
 * Возвращает true если ОС - Windows
 * @return bool
 */
function IsWindowsOS()
{
   if (substr(php_uname(), 0, 7) === "Windows") 
      return true;

   return false;
}

function makePayload($data) 
{
   $res = '';
   
   foreach($data as $v)
      $res .= chr($v);
   
   return $res;
}

function HexStringToArray($buf)
{
   $res = array();
   
   for($i = 0; $i < strlen($buf) - 1; $i += 2)
      $res[] = (hexdec($buf[$i] . $buf[$i + 1]));
   
   return $res;
}

function HexStringToString($buf)
{
   $res = '';
   
   for($i = 0; $i < strlen($buf) - 1; $i += 2) 
      $res .= chr(hexdec($buf[$i] . $buf[$i + 1]));
   
   return $res;
}


function binaryToString($buf)
{
   $res = '';
   $bufLength = strlen($buf);
   
   for($i = 0; $i < $bufLength; $i++)
   {
      $num = dechex(ord($buf[$i]));
      
      if (strlen($num) == 1)
         $num = '0' . $num;
      
      $res .= $num;
   }
   
   return $res;
}


function mysort_array($ar, $field = "TITLE")
{
   $k = 1;
   
   while ($k > 0)
   {
      $k = 0;
      for ($i = 1; $i < count($ar); $i++)
      {
         if (strcmp($ar[$i - 1][$field], $ar[$i][$field]) == 1)
         {
            $temp = array();
            $temp = $ar[$i - 1];
            $ar[$i - 1] = $ar[$i];
            $ar[$i] = $temp;
            $k++;
         }
      }
   }
   
   return $ar;
}

function win2utf($in)
{
   return iconv('windows-1251', 'utf-8', $in);
}

function utf2win($in) 
{
   return iconv('utf-8', 'windows-1251', $in);
}

