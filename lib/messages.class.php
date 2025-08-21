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
    if (strlen($rec['MESSAGE']) > 255) $rec['MESSAGE'] = substr($rec['MESSAGE'], 0, 255);
    $rec['ADDED'] = date('Y-m-d H:i:s');
    $rec['ROOM_ID'] = 0;
    $rec['MEMBER_ID'] = 0;
    if ($level > 0) $rec['IMPORTANCE'] = $level;
    $rec['ID'] = SQLInsert('shouts', $rec);

    $processed = processSubscriptionsSafe('SAYTO', array('id' => $rec['ID'], 'level' => $level, 'message' => $ph, 'destination' => $destination));
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
function say($ph, $level = 0, $member_id = 0, $source = '', $sound_name = 'dingdong')
{

    //dprint(date('Y-m-d H:i:s')." Say started",false);

    verbose_log("SAY (level: $level; member: $member_id; source: $source): " . $ph);
    //DebMes("SAY (level: $level; member: $member; source: $source): ".$ph,'say');

    $image = '';
    if (preg_match('/image:([\w\d\_\/\-\.]+)/is', $ph, $m)) {
        if (file_exists($m[1])) {
            $image = $m[1];
        }
        $ph = str_replace($m[0], '', $ph);
        $ph = preg_replace('/\n+$/', '', $ph);
    }

    $rec = array();
    if (mb_strlen($ph) > 255) $ph = mb_substr($ph, 0, 255);
    $rec['MESSAGE'] = $ph;
    $rec['IMAGE'] = $image;
    $rec['ADDED'] = date('Y-m-d H:i:s');
    $rec['ROOM_ID'] = 0;
    $rec['MEMBER_ID'] = $member_id;
    $rec['SOURCE'] = $source;

    if ($level > 0) $rec['IMPORTANCE'] = $level;
    $rec['ID'] = SQLInsert('shouts', $rec);

    if ($member_id) {
        $processed = processSubscriptionsSafe('COMMAND', array('id' => $rec['ID'], 'level' => $level, 'message' => $ph, 'member_id' => $member_id, 'source' => $source, 'image' => $image));
        return;
    }

    $last_say_time = getGlobal('lastSayTime');
    $last_say_message = getGlobal('lastSayMessage');

    setGlobal('lastSayTime', time());
    setGlobal('lastSayMessage', $ph);


    if ($last_say_time != time() || $last_say_message != $ph) {

        if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY != '') {
            setEvalCode(SETTINGS_HOOK_BEFORE_SAY);
            eval(SETTINGS_HOOK_BEFORE_SAY);
            setEvalCode();
        }


        if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL == '1' ) {
            if ($level >= (int)getGlobal('minMsgLevel') && !$member_id) { // && !$ignoreVoice
                $passed = time() - $last_say_time;
                if ($passed > 20) {
                    if ($sound_name != 'none') {
                        playSound($sound_name, 1, $level);
                    }    
                }
            }
        }


        processSubscriptionsSafe('SAY', array('id' => $rec['ID'], 'level' => $level, 'message' => $ph, 'member_id' => $member_id, 'image' => $image)); //, 'ignoreVoice'=>$ignoreVoice

        if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '') {
            setEvalCode(SETTINGS_HOOK_AFTER_SAY);
            eval(SETTINGS_HOOK_AFTER_SAY);
            setEvalCode();
        }
    }
    //dprint(date('Y-m-d H:i:s')." Say OK",false);

}

function ask($prompt, $target = '')
{

    $source = 'ask';
    $level = 0;
    $rec = array();
    $rec['MESSAGE'] = $prompt;
    $rec['ADDED'] = date('Y-m-d H:i:s');
    $rec['ROOM_ID'] = 0;
    $rec['MEMBER_ID'] = 0;
    $rec['SOURCE'] = $source;
    $rec['IMPORTANCE'] = $level;
    $rec['ID'] = SQLInsert('shouts', $rec);

    processSubscriptionsSafe('ASK', array('prompt' => $prompt, 'message' => $prompt, 'target' => $target, 'destination' => $target));
}
