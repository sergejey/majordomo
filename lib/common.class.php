<?php
/*
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.7
 */


/**
 * Summary of say
 * @param mixed $ph        Phrase
 * @param mixed $level     Level (default 0)
 * @param mixed $member_id Member ID (default 0)
 * @return void
 */
function say($ph, $level = 0, $member_id = 0)
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
   $rec['MEMBER_ID'] = $member_id;

   if ($level > 0) $rec['IMPORTANCE'] = $level;

   $rec['ID'] = SQLInsert('shouts', $rec);

   if ($member_id)
   {
      include_once(DIR_MODULES . 'patterns/patterns.class.php');
      $pt = new patterns();
      $pt->checkAllPatterns($member_id);
   
      return;
   }

   if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY != '')
   {
      eval(SETTINGS_HOOK_BEFORE_SAY);
   }

   processSubscriptions('SAY', array('level' => $level, 'message' => $ph, 'member_id' => $member_id));

   global $ignoreVoice;
   if ($level >= (int)getGlobal('minMsgLevel') && !$ignoreVoice && !$member_id)
   {
      $lang = 'en';
      
      if (defined('SETTINGS_SITE_LANGUAGE'))
      {
         $lang = SETTINGS_SITE_LANGUAGE;
      }
      
      if (defined('SETTINGS_VOICE_LANGUAGE'))
      {
         $lang = SETTINGS_VOICE_LANGUAGE;
      }

      /*
      if (SETTINGS_TTS_ENGINE == 'google')
      {
         $voice_file = GoogleTTS($ph, $lang);
      }
      */
      if (SETTINGS_TTS_ENGINE == 'yandex')
      {
         $voice_file = YandexTTS($ph, $lang);
      }
      else
      {
         $voice_file = false;
      }

      if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL == '1')
      {
         $passed = time() - (int)getGlobal('lastSayTime');

         // play intro-sound only if more than 20 seconds passed from the last one
         if ($passed > 20)
         {
            setGlobal('lastSayTime', time());
            playSound('dingdong', 1, $level);
         }
      }

      if ($voice_file)
      {
         @touch($voice_file);
         playSound($voice_file, 1, $level);
      }
      else
      {
         if (IsWindowsOS())
         {
            safe_exec('cscript ' . DOC_ROOT . '/rc/sapi.js ' . $ph, 1, $level);
         }
         else
         {
            if ($lang == 'ru')
            {
               $ln = 'russian';
            }
            else
            {
               $ln = 'english';
            }

            //safe_exec('echo "' . $ph . '" | festival --language ' . $ln . ' --tts', 1, $level);
         }
      }
   }

   if (!$noPatternMode)
   {
      include_once(DIR_MODULES . 'patterns/patterns.class.php');
      $pt = new patterns();
      $pt->checkAllPatterns($member_id);
   }

   if (defined('SETTINGS_PUSHOVER_USER_KEY') && SETTINGS_PUSHOVER_USER_KEY && !$ignorePushover)
   {
      include_once(ROOT . 'lib/pushover/pushover.inc.php');
      if (defined('SETTINGS_PUSHOVER_LEVEL'))
      {
         if ($level >= SETTINGS_PUSHOVER_LEVEL)
         {
            postToPushover($ph);
         }
      }
      elseif ($level > 0)
      {
         postToPushover($ph);
      }
   }

   if (defined('SETTINGS_PUSHBULLET_KEY') && SETTINGS_PUSHBULLET_KEY && !$ignorePushbullet)
   {
      include_once(ROOT . 'lib/pushbullet/pushbullet.inc.php');
      if (defined('SETTINGS_PUSHBULLET_PREFIX') && SETTINGS_PUSHBULLET_PREFIX)
      {
         $prefix = SETTINGS_PUSHBULLET_PREFIX . ' ';
      }
      else
      {
         $prefix = '';
      }

      if (defined('SETTINGS_PUSHBULLET_LEVEL'))
      {
         if ($level >= SETTINGS_PUSHBULLET_LEVEL)
         {
            postToPushbullet($prefix . $ph);
         }
      }
      elseif ($level > 0)
      {
         postToPushbullet($prefix . $ph);
      }
   }

   if (defined('SETTINGS_GROWL_ENABLE') && SETTINGS_GROWL_ENABLE && $level >= SETTINGS_GROWL_LEVEL && !$ignoreGrowl)
   {
      include_once(ROOT . 'lib/growl/growl.gntp.php');
      $growl = new Growl(SETTINGS_GROWL_HOST, SETTINGS_GROWL_PASSWORD);
      $growl->setApplication('MajorDoMo','Notifications');
      //$growl->registerApplication('http://localhost/img/logo.png');
      $growl->notify($ph);
   }

   if (defined('SETTINGS_TWITTER_CKEY') && SETTINGS_TWITTER_CKEY && !$ignoreTwitter)
   {
      postToTwitter($ph);
   }

   if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '')
   {
      eval(SETTINGS_HOOK_AFTER_SAY);
   }
}

