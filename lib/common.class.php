<?php
/**
 * Summary of sayReply
 * @param mixed $ph        Phrase
 * @param mixed $level     Level (default 0)
 * @param mixed $replyto   Original request
 * @return void
 */
 function sayReply($ph, $level = 0, $replyto='') {
  $source='';
  if ($replyto) {
   $terminal_rec=SQLSelectOne("SELECT * FROM terminals WHERE LATEST_REQUEST LIKE '%".DBSafe($replyto)."%' ORDER BY LATEST_REQUEST_TIME DESC LIMIT 1");
   $orig_msg=SQLSelectOne("SELECT * FROM shouts WHERE SOURCE!='' AND MESSAGE LIKE '%".DBSafe($replyto)."%' AND ADDED>=(NOW() - INTERVAL 30 SECOND) ORDER BY ADDED DESC LIMIT 1");
   if ($orig_msg['ID']) {
    $source=$orig_msg['SOURCE'];
   }
  } else {
   $terminal_rec=SQLSelectOne("SELECT * FROM terminals WHERE LATEST_REQUEST_TIME>=(NOW() - INTERVAL 5 SECOND) ORDER BY LATEST_REQUEST_TIME DESC LIMIT 1");
  }
  if (!$terminal_rec) {
   say($ph, $level);
  } else {
   $source='terminal'.$terminal_rec['ID'];
   $said_status=sayTo($ph, $level, $terminal_rec['NAME']);
   if (!$said_status) {
    say($ph, $level);
   } else {
    $rec = array();
    $rec['MESSAGE']   = $ph;
    $rec['ADDED']     = date('Y-m-d H:i:s');
    $rec['ROOM_ID']   = 0;
    $rec['MEMBER_ID'] = 0;
    if ($level > 0) $rec['IMPORTANCE'] = $level;
    $rec['ID'] = SQLInsert('shouts', $rec);
   }
  }
  processSubscriptions('SAYREPLY', array('level' => $level, 'message' => $ph, 'replyto' => $replyto, 'source'=>$source));
 }

/**
 * Summary of sayTo
 * @param mixed $ph        Phrase
 * @param mixed $level     Level (default 0)
 * @param mixed $destination  Destination terminal name
 * @return void
 */
 function sayTo($ph, $level = 0, $destination = '') {
  if (!$destination) {
   return 0;
  }
  $processed=processSubscriptions('SAYTO', array('level' => $level, 'message' => $ph, 'destination' => $destination));
  $terminal_rec=SQLSelectOne("SELECT * FROM terminals WHERE NAME LIKE '".DBSafe($destination)."'");

  if ($terminal_rec['LINKED_OBJECT'] && $terminal_rec['LEVEL_LINKED_PROPERTY']) {
   $min_level=(int)getGlobal($terminal_rec['LINKED_OBJECT'].'.'.$terminal_rec['LEVEL_LINKED_PROPERTY']);
  } else {
   $min_level=(int)getGlobal('minMsgLevel');
  }
  if ($level < $min_level) {
   return 0;
  }
  if ($terminal_rec['MAJORDROID_API'] && $terminal_rec['HOST']) {
      $service_port = '7999';
      $in = 'tts:' . $ph;
      $address = $terminal_rec['HOST'];
      if (!preg_match('/^\d/', $address)) return 0;
      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      if ($socket === false) {
          return 0;
      }
      $result = socket_connect($socket, $address, $service_port);
      if ($result === false) {
          return 0;
      }
      socket_write($socket, $in, strlen($in));
      socket_close($socket);
      return 1;
  } elseif ($terminal_rec['PLAYER_TYPE']=='ghn') {
      $port=$terminal_rec['PLAYER_PORT'];
      $language = SETTINGS_SITE_LANGUAGE;
      if (!$port) {
          $port='8091';
      }
      $host=$terminal_rec['HOST'];
      $url = 'http://'.$host.':'.$port.'/google-home-notifier?language='.$language.'&text='.urlencode($ph);
      getURL($url,0);
  } elseif ($processed) {
   return 1;
  }
  return 0;
 }

/**
 * Summary of say
 * @param mixed $ph        Phrase
 * @param mixed $level     Level (default 0)
 * @param mixed $member_id Member ID (default 0)
 * @return void
 */
