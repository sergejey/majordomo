<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

$rooms = getObjectsByClass("Rooms");
$total = count($rooms);
for ($i = 0; $i < $total; $i++) {
    $rooms[$i]['room'] = getGlobal($rooms[$i]['TITLE'] . '.Title');
    if ($rooms[$i]['room'] == '') {
        $rooms[$i]['room'] = $rooms[$i]['TITLE'];
    }
    $rooms[$i]['active'] = getGlobal($rooms[$i]['TITLE'] . '.SomebodyHere');
    $rooms[$i]['time'] = (int)getGlobal($rooms[$i]['TITLE'] . '.LatestActivity');
    if (!$rooms[$i]['time']) {
        $rooms[$i]['time'] = 0;
    }
    $rooms[$i]['diff'] = time() - $rooms[$i]['time'];
}

usort($rooms, function ($a,$b) {
    if ($a['diff'] == $b['diff']) {
        return 0;
    }
    return ($a['diff'] < $b['diff']) ? -1 : 1;
});

if (getGlobal('NobodyHomeMode.active')) {
    $somebodyHomeText = LANG_DEVICES_ROOMS_NOBODYHOME." ".LANG_DEVICES_ROOMS_ACTIVITY." " . date('H:i', $rooms[0]['time']) . " (" . $rooms[0]['room'] . ")";
} else {
    $res_rooms = array();
    for ($i = 0; $i < $total; $i++) {
        if ($rooms[$i]['active']) {
            $res_rooms[] = $rooms[$i]['room'];
        }
    }
    $somebodyHomeText = LANG_DEVICES_ROOMS_SOMEBODYHOME.'.';
    if (count($res_rooms)>0) {
        $somebodyHomeText.=" ". LANG_DEVICES_ROOMS_ACTIVITY . ": " . implode(", ", $res_rooms);
    }

}

echo $somebodyHomeText;

setGlobal('somebodyHomeText', $somebodyHomeText);