/**
 * Summary of processCommand
 * @param mixed $command Command
 * @return void
 */
function processCommand($command)
{
   global $pattern_matched;
   if (!$pattern_matched)
   {
      getObject("ThisComputer")->callMethod("commandReceived", array("command" => $command));
   }
}

/**
 * Summary of timeConvert
 * @param mixed $tm Time
 * @return int|string
 */
function timeConvert($tm)
{
   $tm = trim($tm);
   
   if (preg_match('/^(\d+):(\d+)$/', $tm, $m))
   {
      $hour     = $m[1];
      $minute   = $m[2];
      $trueTime = mktime($hour, $minute, 0, (int)date('m'), (int)date('d'), (int)date('Y'));
   }
   elseif (preg_match('/^(\d+)$/', $tm, $m))
   {
      $trueTime = $tm;
   }

   return $trueTime;
}


/**
 * Summary of timeNow
 * @param mixed $tm time (default 0)
 * @return string
 */
function timeNow($tm = 0)
{
   if (!$tm)
   {
      $tm = time();
   }

   $h = (int)date('G', $tm);

   if ($h == 0) $hw      = 'часов';
   elseif ($h == 1) $hw  = 'час';
   elseif ($h < 5) $hw   = 'часа';
   elseif ($h < 21) $hw  = 'часов';
   elseif ($h == 21) $hw = 'час';
   elseif ($h >= 21) $hw = 'часа';

   $m = (int)date('i', $tm);

   if ($m == 1 || $m == 21 || $m == 31 || $m == 41 || $m == 51)
   {
      $ms = $m . " минута";
   }
   elseif ($m >= 5 && $m <= 20 || $m >= 25 && $m <= 30 || $m >= 35
        && $m <= 40 || $m >= 45 && $m <= 50 || $m >= 55 && $m <= 59)
   {
      $ms = $m . " минут";
   }
   elseif ($m >= 22 && $m <= 24 || $m >= 32 && $m <= 34 || $m >= 42 && $m <= 44 || $m >= 52 && $m <= 54)
   {
      $ms = $m . " минуты";
   }
   elseif ($m == 0)
   {
      $ms = "";
   }

   $res = "$h " . ($hw) . " " . ($ms);
   return $res;
}

/**
 * Summary of isWeekEnd
 * @return bool
 */
function isWeekEnd()
{
   if (date('w') == 0 || date('w') == 6)
      return true; // sunday, saturday
   
   return false;
}

/**
 * Summary of isWeekDay
 * @return bool
 */
function isWeekDay()
{
   if (IsWeekEnd())
      return false;
   
   return true;
}

/**
 * Summary of timeIs
 * @param mixed $tm Time
 * @return bool
 */
function timeIs($tm)
{
   if (date('H:i') == $tm)
      return true;
   
   return false;
}

/**
 * Summary of timeBefore
 * @param mixed $tm Time
 * @return bool
 */