function say($ph, $level = 0, $member_id = 0, $source = '')
{
   global $noPatternMode;
   global $ignoreVoice;

    verbose_log("SAY (level: $level; member: $member; source: $source): ".$ph);

   $rec = array();
   $rec['MESSAGE']   = $ph;
   $rec['ADDED']     = date('Y-m-d H:i:s');
   $rec['ROOM_ID']   = 0;
   $rec['MEMBER_ID'] = $member_id;
   $rec['SOURCE'] = $source;

   if ($level > 0) $rec['IMPORTANCE'] = $level;
   $rec['ID'] = SQLInsert('shouts', $rec);

   if ($member_id)
   {
      $processed=processSubscriptions('COMMAND', array('level' => $level, 'message' => $ph, 'member_id' => $member_id, 'source' => $source));
       if (!$processed) {
           include_once(DIR_MODULES . 'patterns/patterns.class.php');
           $pt = new patterns();
           $res=$pt->checkAllPatterns($member_id);
           processCommand($ph);
       }
      return;
   }

   if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY != '')
   {
      eval(SETTINGS_HOOK_BEFORE_SAY);
   }

   if ($level >= (int)getGlobal('minMsgLevel') && !$ignoreVoice && !$member_id)
   {
      if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL == '1')
      {
         $passed = time() - (int)getGlobal('lastSayTime');
         if ($passed > 20)
         {
            playSound('dingdong', 1, $level);
         }
      }
   }

   setGlobal('lastSayTime', time());
   setGlobal('lastSayMessage', $ph);
   processSubscriptions('SAY', array('level' => $level, 'message' => $ph, 'member_id' => $member_id, 'ignoreVoice'=>$ignoreVoice));

   if (!$noPatternMode)
   {
      include_once(DIR_MODULES . 'patterns/patterns.class.php');
      $pt = new patterns();
      $pt->checkAllPatterns($member_id);
   }

   if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '')
   {
      eval(SETTINGS_HOOK_AFTER_SAY);
   }

   $terminals=SQLSelect("SELECT NAME FROM terminals WHERE (IS_ONLINE=1 AND MAJORDROID_API=1) OR PLAYER_TYPE='googlehomenotifier'");
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    sayTo($ph, $level, $terminals[$i]['NAME']);
   }

}

function ask($prompt, $target = '') {
    processSubscriptions('ASK', array('prompt' => $prompt, 'target' => $target));

    $service_port='7999';
    $in='ask:'.$prompt;

    if (preg_match('/^[\d\.]+$/',$target)) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket) {
            $result = socket_connect($socket, $target, $service_port);
            if ($result) {
                socket_write($socket, $in, strlen($in));
            }
        }
        socket_close($socket);
    } else {
        $qry=1;
        $qry.=" AND MAJORDROID_API=1";
        $qry.=" AND (NAME LIKE '".DBSafe($target)."' OR TITLE LIKE '".DBSafe($target)."')";
        $terminals = SQLSelect("SELECT * FROM terminals WHERE $qry");
        $total = count($terminals);
        for ($i = 0; $i < $total; $i++) {
            $address = $terminals[$i]['HOST'];
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket) {
                $result = socket_connect($socket, $address, $service_port);
                if ($result) {
                    socket_write($socket, $in, strlen($in));
                }
            }
            socket_close($socket);
        }
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


