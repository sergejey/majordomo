<?php
/*
* @version 0.2 (auto-set)
*/

global $session;

if ($this->owner->room_id) {
    $session->data['SHOUT_ROOM_ID'] = $this->owner->room_id;
}

global $msg;
global $getdata;
global $clear;
global $room_id;

if ($this->action == 'admin' && $clear) {
    SQLExec("DELETE FROM shouts");
    $this->redirect("?");
}

if (!$session->data['SITE_USERNAME']) {
    $out['NOT_LOGGED'] = 1;
}

if ($this->action == '' && $session->data['SITE_USER_ID'] && $msg != '') {

    if ($session->data['TERMINAL']) {
        $terminal_rec = getTerminalsByName($session->data['TERMINAL'], 1)[0];

        if ($terminal_rec['ID']) {
            $terminal_rec['LATEST_ACTIVITY'] = date('Y-m-d H:i:s');
            $terminal_rec['LATEST_REQUEST_TIME'] = $terminal_rec['LATEST_ACTIVITY'];
            $terminal_rec['LATEST_REQUEST'] = $rec['MESSAGE'] . '';
            $terminal_rec['IS_ONLINE'] = 1;
            SQLUpdate('terminals', $terminal_rec);
        }
    }
    say(htmlspecialchars($msg), 0, $session->data['SITE_USER_ID'], 'terminal' . $terminal_rec['ID']);
    /*
    $rec=array();
    $rec['ROOM_ID']=(int)$room_id;
    $rec['MEMBER_ID']=$session->data['logged_user'];
    $rec['MESSAGE']=htmlspecialchars($msg);
    $rec['ADDED']=date('Y-m-d H:i:s');
    SQLInsert('shouts', $rec);
    */


    /*
    include_once(DIR_MODULES.'patterns/patterns.class.php');
    $pt=new patterns();
    $res=$pt->checkAllPatterns($rec['MEMBER_ID']);
    if (!$res) {
     processCommand($msg);
    }
    */
    $getdata = 1;
}

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";

if ($room_id) {
    $qry .= " AND ROOM_ID=" . (int)$room_id;
    $session->data['SHOUT_ROOM_ID'] = (int)$room_id;
}

// search filters
// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['shouts_qry'];
} else {
    $session->data['shouts_qry'] = $qry;
}
if (!$qry) $qry = "1";
// FIELDS ORDER
global $sortby;
if (!$sortby) {
    $sortby = $session->data['shouts_sort'];
} else {
    if ($session->data['shouts_sort'] == $sortby) {
        if (Is_Integer(strpos($sortby, ' DESC'))) {
            $sortby = str_replace(' DESC', '', $sortby);
        } else {
            $sortby = $sortby . " DESC";
        }
    }
    $session->data['shouts_sort'] = $sortby;
}
if (!$sortby) $sortby = "shouts.ADDED DESC";
$out['SORTBY'] = $sortby;
// SEARCH RESULTS
if ($this->action != 'admin') {
    $limit = "LIMIT 50";
}

global $limit;
if ($limit) {
    $this->limit = $limit;
}

if ($this->limit) {
    $limit = "LIMIT " . $this->limit;
} else {
    $limit = "LIMIT 50";
}
$limit = str_replace('LIMIT LIMIT', 'LIMIT', $limit);

$out['LIMIT'] = $this->limit;


$res = SQLSelect("SELECT shouts.*, DATE_FORMAT(shouts.ADDED, '%H:%i') as DAT, TO_DAYS(shouts.ADDED) as DT, users.NAME, users.COLOR FROM shouts LEFT JOIN users ON shouts.MEMBER_ID=users.ID WHERE $qry ORDER BY shouts.ADDED DESC, ID DESC $limit");


//  if ($_GET['reverse']) {
$this->reverse = 1;
//  }

if (!$this->reverse) {
    $res = array_reverse($res);
} else {
    $out['REVERSE'] = 1;
}
$txtdata = '';

if ($this->mobile) {
    $out['MOBILE'] = 1;
}