function timeBefore($tm)
{
   $trueTime = timeConvert($tm);
   
   if (time() <= $trueTime)
      return true;
   
   return false;
}

/**
 * Summary of timeAfter
 * @param mixed $tm Time
 * @return bool
 */
function timeAfter($tm)
{
   $trueTime = timeConvert($tm);
   
   if (time() >= $trueTime)
      return true;
   
   return false;
}

/**
 * Summary of timeBetween
 * @param mixed $tm1 Time 1
 * @param mixed $tm2 Time 2
 * @return bool
 */
function timeBetween($tm1, $tm2)
{
   $trueTime1 = timeConvert($tm1);
   $trueTime2 = timeConvert($tm2);

   if ($trueTime1 > $trueTime2)
   {
      if ($trueTime2 < time())
      {
         $trueTime2 += 24 * 60 * 60;
      }
      else
      {
         $trueTime1 -= 24 * 60 * 60;
      }
   }

   if ((time() >= $trueTime1) && (time() <= $trueTime2))
      return true;
   
   return false;
}

/**
 * Summary of addScheduledJob
 * @param mixed $title    Title
 * @param mixed $commands Commands
 * @param mixed $datetime Date
 * @param mixed $expire   Expire time (default 60)
 * @return mixed
 */
function addScheduledJob($title, $commands, $datetime, $expire = 60)
{
   $rec = array();

   $rec['TITLE']    = $title;
   $rec['COMMANDS'] = $commands;
   $rec['RUNTIME']  = date('Y-m-d H:i:s', $datetime);
   $rec['EXPIRE']   = date('Y-m-d H:i:s', $datetime + $expire);
   $rec['ID']       = SQLInsert('jobs', $rec);

   return $rec['ID'];
}

/**
 * Summary of clearScheduledJob
 * @param mixed $title Title
 * @return void
 */
function clearScheduledJob($title)
{
   SQLExec("DELETE FROM jobs WHERE TITLE LIKE '" . DBSafe($title) . "'");
}

/**
 * Delete job from schedule
 * @param mixed $id Job id
 * @return void
 */
function deleteScheduledJob($id)
{
   SQLExec("DELETE FROM jobs WHERE ID = " . (int)$id);
}


/**
 * Summary of setTimeOut
 * @param mixed $title    Title
 * @param mixed $commands Commands
 * @param mixed $timeout  Timeout
 * @return mixed
 */
function setTimeOut($title, $commands, $timeout)
{
   clearTimeOut($title);
   $res=addScheduledJob($title, $commands, time() + $timeout);
   return $res;
}

/**
 * Summary of clearTimeOut
 * @param mixed $title Title
 * @return void
 */
function clearTimeOut($title)
{
   return clearScheduledJob($title);
}

/**
 * Summary of timeOutExists
 * @param mixed $title Title
 * @return int
 */
function timeOutExists($title)
{
   $job = SQLSelectOne("SELECT ID FROM jobs WHERE PROCESSED = 0 AND TITLE LIKE '" . DBSafe($title) . "'");
   return (int)$job['ID'];
}

/**
 * Summary of runScheduledJobs
 * @return void
 */
function runScheduledJobs()
{
   SQLExec("DELETE FROM jobs WHERE EXPIRE <= '" . date('Y-m-d H:i:s') . "'");

   $sqlQuery = "SELECT *
                  FROM jobs
                 WHERE PROCESSED = 0
                   AND EXPIRED   = 0
                   AND RUNTIME   <= '" . date('Y-m-d H:i:s') . "'";

   $jobs  = SQLSelect($sqlQuery);
   $total = count($jobs);

   for ($i = 0; $i < $total; $i++)
   {
      echo "Running job: " . $jobs[$i]['TITLE'] . "\n";
      $jobs[$i]['PROCESSED'] = 1;
      $jobs[$i]['STARTED']   = date('Y-m-d H:i:s');
      
      SQLUpdate('jobs', $jobs[$i]);
      $url    = BASE_URL . '/objects/?job=' . $jobs[$i]['ID'];
      $result = trim(getURL($url, 0));

      if ($result != 'OK')
      {
         DebMes("Error executing job " . $jobs[$i]['TITLE'] . " (" . $jobs[$i]['ID'] . "): " . $result);
      }
   }
}