function getNumberWord($number, $suffix) {
    $keys = array(2, 0, 1, 1, 1, 2);
    $mod = $number % 100;
    $suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
    return $suffix[$suffix_key];
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
   $m = (int)date('i', $tm);
   $ms = '';

   $language = SETTINGS_SITE_LANGUAGE;

   if ($language == 'ru') {
       $array = array("час", "часа", "часов");
       $hw = $h.' '.getNumberWord($h,$array);
       if ($m>0) {
           $array = array("минута", "минуты", "минут");
           $ms = $m.' '.getNumberWord($m,$array);
       }
   } elseif ($language == 'en' && $m == 0) {
       $hw = $h.' o\'clock';
   } else {
       $hw = date('H:i',$tm);
   }

   $res = trim($hw . " " . $ms);
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
function addScheduledJob($title, $commands, $datetime, $expire = 1800)
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

       if ($jobs[$i]['COMMANDS'] != '') {
           $url = BASE_URL . '/objects/?job=' . $jobs[$i]['ID'];
           $result = trim(getURL($url, 0));
           $result = preg_replace('/<!--.+-->/is', '', $result);
           if (!preg_match('/OK$/', $result)) {
               //getLogger(__FILE__)->error(sprintf('Error executing job %s (%s): %s', $jobs[$i]['TITLE'], $jobs[$i]['ID'], $result));
               DebMes(sprintf('Error executing job %s (%s): %s', $jobs[$i]['TITLE'], $jobs[$i]['ID'], $result) . ' (' . __FILE__ . ')');
           }
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
function registerEvent($eventName, $details = '', $expire_in = 0)
{
    include_once(DIR_MODULES.'events/events.class.php');
    $events = new events();
    return $events->registerEvent($eventName, $details, $expire_in);
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
            safe_exec('mplayer ' . $filename . " >/dev/null 2>&1", $exclusive, $priority);
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

function runScriptSafe($id, $params = '') {
    $current_call='script.'.$id;
    $call_stack=array();
    if (isset($_GET['m_c_s']) && is_array($_GET['m_c_s'])) {
        $call_stack = $_GET['m_c_s'];
    }
    if (in_array($current_call,$call_stack)) {
        $call_stack[]=$current_call;
        DebMes("Warning: cross-linked call of ".$current_call."\nlog:\n".implode(" -> \n",$call_stack));
        return 0;
    }
    $call_stack[]=$current_call;
    $data=array(
        'script'=>$id,
        'm_c_s'=>$call_stack
    );
    $url=BASE_URL.'/objects/?'.http_build_query($data);
    if (is_array($params)) {
        foreach($params as $k=>$v) {
            $url.='&'.$k.'='.urlencode($v);
        }
    }
    $result = getURLBackground($url,0);
    return $result;
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

function getURLBackground($url, $cache = 0, $username = '', $password = '') {
  getURL($url, $cache, $username, $password, true);
}

/**
 * Summary of getURL
 * @param mixed $url      Url
 * @param mixed $cache    Cache (default 0)
 * @param mixed $username User name (default '')
 * @param mixed $password Password (default '')
 * @return mixed
 */
function getURL($url, $cache = 0, $username = '', $password = '', $background = false)
{
   $filename_part = preg_replace('/\W/is', '_', str_replace('http://', '', $url));
    if (strlen($filename_part)>200) {
        $filename_part=substr($filename_part,0,200).md5($filename_part);
    }
   $cache_file = ROOT . 'cached/urls/' . $filename_part . '.html';
   
   if (!$cache || !is_file($cache_file) || ((time() - filemtime($cache_file)) > $cache))
   {
      try
      {

         //DebMes('Geturl started for '.$url. ' Source: ' .debug_backtrace()[1]['function'], 'geturl');
         $startTime=getmicrotime();

         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0');
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // connection timeout
         curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($ch, CURLOPT_TIMEOUT, 45);  // operation timeout 45 seconds
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

          if ($background) {
              curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
              curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);
          }

         if ($username != '' || $password != '')
         {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
         }

         $url_parsed=parse_url($url);
         $host=$url_parsed['host'];

         $use_proxy=false;
         if (defined('USE_PROXY') && USE_PROXY!='') {
          $use_proxy=true;
         }

         if ($host == '127.0.0.1' || $host == 'localhost') {
          $use_proxy=false;
         }

         if ($use_proxy && defined('HOME_NETWORK') && HOME_NETWORK != '') {
             $p = preg_quote(HOME_NETWORK);
             $p = str_replace('\*', '\d+?', $p);
             $p = str_replace(',', ' ', $p);
             $p = str_replace('  ', ' ', $p);
             $p = str_replace(' ', '|', $p);
             if (preg_match('/' . $p . '/is', $host)) {
              $use_proxy=false;
             }
         }

         if ($use_proxy) {
          curl_setopt($ch, CURLOPT_PROXY, USE_PROXY);
          if (defined('USE_PROXY_AUTH') && USE_PROXY_AUTH!='') {
           curl_setopt($ch, CURLOPT_PROXYUSERPWD, USE_PROXY_AUTH);
          }
         }

         $tmpfname = ROOT . 'cached/cookie.txt';
         curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
         curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);

         $result = curl_exec($ch);


          if (curl_errno($ch) && !$background) {
              $errorInfo = curl_error($ch);
              $info = curl_getinfo($ch);
              $backtrace = debug_backtrace();
              $callSource = $backtrace[1]['function'];
              DebMes("GetURL to $url (source ".$callSource.") finished with error: \n".$errorInfo."\n".json_encode($info));
          }
          curl_close($ch);


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
   $rec['EXCLUSIVE'] = (int)$exclusive;
   $rec['PRIORITY']  = (int)$priority;

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
          if (class_exists('COM')) {
              $WshShell = new COM("WScript.Shell");
              $oExec = $WshShell->Run("cmd /C \"".$cmd."\"", 0, false);
          } else {
              system($cmd);
          }
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

   $e = new \Exception;
   $backtrace=$e->getTraceAsString();

   DebMes("Error registered (type: $code):\n".$details."\nBacktrace:\n".$backtrace,'error');
   $code = trim($code);

   if ($code == 'sql') {
    return 0;
   }

   $error_rec = SQLSelectOne("SELECT * FROM system_errors WHERE CODE LIKE '" . DBSafe($code) . "'");
   
   if (!$error_rec['ID'])
   {
      $error_rec['CODE']         = $code;
      $error_rec['KEEP_HISTORY'] = 1;
      $error_rec['ID']           = SQLInsert('system_errors', $error_rec);
   }

   $error_rec['LATEST_UPDATE'] = date('Y-m-d H:i:s');
   @$error_rec['ACTIVE']        = (int)$error_rec['ACTIVE'] + 1;
   SQLUpdate('system_errors', $error_rec);

   $history_rec = array();
   $history_rec['ERROR_ID'] = $error_rec['ID'];
   $history_rec['COMMENTS'] = $details."\nBacktrace:\n".$backtrace;
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

function verbose_log($data) {
    if (defined('VERBOSE_LOG') && VERBOSE_LOG==1) {
        if (defined('VERBOSE_LOG_IGNORE') && VERBOSE_LOG_IGNORE!='') {
            $tmp=explode(',', VERBOSE_LOG_IGNORE);
            $total=count($tmp);
            for($i=0;$i<$total;$i++) {
                $regex=trim($tmp[$i]);
                if (preg_match('/'.$regex.'/is', $data)) {
                    return;
                }
            }
        }
        global $verbose_thread_id;
        global $argv;
        if (!isset($verbose_thread_id)) {
            $verbose_thread_id = date('H:i:s').'_'.rand(1000,9999);
            $cmd = '';
            if ($_SERVER['REQUEST_URI']!='') {
                $cmd = $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'];
            } elseif ($argv[0]!='') {
                $cmd = implode(' ',$argv);
                $verbose_thread_id.='_'.basename($argv[0]);
            }
            DebMes('th_'.$verbose_thread_id.' start '.$cmd,'verbose');
        }
        $bt = debug_backtrace();
        $total_bt = count($bt);
        $max_bt = 5;
        //$bt = array_reverse($bt);
        $bt = array_slice($bt,1,$max_bt);
        $total = count($bt);
        if ($total>0) {
            $res_trace=array();
            for($i=0;$i<$total;$i++) {
                $res_trace[]=$bt[$i]['function'];
            }
            if ($total_bt>($max_bt+1)) {
                $res_trace[]='...';
            }
            $data = $data . ' ('.implode('<',$res_trace).')';
        }
     DebMes('th_'.$verbose_thread_id.' '.$data,'verbose');
    }    
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

function getPassedText($updatedTime) {
    $passed = time() - $updatedTime;
    $passedText = '';
    if ($passed<10) {
        $passedText = LANG_DEVICES_PASSED_NOW;
    } elseif ($passed<60) {
        $passedText = $passed.' '.LANG_DEVICES_PASSED_SECONDS_AGO;
    } elseif ($passed<60*60) {
        $passedText = round($passed/60).' '.LANG_DEVICES_PASSED_MINUTES_AGO;
    } elseif ($passed<20*60*60) {
        //just time
        $passedText = date('H:i',$updatedTime); 
    } else {
        //time and date
        $passedText=date('d.m.Y H:i',$updatedTime);
    }
    return $passedText;
}