if ($session->data['SHOUT_ROOM_ID'] && LOGGED_USER) {
    $room = SQLSelectOne("SELECT * FROM shoutrooms WHERE ID='" . (int)$session->data['SHOUT_ROOM_ID'] . "'");
    //print_r($room);
    if ($room['ADDED_BY'] == LOGGED_USER_ID) {
        $txtdata .= "<small>[ <a href='/chat.html?delete_room=" . $room['ID'] . "' onClick=\"return confirm('" . LANG_STRING_DELETE_CONFIRM . "')\">" . LANG_STRING_DELETE . "</a> ]</small>&nbsp;&nbsp;&nbsp;";
        $roomType = $room['IS_PUBLIC'] ? LANG_SHOUTROOMS_STRING_PRIVATE : LANG_SHOUTROOMS_STRING_PUBLIC;
        $txtdata .= "<small>[ <a href='/chat.html?change_visibility=" . $room['ID'] . "' onClick=\"return confirm('" . LANG_STRING_OPERATION_CONFIRM . "')\">$roomType</a> ]</small><br><br>";
    }
}

if (defined('SETTINGS_GENERAL_ALICE_NAME') && SETTINGS_GENERAL_ALICE_NAME != '') {
    $comp_name = SETTINGS_GENERAL_ALICE_NAME;
} else {
    $comp_name = LANG_DEFAULT_COMPUTER_NAME;
}

if ($res[0]['ID']) {
    $old_dt = $res[0]['DT'];
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        // some action for every record if required
        $tmp = explode(' ', $res[$i]['ADDED']);
        $res[$i]['ADDED'] = fromDBDate($tmp[0]) . " " . $tmp[1];
        if ($res[$i]['DT'] != $old_dt) {
            $txtdata .= "<hr size=1><b>" . $tmp[0] . "</b><br>";
            $old_dt = $res[$i]['DT'];
        }
        if ($res[$i]['MEMBER_ID'] == 0) {
            $res[$i]['NAME'] = $comp_name;
        }
        $stl = '';
        if (trim($res[$i]['COLOR'])) {
            $stl = ' style="color:' . $res[$i]['COLOR'] . '"';
        }
        $txtdata .= "<span$stl>" . $res[$i]['DAT'] . " <b>" . $res[$i]['NAME'] . "</b>: " . nl2br($res[$i]['MESSAGE']) . "</span><br>";
        if ($res[$i]['IMAGE'] != '' && file_exists($res[$i]['IMAGE'])) {
            if (preg_match('/^\//is', $res[$i]['IMAGE'])) {
            $res[$i]['IMAGE'] = str_replace(ROOT, ROOTHTML, $res[$i]['IMAGE']);
        }
            $txtdata .= "<div class='thumbnail' style='max-width: 600px'><img src='" . $res[$i]['IMAGE'] . "' alt=''/></div>";
        }
    }
    $out['RESULT'] = $res;
    $out['TXT_DATA'] = $txtdata;
} else {
    $txtdata .= 'No data';
}

$rooms = SQLSelect("SELECT * FROM shoutrooms WHERE (IS_PUBLIC=1) OR (IS_PUBLIC=0 AND ADDED_BY=" . (int)$session->data['logged_user'] . ") OR (IS_PUBLIC=0 AND ID=" . (int)$session->data['SHOUT_ROOM_ID'] . ") ORDER BY PRIORITY DESC, TITLE");
if ($rooms[0]['ID']) {
    $rooms[0]['FIRST'] = 1;
    $out['ROOMS'] = $rooms;
    if ($session->data['SHOUT_ROOM_ID']) {
        $out['INIT_ROOM_ID'] = $session->data['SHOUT_ROOM_ID'];
    } else {
        $out['INIT_ROOM_ID'] = $rooms[0]['ID'];
    }
}

$out['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

if ($this->action == '' && $getdata != '') {
    header("HTTP/1.0: 200 OK\n");
    header('Content-Type: text/html; charset=utf-8');
    echo $txtdata;
    $session->save();
    exit;
}


?>