/**
 * Summary of textToNumbers
 * @param mixed $text Text
 * @return mixed
 */
function textToNumbers($text)
{
   $newtext = ($text);
   
   return $newtext;
}

/**
 * Summary of recognizeTime
 * @param mixed $text    Text
 * @param mixed $newText New text
 * @return array|double|int
 */
function recognizeTime($text, &$newText)
{
   $result   = 0;
   $found    = 0;
   $new_time = time();
   $text     = ($text);

   if (preg_match('/через (\d+) секунд.?/isu', $text, $m))
   {
      $new_time = time() + $m[1];
      $newText  = trim(str_replace($m[0], '', $text));
      $found    = 1;
   }
   elseif (preg_match('/через (\d+) минут.?/isu', $text, $m))
   {
      $new_time = time() + $m[1] * 60;
      $newText  = trim(str_replace($m[0], '', $text));
      $found    = 1;
   }
   elseif (preg_match('/через (\d+) час.?/isu', $text, $m))
   {
      $new_time = time() + $m[1] * 60 * 60;
      $newText  = trim(str_replace($m[0], '', $text));
      $found    = 1;
   }
   elseif (preg_match('/в (\d+):(\d+)/isu', $text, $m))
   {
      $new_time = mktime($m[1], $m[2], 0, (int)date('m'), (int)date('d'), (int)date('Y'));
      $newText  = trim(str_replace($m[0], '', $text));
      $found    = 1;
   }

   $newText = ($newText);
   
   if ($found)
   {
      $result = $new_time;
   }

   return $result;
}


/**
 * Summary of registerEvent
 * @param mixed $eventName Event name
 * @param mixed $details   Details (default '')
 * @param mixed $expire_in Expire time (default 365)
 * @return mixed
 */
function registerEvent($eventName, $details = '', $expire_in = 365)
{
   $sqlQuery = "SELECT *
                  FROM events
                 WHERE EVENT_NAME = '" . DBSafe($eventName) . "'
                   AND EVENT_TYPE = 'system'
                 ORDER BY ID DESC
                 LIMIT 1";

   $rec = array();
   $rec = SQLSelectOne($sqlQuery);

   $rec['EVENT_NAME'] = $eventName;
   $rec['EVENT_TYPE'] = 'system';
   $rec['DETAILS']    = $details;
   $rec['ADDED']      = date('Y-m-d H:i:s');
   $rec['EXPIRE']     = date('Y-m-d H:i:s', time() + $expire_in * 24 * 60 * 60);
   $rec['PROCESSED']  = 1;

   if ($rec['ID'])
   {
      SQLUpdate('events', $rec);
      $sqlQuery = "DELETE FROM events
                    WHERE EVENT_NAME = '" . $rec['EVENT_NAME'] . "'
                      AND EVENT_TYPE = '" . $rec['EVENT_TYPE'] . "'
                      AND ID         != " . $rec['ID'];
      SQLExec($sqlQuery);
   }
   else
   {
      $rec['ID'] = SQLInsert('events', $rec);
   }
   return $rec['ID'];
}

/**
 * Summary of registeredEventTime
 * @param mixed $eventName Event name
 * @return mixed
 */
function registeredEventTime($eventName)
{
   $sqlQuery = "SELECT ID, UNIX_TIMESTAMP(ADDED) as TM
                  FROM events
                 WHERE EVENT_TYPE = 'system'
                   AND EVENT_NAME = '" . DBSafe($eventName) . "'
                 ORDER BY ADDED DESC
                 LIMIT 1";

   $rec = SQLSelectOne($sqlQuery);

   if ($rec['ID'])
   {
      return $rec['TM'];
   }
   else
   {
      return -1;
   }
}

