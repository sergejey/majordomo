<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES == 1) return;

/*
 * array('level' => $level, 'message' => $ph, 'member_id' => $member_id, 'source' => $source)
 * $details['BREAK'] = 1 / 0
 */
@include_once(ROOT . 'languages/' . $this->name . '_' . SETTINGS_SITE_LANGUAGE . '.php');
@include_once(ROOT . 'languages/' . $this->name . '_default' . '.php');

if ($details['source']) {
	$terminal = getTerminalByID(str_replace("terminal", "", $details['source']));
	if ($terminal['LOCATION_ID']) {
		$location_id = $terminal['LOCATION_ID'];
	}
} 

$command = $details['message'];

$run_code = '';
$opposite_code = '';
$add_phrase = '';
$period_delay = 0;
$period_run_for = 0;

if (preg_match('/' . LANG_PATTERN_DO_AFTER . ' (\d+?) (' . LANG_PATTERN_SECOND . '|' . LANG_PATTERN_MINUTE . '|' . LANG_PATTERN_HOUR . ')/uis', textToNumbers($command), $m)) {
    $period_number = $m[1];
    $add_phrase = ' ' . $m[0];
    if (preg_match('/' . LANG_PATTERN_SECOND . '/uis', $m[2])) {
        $period_delay = $period_number;
    } elseif (preg_match('/' . LANG_PATTERN_MINUTE . '/uis', $m[2])) {
        $period_delay = $period_number * 60;
    } elseif (preg_match('/' . LANG_PATTERN_HOUR . '/uis', $m[2])) {
        $period_delay = $period_number * 60 * 60;
    }
    $command = trim(str_replace($m[0], '', textToNumbers($command)));
} elseif (preg_match('/' . LANG_PATTERN_DO_FOR . ' (\d+?) (' . LANG_PATTERN_SECOND . '|' . LANG_PATTERN_MINUTE . '|' . LANG_PATTERN_HOUR . ')/uis', textToNumbers($command), $m)) {
    $period_number = $m[1];
    $add_phrase = ' ' . $m[0];
    if (preg_match('/' . LANG_PATTERN_SECOND . '/uis', $m[2])) {
        $period_run_for = $period_number;
    } elseif (preg_match('/' . LANG_PATTERN_MINUTE . '/uis', $m[2])) {
        $period_run_for = $period_number * 60;
    } elseif (preg_match('/' . LANG_PATTERN_HOUR . '/uis', $m[2])) {
        $period_run_for = $period_number * 60 * 60;
    }
    $command = trim(str_replace($m[0], '', textToNumbers($command)));
}

$processed = 0;
$reply_confirm = 0;
$reply_say = '';

$phpmorphy_loaded = 0;

if (file_exists(ROOT . "lib/phpmorphy/common.php")) {
    require_once(ROOT . "lib/phpmorphy/common.php");
    $opts = array(
        'storage' => PHPMORPHY_STORAGE_MEM,
        'predict_by_suffix' => true,
        'predict_by_db' => true,
        'graminfo_as_text' => true,
    );
    $dir = ROOT . 'lib/phpmorphy/dicts';
    $lang = SETTINGS_SITE_LANGUAGE_CODE;
    try {
        $morphy = new phpMorphy($dir, $lang, $opts);
        $this->morphy =& $morphy;
    } catch (phpMorphy_Exception $e) {
        die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
    }
    $words = explode(' ', $command);
    $words_filtered = array();
    $filtered_count = 0;
    $base_forms = array();
    $base_forms_filtered = array();
    $totals = count($words);
    for ($is = 0; $is < $totals; $is++) {
        $filtered = 0;
        $upper = mb_strtoupper($words[$is], 'UTF-8');
        $len = mb_strlen($words[$is], 'UTF-8');
        if ($len >= 3) {
            $words_filtered[] = $words[$is];
            $filtered = 1;
            $filtered_count++;
        }
        if (preg_match('/^(\d+)$/', $words[$is])) {
            $base_forms[$is] = array($words[$is]);
        } elseif (!preg_match('/[\(\)\+\.]/', $words[$is])) {
            $Word = mb_strtoupper($words[$is], 'UTF-8');
            $base_forms[$is] = $morphy->getBaseForm($Word);
            $base_forms[$is][] = $words[$is];
        } else {
            $base_forms[$is] = array($words[$is]);
        }
        if ($filtered) {
            $base_forms_filtered[$filtered_count - 1] = $base_forms[$is];
        }
    }
    $combos = $this->generate_combinations($base_forms);

    if ($filtered_count < $totals) {
        $add_combos = $this->generate_combinations($base_forms_filtered);
        foreach ($add_combos as $cmb) {
            $combos[] = $cmb;
        }
    }

    $lines = array();
    $totals = count($combos);
    for ($is = 0; $is < $totals; $is++) {
        $lines[] = implode(' ', $combos[$is]);
    }
    $phpmorphy_loaded = 1;
}

