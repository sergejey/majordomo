<?php

function getSystemSerial($force_update = 0) {
    $serial = gg('Serial');
    if (!$serial || $serial == '0' || $force_update) {
        $serial = '';
        if (IsWindowsOS()) {
            $data = exec('vol c:');
            if (preg_match('/[\w]+\-[\w]+/', $data, $m)) {
                $serial = strtolower($m[0]);
            }
        } else {
            $data = trim(exec("cat /proc/cpuinfo | grep Serial | cut -d '':'' -f 2"));
            if ($data == '') {
                $data = trim(exec("sudo cat /proc/cpuinfo | grep Serial | cut -d '':'' -f 2"));
            }
            $serial = ltrim($data, '0');
        }
        if (!$serial) {
            $serial = uniqid('uniq');
        }
        sg('Serial', $serial);
    }
    return $serial;
}

function sayReplySafe($ph, $level = 0, $replyto = '')
{
    $data = array(
        'sayReply' => 1,
        'ph' => $ph,
        'level' => $level,
        'replyto' => $replyto,
    );
    if (session_id()) {
        $data[session_name()] = session_id();
    }
    $url = BASE_URL . '/objects/?' . http_build_query($data);
    if (is_array($params)) {
        foreach ($params as $k => $v) {
            $url .= '&' . $k . '=' . urlencode($v);
        }
    }
    $result = getURLBackground($url, 0);
    return $result;
}

/**
 * Summary of sayReply
 * @param mixed $ph Phrase
 * @param mixed $level Level (default 0)
 * @param mixed $replyto Original request
 * @return void
 */
function sayReply($ph, $level = 0, $replyto = '')
{
    $source = '';
    if ($replyto) {
        $terminal_rec = SQLSelectOne("SELECT * FROM terminals WHERE LATEST_REQUEST LIKE '%" . DBSafe($replyto) . "%' ORDER BY LATEST_REQUEST_TIME DESC LIMIT 1");
        $orig_msg = SQLSelectOne("SELECT * FROM shouts WHERE SOURCE!='' AND MESSAGE LIKE '%" . DBSafe($replyto) . "%' AND ADDED>=(NOW() - INTERVAL 30 SECOND) ORDER BY ADDED DESC LIMIT 1");
        if ($orig_msg['ID']) {
            $source = $orig_msg['SOURCE'];
        }
    } else {
        $terminal_rec = SQLSelectOne("SELECT * FROM terminals WHERE LATEST_REQUEST_TIME>=(NOW() - INTERVAL 5 SECOND) ORDER BY LATEST_REQUEST_TIME DESC LIMIT 1");
    }
    if (!$terminal_rec) {
        $source = 'terminal_not_found';
        say($ph, $level);
    } else {
        $source = 'terminal' . $terminal_rec['ID'];
        $said_status = sayTo($ph, $level, $terminal_rec['NAME']);
        if (!$said_status) {
            say($ph, $level);
        } else {
            //$rec = array();
            //$rec['MESSAGE']   = $ph;
            //$rec['ADDED']     = date('Y-m-d H:i:s');
            //$rec['ROOM_ID']   = 0;
            //$rec['MEMBER_ID'] = 0;
            //if ($level > 0) $rec['IMPORTANCE'] = $level;
            //$rec['ID'] = SQLInsert('shouts', $rec);
        }
    }
    processSubscriptionsSafe('SAYREPLY', array('level' => $level, 'message' => $ph, 'replyto' => $replyto, 'source' => $source));
}


function sayToSafe($ph, $level = 0, $destination = '')
{
    $data = array(
        'sayTo' => 1,
        'ph' => $ph,
        'level' => $level,
        'destination' => $destination,
    );
    if (session_id()) {
        $data[session_name()] = session_id();
    }
    $url = BASE_URL . '/objects/?' . http_build_query($data);
    if (is_array($params)) {
        foreach ($params as $k => $v) {
            $url .= '&' . $k . '=' . urlencode($v);
        }
    }
    $result = getURLBackground($url, 0);
    return $result;
}

/**
 * Summary of sayTo
 * @param mixed $ph Phrase
 * @param mixed $level Level (default 0)
 * @param mixed $destination Destination terminal name
 * @return void
 */