/**
 * Summary of getRandomLine
 * @param mixed $filename File name
 * @return mixed
 */
function getRandomLine($filename)
{
   if (file_exists(ROOT . 'texts/' . $filename . '.txt'))
   {
      $filename = ROOT . 'texts/' . $filename . '.txt';
   }

   if (file_exists($filename))
   {
      $data  = LoadFile($filename);
      $data  = str_replace("\r", '', $data);
      $data  = str_replace("\n\n", "\n", $data);
      $lines = mb_split("\n", $data);
      $total = count($lines);
      $line  = $lines[round(rand(0, $total - 1))];
      
      if ($line != '')
      {
         return $line;
      }
   }
}

/**
 * Summary of playSound
 * @param mixed $filename  File name
 * @param mixed $exclusive Exclusive (default 0)
 * @param mixed $priority  Priority (default 0)
 * @return void
 */
function playSound($filename, $exclusive = 0, $priority = 0)
{
   global $ignoreSound;

   if (file_exists(ROOT . 'sounds/' . $filename . '.mp3'))
      $filename = ROOT . 'sounds/' . $filename . '.mp3';
   elseif (file_exists(ROOT . 'sounds/' . $filename))
      $filename = ROOT . 'sounds/' . $filename;

   if (defined('SETTINGS_HOOK_BEFORE_PLAYSOUND') && SETTINGS_HOOK_BEFORE_PLAYSOUND != '')
      eval(SETTINGS_HOOK_BEFORE_PLAYSOUND);

   if (!$ignoreSound)
   {
      if (file_exists($filename))
      {
         if (IsWindowsOS())
            safe_exec(DOC_ROOT . '/rc/madplay.exe ' . $filename, $exclusive, $priority);
         else
            safe_exec('mplayer ' . $filename, $exclusive, $priority);
      }
   }

   if (defined('SETTINGS_HOOK_AFTER_PLAYSOUND') && SETTINGS_HOOK_AFTER_PLAYSOUND != '')
      eval(SETTINGS_HOOK_AFTER_PLAYSOUND);
}

/**
 * Summary of playMedia
 * @param mixed $path Path
 * @param mixed $host Host (default 'localhost')
 * @return int
 */
function playMedia($path, $host = 'localhost')
{
   if (defined('SETTINGS_HOOK_PLAYMEDIA') && SETTINGS_HOOK_PLAYMEDIA != '')
   {
      eval(SETTINGS_HOOK_PLAYMEDIA);
   }

   $sqlQuery = "SELECT *
                  FROM terminals
                 WHERE HOST LIKE '" . DBSafe($host) . "'
                    OR NAME LIKE '" . DBSafe($host) . "'
                    OR TITLE LIKE '" . DBSafe($host) . "'";

   $terminal = SQLSelectOne($sqlQuery);

   if (!$terminal['ID'])
   {
      $terminal = SQLSelectOne("SELECT * FROM terminals WHERE CANPLAY = 1 ORDER BY ID");
   }

   if (!$terminal['ID'])
   {
      $terminal = SQLSelectOne("SELECT * FROM terminals WHERE 1 ORDER BY ID");
   }

   if (!$terminal['ID'])
   {
      return 0;
   }

   include_once(DIR_MODULES . 'app_player/app_player.class.php');
   $player = new app_player();
   
   $player->terminal_id = $terminal['ID'];
   $player->play        = $path;

   global $ajax;
   $ajax = 1;

   global $command;
   $command = 'refresh';

   $player->intCall = 1;
   $player->usual($out);
}

/**
 * Summary of runScript
 * @param mixed $id     ID
 * @param mixed $params Params (default '')
 * @return mixed
 */
function runScript($id, $params = '')
{
   include_once(DIR_MODULES . 'scripts/scripts.class.php');
   $sc = new scripts();
   return $sc->runScript($id, $params);
}

/**
 * Summary of callScript
 * @param mixed $id     ID
 * @param mixed $params Params (default '')
 * @return void
 */
