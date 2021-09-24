<?php

// say to all terminal where User = $user 
function sayToUser($ph, $level = 0, $user = '')
{
    if (!$user) return false;

    // replace enter simbol
    $ph = str_replace(array("\r\n", "\r", "\n"), '', $ph);
    if (!$level) $level=0;

    // get room ID
    $users = SQLSelectOne("SELECT * FROM `users` WHERE `USERNAME` LIKE '" . $user. "' OR `NAME` LIKE '" . $user. "'");
    // add message to chat
    $rec               = array();
    $rec['MESSAGE']    = $ph;
    $rec['ADDED']      = date('Y-m-d H:i:s');
    $rec['ROOM_ID']    = 0;
    $rec['MEMBER_ID']  = $users['ID'];
    $rec['IMPORTANCE'] = $level;
    
    $terminals = array();
    if (!$users OR !$terminals = getTerminalsByUser($user)) return false;
	
    foreach ($terminals as $terminal) {
        // если пустая инфа о терминале пропускаем
        if (!$terminal OR !$terminal['TTS_IS_ONLINE']) {
            DebMes("No information of terminal" . $terminal['NAME'], 'terminals');
            continue;
        }
        if ($terminal['USE_SYSTEM_MML']) {
            if ( $rec['IMPORTANCE'] >= getGlobal('minMsgLevel')) {
                if ($terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } else  {
            if ($rec['IMPORTANCE'] >= $terminal['MIN_MSG_LEVEL']) {
                if ( $terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } 
    }
    
    $rec['EVENT'] = 'SAYTO';
    $rec['ID'] = SQLInsert('shouts', $rec);

    return true;
}

// say to all terminal where location = $destination 
function sayToLocation($ph, $level = 0, $destination = '')
{
    if (!$destination) return false;
    
    // replace enter simbol
    $ph = str_replace(array("\r\n", "\r", "\n"), '', $ph);
    if (!$level) $level=0;
    
    // get room ID
    $location = SQLSelectOne("SELECT * FROM `locations` WHERE `TITLE` LIKE '" . $destination. "'");

    // add message to chat
    $rec               = array();
    $rec['MESSAGE']    = $ph;
    $rec['ADDED']      = date('Y-m-d H:i:s');
    $rec['ROOM_ID']    = $location['ID'];
    $rec['MEMBER_ID']  = 0;
    $rec['IMPORTANCE'] = $level;
   
    if (!$location OR !$terminals = getTerminalsByLocationId($location['ID'])) return false;

    foreach ($terminals as $terminal) {
        // если пустая инфа о терминале пропускаем
        if (!$terminal OR !$terminal['TTS_IS_ONLINE']) {
            DebMes("No information of terminal" . $terminal['NAME'], 'terminals');
            continue;
        }
        if ($terminal['USE_SYSTEM_MML']) {
            if ( $rec['IMPORTANCE'] >= getGlobal('minMsgLevel')) {
                if ($terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } else  {
            if ($rec['IMPORTANCE'] >= $terminal['MIN_MSG_LEVEL']) {
                if ( $terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } 
    }
    
    $rec['EVENT'] = 'SAYTO';
    $rec['ID'] = SQLInsert('shouts', $rec);

    return true;
}

function sayReplySafe($ph, $level = 0, $replyto = '')
{
    $data = array(
        'sayReply' => 1,
        'ph' => $ph,
        'level' => $level,
        'replyto' => $replyto
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
    // replace enter simbol
    $ph = str_replace(array("\r\n", "\r", "\n"), '', $ph);
    if ($replyto) {
        $terminal_rec = SQLSelectOne("SELECT * FROM terminals WHERE LATEST_REQUEST LIKE '%" . DBSafe($replyto) . "%' ORDER BY LATEST_REQUEST_TIME DESC LIMIT 1");
        $orig_msg     = SQLSelectOne("SELECT * FROM shouts WHERE SOURCE!='' AND MESSAGE LIKE '%" . DBSafe($replyto) . "%' AND ADDED>=(NOW() - INTERVAL 30 SECOND) ORDER BY ADDED DESC LIMIT 1");
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
        $source = 'terminal - ' . $terminal_rec['ID'];
        sayTo($ph, $level, $terminal_rec['NAME']);
    }
    // запуск этой функции уже будет в функции САЙ или САЙТУ - небходимо исключить этот вариант из возможности подписки
    //processSubscriptionsSafe('SAYREPLY', array('level' => $level, 'message' => $ph, 'replyto' => $replyto, 'source' => $source));
}


function sayToSafe($ph, $level = 0, $destination = '')
{
    $data = array(
        'sayTo' => 1,
        'ph' => $ph,
        'level' => $level,
        'destination' => $destination
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
    if (!$destination) say($ph, $level);
    
    // replace enter simbol
    $ph = str_replace(array("\r\n", "\r", "\n"), '', $ph);
    if (!$level) $level=0;
    
    // add message to chat
    $rec               = array();
    $rec['MESSAGE']    = $ph;
    $rec['ADDED']      = date('Y-m-d H:i:s');
    $rec['ROOM_ID']    = 0;
    $rec['MEMBER_ID']  = 0;
    $rec['IMPORTANCE'] = $level;
    
    $terminals = array();
    if ($destination) {
        if (!$terminals = getTerminalsByName($destination, 1)) {
            $terminals = getTerminalsByHost($destination, 1);
        }
    } else {
        $terminals = getTerminalsByCANTTS();
    }

    foreach ($terminals as $terminal) {
        // если пустая инфа о терминале пропускаем
        if (!$terminal OR !$terminal['TTS_IS_ONLINE']) {
            DebMes("No information of terminal " . $terminal['NAME'], 'terminals');
            continue;
        }
        if ($terminal['USE_SYSTEM_MML']) {
            if ( $rec['IMPORTANCE'] >= getGlobal('minMsgLevel')) {
                if ($terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } else  {
            if ($rec['IMPORTANCE'] >= $terminal['MIN_MSG_LEVEL']) {
                if ( $terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } 
    }
    
    $rec['EVENT'] = 'SAYTO';
    $rec['ID'] = SQLInsert('shouts', $rec);

    return true;
}

function saySafe($ph, $level = 0, $member_id = 0, $source = '')
{
    $data = array(
        'say' => 1,
        'ph' => $ph,
        'level' => $level,
        'member_id' => $member_id,
        'source' => $source
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
    // replace enter simbol
    $ph = str_replace(array("\r\n", "\r", "\n"), '', $ph);
    if (!$level) $level=0;
    
    verbose_log("SAY (level: $level; member: $member; source: $source): " . $ph);
    //DebMes("SAY (level: $level; member: $member; source: $source): ".$ph,'say');
    
    $rec               = array();
    $rec['MESSAGE']    = $ph;
    $rec['ADDED']      = date('Y-m-d H:i:s');
    $rec['ROOM_ID']    = 0;
    $rec['MEMBER_ID']  = $member_id;
    $rec['SOURCE']     = $source;
    $rec['IMPORTANCE'] = $level;
    $rec['EVENT'] = 'SAY';
    $rec['ID']    = SQLInsert('shouts', $rec);
    
    // если это не комманда из чата - то запускаем на все модуля подписанные на процесс COMMAND
    if ($member_id) {
        $processed = processSubscriptionsSafe('COMMAND', array(
            'level' => $level,
            'message' => $ph,
            'member_id' => $member_id,
            'source' => $source
        ));
        return false;
    }
   
    if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY != '') {
        eval(SETTINGS_HOOK_BEFORE_SAY);
    }
    
    
    // добавляем список терминалов на которые надо говорить - если это не комманда из чата
    $terminals = array();
    $terminals = getTerminalsByCANTTS();
    
    foreach ($terminals as $terminal) {
        // если пустая инфа о терминале пропускаем
        if (!$terminal OR !$terminal['TTS_IS_ONLINE']) {
            DebMes("No information of terminal " . $terminal['NAME'], 'terminals');
            continue;
        }
        if ($terminal['USE_SYSTEM_MML']) {
            if ( $rec['IMPORTANCE'] >= getGlobal('minMsgLevel')) {
                if ($terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } else  {
            if ($rec['IMPORTANCE'] >= $terminal['MIN_MSG_LEVEL']) {
                if ( $terminal['CANTTS'] AND $terminal['TTS_TYPE'] AND $terminal['LINKED_OBJECT'] ) {
                    $rec['SOURCE'] .= $terminal['ID'] . '^';
                }
            }
        } 
    }

    SQLUpdate('shouts', $rec);
    
    setGlobal('lastSayTime', time());
    setGlobal('lastSayMessage', $ph);
    
    
    if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '') {
        eval(SETTINGS_HOOK_AFTER_SAY);
    }
    //dprint(date('Y-m-d H:i:s')." Say OK",false);
    return true;
}

function ask($ph, $destination = '')
{
	if (!$destination) {
        return 0;
    }
   if ($destination) {
        if (!$terminals = getTerminalsByName($destination, 1)) {
            $terminals = getTerminalsByHost($destination, 1);
        }
    }
    foreach ($terminals as $terminal) {
        if (!$terminal['IS_ONLINE']) {
            pingTerminalSafe($terminal['NAME'], $terminal);
        } 
		processSubscriptionsSafe('ASK', array('message' => $ph, 'destination' => $terminal));
	    DebMes("Make Message - " . $ph . " with EVENT ASK ", 'terminals');
    }

}