function sayTo($ph, $level = 0, $destination = '')
{
    if (!$destination) {
        return 0;
    }
    // add message to chat
    $rec = array();
    $rec['MESSAGE'] = $ph;
    $rec['ADDED'] = date('Y-m-d H:i:s');
    $rec['ROOM_ID'] = 0;
    $rec['MEMBER_ID'] = 0;
    if ($level > 0) $rec['IMPORTANCE'] = $level;
    $rec['ID'] = SQLInsert('shouts', $rec);

    $processed = processSubscriptionsSafe('SAYTO', array('level' => $level, 'message' => $ph, 'destination' => $destination));
    return 1;
}

function saySafe($ph, $level = 0, $member_id = 0, $source = '')
{
    $data = array(
        'say' => 1,
        'ph' => $ph,
        'level' => $level,
        'member_id' => $member_id,
        'source' => $source,
    );
    if (session_id()) {
        $data[session_name()] = session_id();
    }
    $url = BASE_URL . '/objects/?' . http_build_query($data);
    if (is_array($params)) {
        foreach ($params as $k => $v) {
            $url .= '&' . $k . '=' . urlencode($v);
        }
    }
    $result = getURLBackground($url, 0);
    return $result;
}

/**
 * Summary of say
 * @param mixed $ph Phrase
 * @param mixed $level Level (default 0)
 * @param mixed $member_id Member ID (default 0)
 * @return void
 */
function say($ph, $level = 0, $member_id = 0, $source = '')
{

    //dprint(date('Y-m-d H:i:s')." Say started",false);

    verbose_log("SAY (level: $level; member: $member; source: $source): " . $ph);
    //DebMes("SAY (level: $level; member: $member; source: $source): ".$ph,'say');

    $rec = array();
    $rec['MESSAGE'] = $ph;
    $rec['ADDED'] = date('Y-m-d H:i:s');
    $rec['ROOM_ID'] = 0;
    $rec['MEMBER_ID'] = $member_id;
    $rec['SOURCE'] = $source;

    if ($level > 0) $rec['IMPORTANCE'] = $level;
    $rec['ID'] = SQLInsert('shouts', $rec);

    if ($member_id) {
        $processed = processSubscriptionsSafe('COMMAND', array('level' => $level, 'message' => $ph, 'member_id' => $member_id, 'source' => $source));
        return;
    }

    if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY != '') {
        eval(SETTINGS_HOOK_BEFORE_SAY);
    }


    if ($level >= (int)getGlobal('minMsgLevel') && !$ignoreVoice && !$member_id) {
        if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL == '1') {
            $passed = time() - (int)getGlobal('lastSayTime');
            if ($passed > 20) {
                playSound('dingdong', 1, $level);
            }
        }
    }

    setGlobal('lastSayTime', time());
    setGlobal('lastSayMessage', $ph);

    processSubscriptionsSafe('SAY', array('level' => $level, 'message' => $ph, 'member_id' => $member_id)); //, 'ignoreVoice'=>$ignoreVoice

    if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '') {
        eval(SETTINGS_HOOK_AFTER_SAY);
    }
    //dprint(date('Y-m-d H:i:s')." Say OK",false);

}

function ask($prompt, $target = '')
{
    processSubscriptionsSafe('ASK', array('prompt' => $prompt, 'message' => $prompt, 'target' => $target, 'destination' => $target));
}

/**
 * Summary of processCommand
 * @param mixed $command Command
 * @return void
 */