function callScript($id, $params = '')
{
   runScript($id, $params);
}

/**
 * Summary of getURL
 * @param mixed $url      Url
 * @param mixed $cache    Cache (default 0)
 * @param mixed $username User name (default '')
 * @param mixed $password Password (default '')
 * @return mixed
 */
function getURL($url, $cache = 0, $username = '', $password = '')
{
   $cache_file = ROOT . 'cached/urls/' . preg_replace('/\W/is', '_', str_replace('http://', '', $url)) . '.html';
   
   if (!$cache || !is_file($cache_file) || ((time() - filemtime($cache_file)) > $cache))
   {
      //download
      try
      {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.80 (Windows NT 6.1; WOW64) Presto/2.12.388 Version/12.14');
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
         curl_setopt($ch, CURLOPT_TIMEOUT, 15);
         
         if ($username != '' || $password != '')
         {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
         }

         $tmpfname = ROOT . 'cached/cookie.txt';
         curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
         curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);

         $result = curl_exec($ch);
      }
      catch (Exception $e)
      {
         registerError('geturl', $url . ' ' . get_class($e) . ', ' . $e->getMessage());
      }

      if ($cache > 0)
      {
         CreateDir(ROOT . 'cached/urls');
         SaveFile($cache_file, $result);
      }
   }
   else
   {
      $result = LoadFile($cache_file);
   }

   return $result;
}

/**
 * Summary of safe_exec
 * @param mixed $command   Command
 * @param mixed $exclusive Exclusive (default 0)
 * @param mixed $priority  Priority (default 0)
 * @return mixed
 */
function safe_exec($command, $exclusive = 0, $priority = 0)
{
   $rec = array();

   $rec['ADDED']     = date('Y-m-d H:i:s');
   $rec['COMMAND']   = $command;
   $rec['EXCLUSIVE'] = $exclusive;
   $rec['PRIORITY']  = $priority;

   $rec['ID'] = SQLInsert('safe_execs', $rec);
   return $rec['ID'];
}

/**
 * Summary of execInBackground
 * @param mixed $cmd Command
 * @return void
 */
function execInBackground($cmd)
{
   if (IsWindowsOS())
   {
      //pclose(popen("start /B ". $cmd, "r"));
      try
      {
         //pclose(popen("start /B ". $cmd, "r"));
         system($cmd);
         //$WshShell = new COM("WScript.Shell");
         //$oExec = $WshShell->Run("cmd /C ".$cmd, 0, false);
         //exec($cmd);
      }
      catch (Exception $e)
      {
         DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
      }
   }
   else
   {
      exec($cmd . " > /dev/null &");
   }
}

/**
 * Summary of getFilesTree
 * @param mixed $destination Destination
 * @param mixed $sort        Sort (default 'name')
 * @return array
 */
function getFilesTree($destination, $sort = 'name')
{
   if (substr($destination, -1) == '/' || substr($destination, -1) == '\\')
   {
      $destination = substr($destination, 0, strlen($destination) - 1);
   }

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
               foreach ($tmp as $elem)
               {
                  $res[] = $elem;
               }
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
   {
      sort($res, SORT_STRING);
   }

   return $res;
}


/**
 * Summary of isOnline
 * @param mixed $host Host
 * @return int
 */
function isOnline($host)
{
   $sqlQuery = "SELECT *
                  FROM pinghosts
                 WHERE HOSTNAME LIKE '" . DBSafe($host) . "'
                    OR TITLE LIKE    '" . DBSafe($host) . "'";
   
   $rec = SQLSelectOne($sqlQuery);
   if (!$rec['STATUS'] || $rec['STATUS'] == 2)
   {
      return 0;
   }
   else
   {
      return 1;
   }
}

/**
 * Summary of checkAccess
 * @param mixed $object_type Object type
 * @param mixed $object_id   Object ID
 * @return bool
 */
