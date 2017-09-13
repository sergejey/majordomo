<?php

@include_once(ROOT . 'languages/devices_' . SETTINGS_SITE_LANGUAGE . '.php');
@include_once(ROOT . 'languages/devices_default' . '.php');

$rooms = getObjectsByClass("Rooms");
$total = count($rooms);
for ($i = 0; $i < $total; $i++) {
    $rooms[$i]['room'] = getGlobal($rooms[$i]['TITLE'] . '.Title');
    if (!$rooms[$i]['room']) {
        $rooms[$i]['room'] = $rooms[$i]['TITLE'];
    }
    $rooms[$i]['active'] = getGlobal($rooms[$i]['TITLE'] . '.SomebodyHere');
    $rooms[$i]['time'] = getGlobal($rooms[$i]['TITLE'] . '.LatestActivity');
	$rooms[$i]['user'] = getGlobal($rooms[$i]['TITLE'] . '.UserName');
	if (!$rooms[$i]['time']) {
        $rooms[$i]['time'] = 0;
    }
    $rooms[$i]['diff'] = time() - $rooms[$i]['time'];
}

if (!function_exists('cmpRoomsActivity')) {
    function cmpRoomsActivity($a, $b)
    {
        if ($a['diff'] == $b['diff']) {
            return 0;
        }
        return ($a['diff'] < $b['diff']) ? -1 : 1;
    }
}
usort($rooms, "cmpRoomsActivity");

if (!$rooms[0]['active']) {
    $somebodyHomeText = LANG_DEVICES_ROOMS_NOBODYHOME." ".LANG_DEVICES_ROOMS_ACTIVITY." " . date('H:i m.d.y', $rooms[0]['time']) . " (" . $rooms[0]['room'] . "). " . $rooms[0]['user'];
	setGlobal($rooms[0]['TITLE']. '.UserName','');
} else {
    $res_rooms = array();
    for ($i = 0; $i < $total; $i++) {
        if ($rooms[$i]['active']) {
            $res_rooms[] = $rooms[$i]['room']. '. '. $rooms[$i]['user'];
        }
    }
    $somebodyHomeText = LANG_DEVICES_ROOMS_SOMEBODYHOME." ". LANG_DEVICES_ROOMS_ACTIVITY . " " . implode(", ", $res_rooms);
}

echo $somebodyHomeText;

setGlobal('somebodyHomeText', $somebodyHomeText);