function processCommand($command)
{
    global $pattern_matched;
    if (!$pattern_matched) {
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

    if (preg_match('/^(\d+):(\d+)$/', $tm, $m)) {
        $hour = $m[1];
        $minute = $m[2];
        $trueTime = mktime($hour, $minute, 0, (int)date('m'), (int)date('d'), (int)date('Y'));
    } elseif (preg_match('/^(\d+)$/', $tm, $m)) {
        $trueTime = $tm;
    }

    return $trueTime;
}


function getNumberWord($number, $suffix)
{
    $keys = array(2, 0, 1, 1, 1, 2);
    $mod = abs($number) % 100;
    $suffix_key = ($mod > 7 && $mod < 20) ? 2 : $keys[min($mod % 10, 5)];
    return $suffix[$suffix_key];
}

/**
 * Summary of timeNow
 * @param mixed $tm time (default 0)
 * @return string
 */
function timeNow($tm = 0)
{
    if (!$tm) {
        $tm = time();
    }

    $h = (int)date('G', $tm);
    $m = (int)date('i', $tm);
    $ms = '';

    if (LANG_HOURS_ARRAY and LANG_MINUTE_ARRAY) {
        $array = explode(',', LANG_HOURS_ARRAY);
        $hw = $h . ' ' . getNumberWord($h, $array);
        if ($m > 0) {
            $array = explode(',', LANG_MINUTE_ARRAY);
            $ms = $m . ' ' . getNumberWord($m, $array);
        }
    } else {
        $hw = date('H:i', $tm);
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

    if ($trueTime1 > $trueTime2) {
        if ($trueTime2 < time()) {
            $trueTime2 += 24 * 60 * 60;
        } else {
            $trueTime1 -= 24 * 60 * 60;
        }
    }

    if ((time() >= $trueTime1) && (time() <= $trueTime2))
        return true;

    return false;
}

/**
 * Summary of addScheduledJob
 * @param mixed $title Title
 * @param mixed $commands Commands
 * @param mixed $datetime Date
 * @param mixed $expire Expire time (default 60)
 * @return mixed
 */
function addScheduledJob($title, $commands, $datetime, $expire = 1800)
{
    clearScheduledJob($title);
    $rec = array();
    $rec['TITLE'] = $title;
    $rec['COMMANDS'] = $commands;
    $rec['RUNTIME'] = date('Y-m-d H:i:s', $datetime);
    $rec['EXPIRE'] = date('Y-m-d H:i:s', $datetime + $expire);
    $rec['ID'] = SQLInsert('jobs', $rec);

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
 * @param mixed $title Title
 * @param mixed $commands Commands
 * @param mixed $timeout Timeout
 * @return mixed
 */
function setTimeOut($title, $commands, $timeout)
{
    startMeasure('setTimeout');
    clearTimeOut($title);
    $res = addScheduledJob($title, $commands, time() + $timeout);
    endMeasure('setTimeout');
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
    $job = SQLSelectOne("SELECT ID FROM jobs WHERE PROCESSED = 0 AND TITLE = '" . DBSafe($title) . "'");
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

    $jobs = SQLSelect($sqlQuery);
    $total = count($jobs);

    for ($i = 0; $i < $total; $i++) {
        //echo "Running job: " . $jobs[$i]['TITLE'] . "\n";
        $jobs[$i]['PROCESSED'] = 1;
        $jobs[$i]['STARTED'] = date('Y-m-d H:i:s');

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
 * @param mixed $text Text
 * @param mixed $newText New text
 * @return array|double|int
 */
function recognizeTime($text, &$newText)
{
    $result = 0;
    $found = 0;
    $new_time = time();
    $text = ($text);

    if (preg_match('/через (\d+) '.LANG_PATTERN_SECOND.'.?/isu', $text, $m)) {
        $new_time = time() + $m[1];
        $newText = trim(str_replace($m[0], '', $text));
        $found = 1;
    } elseif (preg_match('/через (\d+) '.LANG_PATTERN_MINUTE.'.?/isu', $text, $m)) {
        $new_time = time() + $m[1] * 60;
        $newText = trim(str_replace($m[0], '', $text));
        $found = 1;
    } elseif (preg_match('/через (\d+) '.LANG_PATTERN_HOUR.'.?/isu', $text, $m)) {
        $new_time = time() + $m[1] * 60 * 60;
        $newText = trim(str_replace($m[0], '', $text));
        $found = 1;
    } elseif (preg_match('/в (\d+):(\d+)/isu', $text, $m)) {
        $new_time = mktime($m[1], $m[2], 0, (int)date('m'), (int)date('d'), (int)date('Y'));
        $newText = trim(str_replace($m[0], '', $text));
        $found = 1;
    }

    $newText = ($newText);

    if ($found) {
        $result = $new_time;
    }

    return $result;
}


/**
 * Summary of registerEvent
 * @param mixed $eventName Event name
 * @param mixed $details Details (default '')
 * @param mixed $expire_in Expire time (default 365)
 * @return mixed
 */
function registerEvent($eventName, $details = '', $expire_in = 0)
{
    include_once(DIR_MODULES . 'events/events.class.php');
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
    $sqlQuery = "SELECT ID, UNIX_TIMESTAMP(ADDED) AS TM
                  FROM events
                 WHERE EVENT_TYPE = 'system'
                   AND EVENT_NAME = '" . DBSafe($eventName) . "'
                 ORDER BY ADDED DESC
                 LIMIT 1";

    $rec = SQLSelectOne($sqlQuery);

    if ($rec['ID']) {
        return $rec['TM'];
    } else {
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
    if (file_exists(ROOT . 'cms/texts/' . $filename . '.txt')) {
        $filename = ROOT . 'cms/texts/' . $filename . '.txt';
    }

    if (file_exists($filename)) {
        $data = LoadFile($filename);
        $data = str_replace("\r", '', $data);
        $data = str_replace("\n\n", "\n", $data);
        $lines = mb_split("\n", $data);
        $total = count($lines);
        $line = $lines[round(rand(0, $total - 1))];

        if ($line != '') {
            return $line;
        }
    }
}

/**
 * Summary of playSound
 * @param mixed $filename File name
 * @param mixed $exclusive Exclusive (default 0)
 * @param mixed $priority Priority (default 0)
 * @return void
 */
function playSound($filename, $exclusive = 0, $priority = 0)
{
    global $ignoreSound;

    if (file_exists(ROOT . 'cms/sounds/' . $filename . '.mp3'))
        $filename = ROOT . 'cms/sounds/' . $filename . '.mp3';
    elseif (file_exists(ROOT . 'cms/sounds/' . $filename))
        $filename = ROOT . 'cms/sounds/' . $filename;

    if (defined('SETTINGS_HOOK_BEFORE_PLAYSOUND') && SETTINGS_HOOK_BEFORE_PLAYSOUND != '')
        eval(SETTINGS_HOOK_BEFORE_PLAYSOUND);

    if (!$ignoreSound) {
        if (file_exists($filename)) {
            if (IsWindowsOS())
                safe_exec(DOC_ROOT . '/rc/madplay.exe ' . $filename, $exclusive, $priority);
            else {
                if (defined('AUDIO_PLAYER') && AUDIO_PLAYER!='') {
                    $audio_player = AUDIO_PLAYER;
                } else {
                    $audio_player = 'mplayer';
                }
                safe_exec($audio_player.' ' . $filename . " >/dev/null 2>&1", $exclusive, $priority);
            }

        }
    }

    if (defined('SETTINGS_HOOK_AFTER_PLAYSOUND') && SETTINGS_HOOK_AFTER_PLAYSOUND != '')
        eval(SETTINGS_HOOK_AFTER_PLAYSOUND);
}

/**
 * Summary of runScript
 * @param mixed $id ID
 * @param mixed $params Params (default '')
 * @return mixed
 */
function runScript($id, $params = '')
{
    include_once(DIR_MODULES . 'scripts/scripts.class.php');
    $sc = new scripts();
    return $sc->runScript($id, $params);
}

function runScriptSafe($id, $params = '')
{
    $current_call = 'script.' . $id;
    $call_stack = array();
    if (isset($_GET['m_c_s']) && is_array($_GET['m_c_s'])) {
        $call_stack = $_GET['m_c_s'];
    }
    if (in_array($current_call, $call_stack)) {
        $call_stack[] = $current_call;
        DebMes("Warning: cross-linked call of " . $current_call . "\nlog:\n" . implode(" -> \n", $call_stack));
        return 0;
    }
    $call_stack[] = $current_call;
    $data = array(
        'script' => $id,
        'm_c_s' => $call_stack
    );
    if (session_id()) {
        $data[session_name()] = session_id();
    }
    $url = BASE_URL . '/objects/?' . http_build_query($data);
    if (is_array($params)) {
        foreach ($params as $k => $v) {
            $url .= '&' . $k . '=' . urlencode($v);
        }
    }
    $result = getURLBackground($url, 0);
    return $result;
}

/**
 * Summary of callScript
 * @param mixed $id ID
 * @param mixed $params Params (default '')
 * @return void
 */
function callScript($id, $params = '')
{
    runScript($id, $params);
}

function getURLBackground($url, $cache = 0, $username = '', $password = '')
{
    //DebMes("URL: ".$url,'debug1');
    getURL($url, $cache, $username, $password, true);
    return true;
}

/**
 * Summary of getURL
 * @param mixed $url Url
 * @param mixed $cache Cache (default 0)
 * @param mixed $username User name (default '')
 * @param mixed $password Password (default '')
 * @return mixed
 */
function getURL($url, $cache = 0, $username = '', $password = '', $background = false)
{
    startMeasure('getURL');
    // DebMes($url,'urls');
    $filename_part = preg_replace('/\W/is', '_', str_replace('http://', '', $url));
    if (strlen($filename_part) > 200) {
        $filename_part = substr($filename_part, 0, 200) . md5($filename_part);
    }
    $cache_file = ROOT . 'cms/cached/urls/' . $filename_part . '.html';

    if (!$cache || !is_file($cache_file) || ((time() - filemtime($cache_file)) > $cache)) {
        try {

            //DebMes('Geturl started for '.$url. ' Source: ' .debug_backtrace()[1]['function'], 'geturl');
            startMeasure('curl_prepare');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // connection timeout
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);  // operation timeout 45 seconds
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            if ($background) {
                curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 50);
            }

            if ($username != '' || $password != '') {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            }

            $url_parsed = parse_url($url);
            $host = $url_parsed['host'];

            $use_proxy = false;
            if (defined('USE_PROXY') && USE_PROXY != '') {
                $use_proxy = true;
            }

            if ($host == '127.0.0.1' || $host == 'localhost') {
                $use_proxy = false;
            }

            if ($use_proxy && defined('HOME_NETWORK') && HOME_NETWORK != '') {
                $p = preg_quote(HOME_NETWORK);
                $p = str_replace('\*', '\d+?', $p);
                $p = str_replace(',', ' ', $p);
                $p = str_replace('  ', ' ', $p);
                $p = str_replace(' ', '|', $p);
                if (preg_match('/' . $p . '/is', $host)) {
                    $use_proxy = false;
                }
            }

            if ($use_proxy) {
                curl_setopt($ch, CURLOPT_PROXY, USE_PROXY);
                if (defined('USE_PROXY_AUTH') && USE_PROXY_AUTH != '') {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, USE_PROXY_AUTH);
                }
            }

            $tmpfname = ROOT . 'cms/cached/cookie.txt';
            curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);

            endMeasure('curl_prepare');
            startMeasure('curl_exec');
            $result = curl_exec($ch);
            endMeasure('curl_exec');


            startMeasure('curl_post');
            if (!$background && curl_errno($ch)) {
                $errorInfo = curl_error($ch);
                $info = curl_getinfo($ch);
                $backtrace = debug_backtrace();
                $callSource = $backtrace[1]['function'];
                DebMes("GetURL to $url (source " . $callSource . ") finished with error: \n" . $errorInfo . "\n" . json_encode($info),'geturl_error');
            }
            curl_close($ch);
            endMeasure('curl_post');


        } catch (Exception $e) {
            registerError('geturl', $url . ' ' . get_class($e) . ', ' . $e->getMessage());
        }

        if ($cache > 0) {
            CreateDir(ROOT . 'cms/cached/urls');
            SaveFile($cache_file, $result);
        }
    } else {
        $result = LoadFile($cache_file);
    }


    endMeasure('getURL');

    return $result;
}

/**
 * Summary of safe_exec
 * @param mixed $command Command
 * @param mixed $exclusive Exclusive (default 0)
 * @param mixed $priority Priority (default 0)
 * @param mixed $on_complete On_Complete code (default '')
 * @return mixed
 */
function safe_exec($command, $exclusive = 0, $priority = 0, $on_complete = '')
{
    $rec = array();

    $rec['ADDED'] = date('Y-m-d H:i:s');
    $rec['COMMAND'] = $command;
    $rec['EXCLUSIVE'] = (int)$exclusive;
    $rec['PRIORITY'] = (int)$priority;
    $rec['ON_COMPLETE'] = $on_complete;

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
    if (IsWindowsOS()) {
        //pclose(popen("start /B ". $cmd, "r"));
        try {
            //pclose(popen("start /B ". $cmd, "r"));
            if (class_exists('COM')) {
                $WshShell = new COM("WScript.Shell");
                $oExec = $WshShell->Run("cmd /C \"" . $cmd . "\"", 0, false);
            } else {
                system($cmd);
            }
        } catch (Exception $e) {
            DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
        }
    } else {
        exec($cmd . " > /dev/null &");
    }
}

/**
 * Summary of getFilesTree
 * @param mixed $destination Destination
 * @param mixed $sort Sort (default 'name')
 * @return array
 */
function getFilesTree($destination, $sort = 'name')
{
    if (substr($destination, -1) == '/' || substr($destination, -1) == '\\') {
        $destination = substr($destination, 0, strlen($destination) - 1);
    }

    $res = array();

    if (!is_dir($destination))
        return $res;

    if ($dir = @opendir($destination)) {
        while (($file = readdir($dir)) !== false) {
            if (is_dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                $tmp = getFilesTree($destination . "/" . $file);
                if (is_array($tmp)) {
                    foreach ($tmp as $elem) {
                        $res[] = $elem;
                    }
                }
            } elseif (is_file($destination . "/" . $file)) {
                $res[] = ($destination . "/" . $file);
            }
        }
        closedir($dir);
    }

    if ($sort == 'name') {
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
                 WHERE HOSTNAME = '" . DBSafe($host) . "'
                    OR TITLE =    '" . DBSafe($host) . "'";

    $rec = SQLSelectOne($sqlQuery);
    if (!$rec['STATUS'] || $rec['STATUS'] == 2) {
        return 0;
    } else {
        return 1;
    }
}

/**
 * Summary of checkAccess
 * @param mixed $object_type Object type
 * @param mixed $object_id Object ID
 * @return bool
 */
function checkAccess($object_type, $object_id)
{

    global $access_rules_cached;

    startMeasure('checkAccess');

    if (!isset($access_rules_cached)) {
        $all_rules=SQLSelect("SELECT OBJECT_TYPE, OBJECT_ID FROM security_rules");
        foreach($all_rules as $rule) {
            $access_rules_cached[$rule['OBJECT_TYPE'].$rule['OBJECT_ID']]=1;
        }
    }

    if (!isset($access_rules_cached[$object_type.$object_id])) {
        endMeasure('checkAccess');
        return true;
    }

    include_once(DIR_MODULES . 'security_rules/security_rules.class.php');
    $sc = new security_rules();
    $result = $sc->checkAccess($object_type, $object_id);
    endMeasure('checkAccess');
    return $result;
}

/**
 * Summary of registerError
 * @param mixed $code Code (default 'custom')
 * @param mixed $details Details (default '')
 * @return void
 */
function registerError($code = 'custom', $details = '')
{

    $e = new \Exception;
    $backtrace = $e->getTraceAsString();

    DebMes("Error registered (type: $code):\n" . $details . "\nBacktrace:\n" . $backtrace, 'error');
    $code = trim($code);

    if ($code == 'sql') {
        return 0;
    }

    $error_rec = SQLSelectOne("SELECT * FROM system_errors WHERE CODE = '" . DBSafe($code) . "'");

    if (!$error_rec['ID']) {
        $error_rec['CODE'] = $code;
        $error_rec['KEEP_HISTORY'] = 1;
        $error_rec['ID'] = SQLInsert('system_errors', $error_rec);
    }

    $error_rec['LATEST_UPDATE'] = date('Y-m-d H:i:s');
    @$error_rec['ACTIVE'] = (int)$error_rec['ACTIVE'] + 1;
    SQLUpdate('system_errors', $error_rec);

    $history_rec = array();
    $history_rec['ERROR_ID'] = $error_rec['ID'];
    $history_rec['COMMENTS'] = $details . "\nBacktrace:\n" . $backtrace;
    $history_rec['ADDED'] = $error_rec['LATEST_UPDATE'];

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

    if (!$error_rec['KEEP_HISTORY']) {
        SQLExec("DELETE FROM system_errors_data WHERE ERROR_ID=" . (int)$error_rec['ID'] . " AND ID != '" . $history_rec['ID'] . "'");
    } else {
        $tmp = SQLSelect("SELECT ID FROM system_errors_data WHERE ERROR_ID=" . (int)$error_rec['ID'] . " ORDER BY ID DESC LIMIT 50");
        if ($tmp[0]['ID'] && count($tmp) == 50) {
            $tmp = array_reverse($tmp);
            SQLExec("DELETE FROM system_errors_data WHERE ERROR_ID=" . (int)$error_rec['ID'] . " AND ID<" . $tmp[0]['ID']);
        }

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
    foreach ($data as $v) {
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
    $res = array();
    $bufLength = strlen($buf) - 1;

    for ($i = 0; $i < $bufLength; $i += 2) {
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
    $res = '';
    $bufLength = strlen($buf) - 1;
    for ($i = 0; $i < $bufLength; $i += 2) {
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
    $res = '';
    $bufLength = strlen($buf);

    for ($i = 0; $i < $bufLength; $i++) {
        $num = dechex(ord($buf[$i]));
        if (strlen($num) == 1) {
            $num = '0' . $num;
        }

        $res .= $num;
    }

    return $res;
}

function verbose_log($data)
{
    if (defined('VERBOSE_LOG') && VERBOSE_LOG == 1) {
        if (defined('VERBOSE_LOG_IGNORE') && VERBOSE_LOG_IGNORE != '') {
            $tmp = explode(',', VERBOSE_LOG_IGNORE);
            $total = count($tmp);
            for ($i = 0; $i < $total; $i++) {
                $regex = trim($tmp[$i]);
                if (preg_match('/' . $regex . '/is', $data)) {
                    return;
                }
            }
        }
        global $verbose_thread_id;
        global $argv;
        if (!isset($verbose_thread_id)) {
            $verbose_thread_id = date('H:i:s') . '_' . rand(1000, 9999);
            $cmd = '';
            if ($_SERVER['REQUEST_URI'] != '') {
                $cmd = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
            } elseif ($argv[0] != '') {
                $cmd = implode(' ', $argv);
                $verbose_thread_id .= '_' . basename($argv[0]);
            }
            DebMes('th_' . $verbose_thread_id . ' start ' . $cmd, 'verbose');
        }
        $bt = debug_backtrace();
        $total_bt = count($bt);
        $max_bt = 5;
        //$bt = array_reverse($bt);
        $bt = array_slice($bt, 1, $max_bt);
        $total = count($bt);
        if ($total > 0) {
            $res_trace = array();
            for ($i = 0; $i < $total; $i++) {
                $res_trace[] = $bt[$i]['function'];
            }
            if ($total_bt > ($max_bt + 1)) {
                $res_trace[] = '...';
            }
            $data = $data . ' (' . implode('<', $res_trace) . ')';
        }
        DebMes('th_' . $verbose_thread_id . ' ' . $data, 'verbose');
    }
}

/**
 * Title
 * @return string
 */
function return_memory_usage()
{
    $size = memory_get_usage(true);
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function getPassedText($updatedTime)
{
    $passed = time() - $updatedTime;
    $passedText = '';
    if ($passed < 10) {
        $passedText = LANG_DEVICES_PASSED_NOW;
    } elseif ($passed < 60) {
        $passedText = $passed . ' ' . LANG_DEVICES_PASSED_SECONDS_AGO;
    } elseif ($passed < 60 * 60) {
        $passedText = round($passed / 60) . ' ' . LANG_DEVICES_PASSED_MINUTES_AGO;
    } elseif ($passed < 20 * 60 * 60) {
        //just time
        $passedText = date('H:i', $updatedTime);
    } else {
        //time and date
        $passedText = date('d.m.Y H:i', $updatedTime);
    }
    return $passedText;
}

/**
 * Encode/Decode a string for safe transfer to a URL
 * @param mixed $string String
 * @return string
 */
function urlsafe_b64encode($string)
{
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    $data = urlencode($data);
    return $data;
}

function urlsafe_b64decode($string)
{
    $data = urldecode($data);
    $data = str_replace(array('-', '_'), array('+', '/'), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

function rgbToHsv($r, $g, $b)
{
    $r /= 255;
    $g /= 255;
    $b /= 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $h;
    $s;
    $v;
    $d = $max - $min;
    if ($max == $min) $h = 0;
    else {
        if ($max == $r) {
            $h = 60 * (($g - $b) / $d);
            if ($g < $b) $h += 360;
        } elseif ($max == $g) {
            $h = 60 * (($b - $r) / $d) + 120;
        } elseif ($max == $b) {
            $h = 60 * (($r - $g) / $d) + 240;
        }
    }
    if ($max == 0) $s = 0;
    else $s = 1 - ($min / $max);
    $v = $max;
    return array(round($h, 2), round($s, 2), round($v, 2));
}

function hsvToRgb($h, $s, $v)
{
    $r;
    $g;
    $b;
    $h = intval($h);
    if ($h < 0) $h = 0;
    if ($h > 360) $h = 360;

    $Vmin = ((100 - $s) * $v) / 100;
    $a = ($v - $Vmin) * (($h % 60) / 60);
    $Vinc = $Vmin + $a;
    $Vdec = $v - $a;
    $Hi = intval($h / 60) % 6;
    if ($Hi == 0) {
        $r = $v;
        $g = $Vinc;
        $b = $Vmin;
    } elseif ($Hi == 1) {
        $r = $Vdec;
        $g = $v;
        $b = $Vmin;
    } elseif ($Hi == 2) {
        $r = $Vmin;
        $g = $v;
        $b = $Vinc;
    } elseif ($Hi == 3) {
        $r = $Vmin;
        $g = $Vdec;
        $b = $v;
    } elseif ($Hi == 4) {
        $r = $Vinc;
        $g = $Vmin;
        $b = $v;
    } elseif ($Hi == 5) {
        $r = $v;
        $g = $Vmin;
        $b = $Vdec;
    }

    $r = intval(($r * 255) / 100);
    $g = intval(($g * 255) / 100);
    $b = intval(($b * 255) / 100);

    return array($r, $g, $b);
}

function hexToHsv($hex)
{
    $hex = str_replace('#', '', $hex);
    $length = strlen($hex);
    $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
    $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
    $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
    return rgbToHsv($rgb['r'], $rgb['g'], $rgb['b']);
}

function hsvToHex($h, $s, $v)
{
    $rgb = hsvToRgb($h, $s, $v);
    return sprintf("%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
}

function remote_file_exists($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if( $httpCode == 200 ){return true;}
    return false;
}

function logAction($action_type,$details='') {
    global $session;
    $rec=array();
    $rec['ADDED']=date('Y-m-d H:i:s');
    if ($session->data['SITE_USERNAME']) {
        $rec['USER']=$session->data['SITE_USERNAME'];
    } elseif (preg_match('/^\/admin\.php/',$_SERVER['REQUEST_URI'])) {
        $rec['USER']='Control Panel';
    }
    if ($session->data['TERMINAL']) {
        $rec['TERMINAL']=$session->data['TERMINAL'];
    } else {
        $rec['TERMINAL']='';
    }
    $rec['ACTION_TYPE']=$action_type;
    $rec['TITLE']=$details;
    $rec['TITLE']=mb_substr($rec['TITLE'],0,250,'utf-8');
    $rec['IP']=$_SERVER['REMOTE_ADDR'];
    SQLInsert('actions_log',$rec);

}

function get_media_info($file)
{
    if (!defined('PATH_TO_FFMPEG')) {
        if (IsWindowsOS()) {
            define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
        } else {
            define("PATH_TO_FFMPEG", 'ffmpeg');
        }
    }
    $data = shell_exec(PATH_TO_FFMPEG . " -i " . $file . " 2>&1");
    //DebMes ($data);
    if (preg_match("/: Invalid /", $data)) {
        return false;
    }
    //get duration
    preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $data, $duration);

    if (!isset($duration[1])) {
        return false;
    }
    $hours = $duration[1];
    $minutes = $duration[2];
    $seconds = $duration[3]+1;
	$out['duration'] = $seconds + ($minutes * 60) + ($hours * 60 * 60);
	// get all info about codec
	preg_match("/Audio: (.+), (.\d+) Hz, (.\w+), (.+), (.\d+) kb/", $data, $format);
    
	if ($format) {
		$out['Audio_format'] = $format[1];
		$out['Audio_sample_rate'] = $format[2];
		$out['Audio_type'] = $format[3];
		$out['Audio_codec'] = $format[4];
		$out['Audio_bitrate'] = $format[5];
		if ($out['Audio_type'] == 'mono' ) {
			$out['Audio_chanel'] = 1;
		} else {
			$out['Audio_chanel'] = 2;
		}	
	}
    preg_match("/Video: (.+),\s(.\d+x.\d+) (.+), (.+), (.+), (.+), (.+), (.+) /", $data, $formatv);
    if ($formatv) {
		$out['Video_format'] = $formatv[1];
	    $out['Video_size'] = $formatv[2];
	    $out['Video_bitrate'] = str_ireplace("kb/s", "", $formatv[4]);
	    $out['Video_fps'] = $formatv[5];
	}
    return $out;
}

function get_remote_filesize($url)
{
    $head = array_change_key_case(get_headers($url, 1));
    // content-length of download (in bytes), read from Content-Length: field
    $clen = isset($head['content-length']) ? $head['content-length'] : 0;

    // cannot retrieve file size, return "-1"
    if (!$clen) {
        return '0';
    }
    return $clen; // return size in bytes
}