function checkAccess($object_type, $object_id)
{
   include_once(DIR_MODULES . 'security_rules/security_rules.class.php');
   $sc = new security_rules();
   return $sc->checkAccess($object_type, $object_id);
}

/**
 * Summary of registerError
 * @param mixed $code    Code (default 'custom')
 * @param mixed $details Details (default '')
 * @return void
 */
function registerError($code = 'custom', $details = '')
{
   $code = trim($code);
   
   if (!$code)
   {
      $code = 'custom';
   }

   $error_rec = SQLSelectOne("SELECT * FROM system_errors WHERE CODE LIKE '" . DBSafe($code) . "'");
   
   if (!$error_rec['ID'])
   {
      $error_rec['CODE']         = $code;
      $error_rec['KEEP_HISTORY'] = 1;
      $error_rec['ID']           = SQLInsert('system_errors', $error_rec);
   }

   $error_rec['LATEST_UPDATE'] = date('Y-m-d H:i:s');
   $error_rec['ACTIVE']        = (int)$error_rec['ACTIVE'] + 1;
   SQLUpdate('system_errors', $error_rec);

   $history_rec = array();

   $history_rec['ERROR_ID'] = $error_rec['ID'];
   $history_rec['COMMENTS'] = $details;
   $history_rec['ADDED']    = $error_rec['LATEST_UPDATE'];

   //Temporary disabled
   /*
   $xrayUrl = BASE_URL . ROOTHTML.'popup/xray.html?ajax=1&md=xray&op=getcontent&view_mode=';
   $history_rec['PROPERTIES_DATA'] = getURL($xrayUrl, 0);
   $history_rec['METHODS_DATA']    = getURL($xrayUrl . 'methods', 0);
   $history_rec['SCRIPTS_DATA']    = getURL($xrayUrl . 'scripts', 0);
   $history_rec['TIMERS_DATA']     = getURL($xrayUrl . 'timers', 0);
   $history_rec['EVENTS_DATA']     = getURL($xrayUrl . 'events', 0);
   $history_rec['DEBUG_DATA']      = getURL($xrayUrl . 'debmes', 0);
    */

   $history_rec['ID'] = SQLInsert('system_errors_data', $history_rec);

   if (!$error_rec['KEEP_HISTORY'])
   {
      SQLExec("DELETE FROM system_errors_data WHERE ID != '" . $history_rec['ID'] . "'");
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

/**
 * Summary of makePayload
 * @param mixed $data Data
 * @return string
 */
function makePayload($data)
{
   $res = '';
   foreach ($data as $v)
   {
      $res .= chr($v);
   }

   return $res;
}

/**
 * Summary of HexStringToArray
 * @param mixed $buf Buffer
 * @return array
 */
function HexStringToArray($buf)
{
   $res       = array();
   $bufLength = strlen($buf) - 1;

   for ($i = 0; $i < $bufLength; $i += 2)
   {
      $res[] = (hexdec($buf[$i] . $buf[$i + 1]));
   }

   return $res;
}

/**
 * Summary of HexStringToString
 * @param mixed $buf Buf
 * @return string
 */
function HexStringToString($buf)
{
   $res       = '';
   $bufLength = strlen($buf) - 1;
   for ($i = 0; $i < $bufLength; $i += 2)
   {
      $res .= chr(hexdec($buf[$i] . $buf[$i + 1]));
   }

   return $res;
}

/**
 * Summary of binaryToString
 * @param mixed $buf Buf
 * @return string
 */
function binaryToString($buf)
{
   $res       = '';
   $bufLength = strlen($buf);

   for ($i = 0; $i < $bufLength; $i++)
   {
      $num = dechex(ord($buf[$i]));
      if (strlen($num) == 1)
      {
         $num = '0' . $num;
      }

      $res .= $num;
   }

   return $res;
}


/**
* Title
* @return string
*/
 function return_memory_usage() {
  $size=memory_get_usage(true);
  $unit=array('b','kb','mb','gb','tb','pb');
  return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 }