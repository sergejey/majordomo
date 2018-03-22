<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

/*
 * array('level' => $level, 'message' => $ph, 'member_id' => $member_id)
 * $details['BREAK'] = 1 / 0
 */
@include_once(ROOT . 'languages/' . $this->name . '_' . SETTINGS_SITE_LANGUAGE . '.php');
@include_once(ROOT . 'languages/' . $this->name . '_default' . '.php');

$command = $details['message'];
$processed = 0;
$reply_confirm = 0;

$devices = SQLSelect("SELECT ID, TITLE, TYPE, LINKED_OBJECT FROM devices");
$total = count($devices);
for ($i = 0; $i < $total; $i++) {

    $device_matched = 0;
    if (preg_match('/' . preg_quote($devices[$i]['TITLE']) . '/uis', $command)) {
        $device_matched = 1;
    } elseif (file_exists(ROOT . "lib/phpmorphy/common.php")) {
        require_once(ROOT . "lib/phpmorphy/common.php");
        $opts = array(
            'storage' => PHPMORPHY_STORAGE_MEM,
            'predict_by_suffix' => true,
            'predict_by_db' => true,
            'graminfo_as_text' => true,
        );
        $dir = ROOT . 'lib/phpmorphy/dicts';
        if (SETTINGS_SITE_LANGUAGE == 'ru') {
            $lang = 'ru_RU';
        } else {
            $lang = 'en_EN';
        }
        try {
            $morphy = new phpMorphy($dir, $lang, $opts);
            $this->morphy =& $morphy;
        } catch (phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }
        $words = explode(' ', $command);
        $base_forms = array();
        $totals = count($words);
        for ($is = 0; $is < $totals; $is++) {
            if (preg_match('/^(\d+)$/', $words[$is])) {
                $base_forms[$is] = array($words[$is]);
            } elseif (!preg_match('/[\(\)\+\.]/', $words[$is])) {
                $Word = mb_strtoupper($words[$is], 'UTF-8');
                $base_forms[$is] = $morphy->getBaseForm($Word);
                $base_forms[$is][]=$words[$is];
            } else {
                $base_forms[$is] = array($words[$is]);
            }
        }
        $combos = $this->generate_combinations($base_forms);
        $lines = array();
        $totals = count($combos);
        for ($is = 0; $is < $totals; $is++) {
            $lines[] = implode(' ', $combos[$is]);
        }
        if (preg_match('/' . preg_quote($devices[$i]['TITLE']) . '/isu', implode('@@@@', $lines), $matches)) {
            $device_matched = 1;
        }
    }

    if ($device_matched) {
        //found device
        $device_id = $devices[$i]['ID'];
        $device_type = $devices[$i]['TYPE'];
        $device_title = $devices[$i]['TITLE'];
        $linked_object = $devices[$i]['LINKED_OBJECT'];
        if ($device_type == 'sensor_light' || $device_type == 'sensor_percentage' || $device_type == 'sensor_humidity') {
            sayReply($device_title . ' ' . gg($linked_object . '.value') . '%', 2);
            $processed = 1;
        } elseif ($device_type == 'sensor_temp') {
            sayReply($device_title . ' ' . gg($linked_object . '.value') . ' ' . LANG_DEVICES_DEGREES, 2);
            $processed = 1;
        } elseif (preg_match('/sensor/', $device_type)) {
            sayReply($device_title . ' ' . gg($linked_object . '.value') . '', 2);
            $processed = 1;
        } elseif ($device_type == 'counter') {
            sayReply($device_title . ' ' . gg($linked_object . '.value') . ' ' . gg($linked_object . '.unit'), 2);
            $processed = 1;
        } elseif ($device_type == 'openclose') {
            sayReply($device_title . ' ' . (gg($linked_object . '.status') ? LANG_DEVICES_STATUS_CLOSED : LANG_DEVICES_STATUS_OPEN), 2);
            $processed = 1;
        } elseif ($device_type == 'smoke' || $device_type == 'leak') {
            sayReply($device_title . ' ' . (gg($linked_object . '.status') ? LANG_DEVICES_STATUS_ALARM : LANG_DEVICES_NORMAL_VALUE), 2);
            $processed = 1;
        } elseif ($device_type == 'button') {
            callMethod($linked_object . '.pressed');
            $processed = 1;
            $reply_confirm = 1;
        } elseif ($device_type == 'controller' ||
            $device_type == 'relay' ||
            $device_type == 'dimmer' ||
            $device_type == 'rgb'
        ) {
            if (preg_match('/' . LANG_DEVICES_PATTERN_TURNON . '/uis', $command)) {
                callMethod($linked_object . '.turnOn');
                $processed = 1;
                $reply_confirm = 1;
            } elseif (preg_match('/' . LANG_DEVICES_PATTERN_TURNOFF . '/uis', $command)) {
                callMethod($linked_object . '.turnOff');
                $processed = 1;
                $reply_confirm = 1;
            }
        }
    }
    if ($processed) break;
}


if ($reply_confirm) {
    $items = explode('|', LANG_DEVICES_COMMAND_CONFIRMATION);
    $items = array_map('trim', $items);
    say($items[array_rand($items)], 2);
}

if ($processed) {
    $details['PROCESSED'] = 1;
}