$devices = SQLSelect("SELECT ID, TITLE, ALT_TITLES, TYPE, LINKED_OBJECT, LOCATION_ID FROM devices");
foreach ($devices as $device) {
    if (trim($device['ALT_TITLES']) != '') {
        $nicknames = explode(',', trim($device['ALT_TITLES']));
        foreach ($nicknames as $nickname) {
            $add_rec = $device;
            $add_rec['TITLE'] = $nickname;
            $devices[] = $add_rec;
        }
    }
}
$groups = SQLSelect("SELECT * FROM devices_groups");
$total = count($groups);
for ($i = 0; $i < $total; $i++) {
    $add_rec = $groups[$i];
    $add_rec['TYPE'] = 'group';
    $devices[] = $add_rec;
}

$rooms = SQLSelect("SELECT locations.ID, locations.TITLE, COUNT(*) AS TOTAL FROM locations, devices WHERE locations.ID=devices.LOCATION_ID GROUP BY locations.ID");
foreach ($rooms as $room) {
    //lights
    //if ($room['TITLE']=='Кабинет') {
    $device_types = array();
    $room_devices = SQLSelect("SELECT * FROM devices WHERE LOCATION_ID=" . $room['ID'] . " AND TYPE='relay'");
    foreach ($room_devices as $device) {
        $loadType = gg($device['LINKED_OBJECT'] . '.loadType');
        $device_types[$loadType][] = $device;
    }
    if (isset($device_types['light'])) {
        $add_rec = array();
        $add_rec['TYPE'] = 'group';
        $add_rec['TITLE'] = LANG_DEVICES_LOADTYPE_LIGHT . ' ' . $room['TITLE'];
        $add_rec['DEVICES'] = $device_types['light'];
        $add_rec['APPLY_TYPES'] = 'relay';
        $devices[] = $add_rec;

        $add_rec = array();
        $add_rec['TYPE'] = 'group';
        $add_rec['TITLE'] = LANG_DEVICES_LOADTYPE_LIGHT_ALT . ' ' . $room['TITLE'];
        $add_rec['DEVICES'] = $device_types['light'];
        $add_rec['APPLY_TYPES'] = 'relay';
        $devices[] = $add_rec;
    }
    //}


}

if ($phpmorphy_loaded) {
    $total = count($devices);
    $add_devices = array();
    for ($i = 0; $i < $total; $i++) {
        $device_title = $devices[$i]['TITLE'];
        $words = explode(' ', mb_strtoupper($device_title, 'UTF-8'));
        $base_forms = array();
        $totals = count($words);
        for ($is = 0; $is < $totals; $is++) {
            if (preg_match('/^(\d+)$/', $words[$is])) {
                $base_forms[$is] = array($words[$is]);
            } elseif (!preg_match('/[\(\)\+\.]/', $words[$is])) {
                $Word = mb_strtoupper($words[$is], 'UTF-8');
                $base_form = $morphy->getBaseForm($Word);
                if (is_array($base_form)) {
                    $base_forms[$is] = $base_form;
                } else {
                    $base_forms[$is] = array();
                }
                if (!in_array($words[$is], $base_forms[$is])) {
                    $base_forms[$is][] = $words[$is];
                }
            } else {
                $base_forms[$is] = array($words[$is]);
            }
        }
        $combos = $this->generate_combinations($base_forms);
        $phrases = array();
        foreach ($combos as $combo) {
            $mutations = $this->computePermutations($combo);
            foreach ($mutations as $m) {
                $phrases[] = implode(' ', $m);
            }
        }
        $device_titles = array();
        $totals = count($phrases);
        for ($is = 0; $is < $totals; $is++) {
            $new_title = $phrases[$is];
            $device_titles[] = $new_title;
            $new_device = $devices[$i];
            $new_device['TITLE'] = $new_title;
            $new_device['ORIGINAL_TITLE'] = $device_title;
            $add_devices[] = $new_device;
        }
    }
    foreach ($add_devices as $device) {
        $devices[] = $device;
    }
}

