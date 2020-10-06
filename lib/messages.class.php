<?php

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


    if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL == '1') {
        if ($level >= (int)getGlobal('minMsgLevel') && !$member_id) { // && !$ignoreVoice
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