//dprint($lines,false);
//dprint($devices);
$compare_title = $command;
if (preg_match('/' . LANG_DEVICES_PATTERN_TURNON . '/uis', $compare_title, $m)) {
    $compare_title = trim(str_replace($m[0], ' ', $compare_title));
}
if (preg_match('/' . LANG_DEVICES_PATTERN_TURNOFF . '/uis', $compare_title, $m)) {
    $compare_title = trim(str_replace($m[0], ' ', $compare_title));
}

$compare_title = trim(preg_replace('/^ть /', '', $compare_title));
$compare_title = trim(preg_replace('/^те /', '', $compare_title));

if ($compare_title == '') {
    return;
}


$total = count($devices);
for ($i = 0; $i < $total; $i++) {
    $device_matched = 0;

   // если есть местоположение терминала 
    if ($location_id) {
        // ищем строгое соответствие по названию и местоположению
        foreach($devices as $key => $value) {
          if(in_array($location_id, $value) && strtolower($devices[$key]['TITLE']) == strtolower ($compare_title)) {
               $i = $key;
               $device_matched = 1;
               break ;
            }
        }
        if (!$device_matched ) {
            // ищем строгое соответствие по названию
            foreach($devices as $key => $value) {
                if(in_array(strtolower ($compare_title), strtolower ($value))) {
                    $i = $key;
                    $device_matched = 1;
                    break ;
                }
            }
        }
    }

    // ищем по старому принципу
    if (preg_match('/' . preg_quote($devices[$i]['TITLE']) . '/uis', $compare_title)) {
        $device_matched = 1;
    } elseif (preg_match('/' . preg_quote($compare_title) . '/uis', $devices[$i]['TITLE'])) {
        $device_matched = 1;
    } elseif ($phpmorphy_loaded) {
        if (preg_match('/' . preg_quote($devices[$i]['TITLE']) . '/isu', implode('@@@@', $lines), $matches)) {
            $device_matched = 1;
        }
    }

    /*
    if (preg_match('/свет над столом/uis',$devices[$i]['TITLE'])) {
        dprint($devices[$i]['TITLE'].' - '.$compare_title.': '.$device_matched);
    }
    */

    if ($device_matched) {

        //found device
        $device_id = $devices[$i]['ID'];
        $device_type = $devices[$i]['TYPE'];
        if ($devices[$i]['ORIGINAL_TITLE'] != '') {
            $device_title = $devices[$i]['ORIGINAL_TITLE'];
        } else {
            $device_title = $devices[$i]['TITLE'];
        }

        DebMes("Device found for $command ($device_title)", 'simple_devices');

        $linked_object = $devices[$i]['LINKED_OBJECT'];
        if ($device_type == 'sensor_percentage' || $device_type == 'sensor_humidity') {
            $reply_say = $device_title . ' ' . gg($linked_object . '.value') . '%';
            $processed = 1;
        } elseif ($device_type == 'sensor_light') {
            $reply_say = $device_title . ' ' . gg($linked_object . '.value');
            $processed = 1;
        } elseif ($device_type == 'sensor_temp') {
            $reply_say = $device_title . ' ' . gg($linked_object . '.value') . ' ' . LANG_DEVICES_DEGREES;
            $processed = 1;
        } elseif (preg_match('/sensor/', $device_type)) {
            $reply_say = $device_title . ' ' . gg($linked_object . '.value') . '';
            $processed = 1;
        } elseif ($device_type == 'counter') {
            $reply_say = $device_title . ' ' . gg($linked_object . '.value') . ' ' . gg($linked_object . '.unit');
            $processed = 1;
        } elseif ($device_type == 'openclose') {
            $reply_say = $device_title . ' ' . (gg($linked_object . '.status') ? LANG_DEVICES_STATUS_CLOSED : LANG_DEVICES_STATUS_OPEN);
            $processed = 1;
        } elseif ($device_type == 'smoke' || $device_type == 'leak') {
            $reply_say = $device_title . ' ' . (gg($linked_object . '.status') ? LANG_DEVICES_STATUS_ALARM : LANG_DEVICES_NORMAL_VALUE);
            $processed = 1;
        } elseif ($device_type == 'button') {
            $run_code .= "callMethod('$linked_object.pressed');";
            $processed = 1;
            $reply_confirm = 1;
        } elseif ($device_type == 'controller' ||
            $device_type == 'relay' ||
            $device_type == 'dimmer' ||
            $device_type == 'rgb'
        ) {
            if (preg_match('/' . LANG_DEVICES_PATTERN_TURNON . '/uis', $command)) {
                $reply_say = LANG_TURNING_ON . ' ' . $device_title . $add_phrase;
                $run_code .= "callMethod('$linked_object.turnOn');";
                $opposite_code .= "callMethod('$linked_object.turnOff');";
                $processed = 1;
                //$reply_confirm = 1;
            } elseif (preg_match('/' . LANG_DEVICES_PATTERN_TURNOFF . '/uis', $command)) {
                $reply_say = LANG_TURNING_OFF . ' ' . $device_title . $add_phrase;
                $run_code .= "callMethod('$linked_object.turnOff');";
                $opposite_code .= "callMethod('$linked_object.turnOn');";
                $processed = 1;
                //$reply_confirm = 1;
            }
       } elseif ($device_type == 'openable') {
            if (preg_match('/' . LANG_DEVICES_PATTERN_OPEN . '/uis', $command)) {
                $reply_say = LANG_TURNING_OPEN . ' ' . $device_title . $add_phrase;
                $run_code .= "callMethod('$linked_object.Open');";
                $opposite_code .= "callMethod('$linked_object.Close');";
                $processed = 1;
                //$reply_confirm = 1;
            } elseif (preg_match('/' . LANG_DEVICES_PATTERN_CLOSE . '/uis', $command)) {
                $reply_say = LANG_TURNING_CLOSE . ' ' . $device_title . $add_phrase;
                $run_code .= "callMethod('$linked_object.Close');";
                $opposite_code .= "callMethod('$linked_object.Open');";
                $processed = 1;
                //$reply_confirm = 1;
            }
        } elseif ($device_type == 'group') {
            $applies_to = explode(',', $devices[$i]['APPLY_TYPES']);
            if (is_array($devices[$i]['DEVICES'])) {
                $devices_in_group = array();
                foreach ($devices[$i]['DEVICES'] as $group_device) {
                    $devices_in_group[] = $group_device['LINKED_OBJECT'];
                }
            } else {
                $devices_in_group = getObjectsByProperty('group' . $devices[$i]['SYS_NAME'], 1);
            }
            //dprint($devices_in_group);

            if (!is_array($devices_in_group)) continue;

            if (in_array('relay', $applies_to) ||
                in_array('dimmer', $applies_to) ||
                in_array('rgb', $applies_to) ||
                0
            ) {
                if (preg_match('/' . LANG_DEVICES_PATTERN_TURNON . '/uis', $command)) {
                    $reply_say = LANG_TURNING_ON . ' ' . $device_title . $add_phrase;
                    foreach ($devices_in_group as $linked_object) {
                        $run_code .= "callMethod('$linked_object.turnOn');";
                        $opposite_code .= "callMethod('$linked_object.turnOff');";
                    }
                    $processed = 1;
                    //$reply_confirm = 1;
                } elseif (preg_match('/' . LANG_DEVICES_PATTERN_TURNOFF . '/uis', $command)) {
                    $reply_say = LANG_TURNING_OFF . ' ' . $device_title . $add_phrase;
                    foreach ($devices_in_group as $linked_object) {
                        $run_code .= "callMethod('$linked_object.turnOff');";
                        $opposite_code .= "callMethod('$linked_object.turnOn');";
                    }
                    $processed = 1;
                    //$reply_confirm = 1;
                }
            }
        }

        $addons_dir = dirname(__FILE__) . '/addons';
        if (is_dir($addons_dir)) {
            $addon_files = scandir($addons_dir);
            foreach ($addon_files as $file) {
                if (preg_match('/\_commands\.php$/', $file)) {
                    require($addons_dir . '/' . $file);
                }
            }
        }
    }
    if ($processed) break;
}

if ($run_code != '' && $period_delay > 0) {
    setTimeout('delay' . md5($run_code), $run_code, $period_delay);
} elseif ($run_code != '' && $period_run_for > 0 && $opposite_code != '') {
    eval($run_code);
    setTimeout('opposite' . md5($run_code), $opposite_code, $period_run_for);
} elseif ($run_code != '') {
    eval($run_code);
}

if ($reply_say != '') {
    sayReplySafe($reply_say, 2);
}

if ($reply_confirm) {
    $items = explode('|', LANG_DEVICES_COMMAND_CONFIRMATION);
    $items = array_map('trim', $items);
    sayReplySafe($items[array_rand($items)], 2);
}

if ($processed) {
    $details['PROCESSED'] = 1;
    $details['BREAK'] = 1;
}
