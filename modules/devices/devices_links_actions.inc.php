<?php

startMeasure('homebridge_update');

$debug_sync = 0;
//DebMes("homebridgesync for ".$device1['TITLE'],'homebridge');
if (!$device1['SYSTEM_DEVICE'] && !$device1['ARCHIVED']) {
    if ($this->isHomeBridgeAvailable()) {
        // send updated status to HomeKit
        include DIR_MODULES . 'devices/homebridgeSendUpdate.inc.php';
    } else if (isModuleInstalled('homekit')) {
        // send updated status to module HomeKit
        include DIR_MODULES . 'homekit/homebridgeSendUpdate.inc.php';
    }
}
endMeasure('homebridge_update');

startMeasure('checkingLinks');
$value = (float)gg($device1['LINKED_OBJECT'] . '.value');
$status = (float)gg($device1['LINKED_OBJECT'] . '.status');

$links = SQLSelect("SELECT devices_linked.*, devices.LINKED_OBJECT FROM devices_linked LEFT JOIN devices ON devices_linked.DEVICE2_ID=devices.ID WHERE devices_linked.IS_ACTIVE=1 AND DEVICE1_ID=" . (int)$device1['ID']);
$total = count($links);
for ($i = 0; $i < $total; $i++) {
    if (!checkAccess('sdevice', $links[$i]['ID'])) continue;
    $link_type = $links[$i]['LINK_TYPE'];
    $settings = unserialize($links[$i]['LINK_SETTINGS']);
    if ($device1['TYPE'] == 'button' && !$status) continue;
    if ($device1['TYPE'] == 'motion' && $settings['action_type'] != 'sync' && $settings['action_type'] != 'sync_inverted' && !$status) continue;
    $object = $links[$i]['LINKED_OBJECT'];
    $timer_name = 'linkTimer' . $links[$i]['ID'];
    $action_string = '';
    // -----------------------------------------------------------------
    if ($link_type == 'switch_it') {
        if ($settings['action_type'] == 'turnoff') {
            $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'turnon') {
            $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'switch') {
            $action_string = 'callMethodSafe("' . $object . '.switch' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'close') {
            $action_string = 'callMethodSafe("' . $object . '.close' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'open') {
            $action_string = 'callMethodSafe("' . $object . '.open' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'sync') {
            if ($status) {
                $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
            } else {
                $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
            }
        } elseif ($settings['action_type'] == 'sync_inverted') {
            if (!$status) {
                $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
            } else {
                $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
            }
        }
        if ($settings['action_delay'] != '') {
            $settings['action_delay'] = (int)processTitle($settings['action_delay']);
            if ($settings['action_delay'] > 0) {
                $action_string = 'setTimeout(\'' . $timer_name . '\',\'' . $action_string . '\',' . (int)$settings['action_delay'] . ');';
            }
        }
    } elseif ($link_type == 'switch_timer') {
        $timer_name = $object . '_switch_timer';
        $action_string = '';
        if ($settings['darktime']) {
            $action_string .= 'if (gg("DarknessMode.active")) {';
        }
        $action_string .= 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        if ($settings['action_delay'] != '') {
            $settings['action_delay'] = (int)processTitle($settings['action_delay']);
            if ($settings['action_delay'] > 0) {
                $action_string .= 'setTimeout(\'' . $timer_name . '\',\'' . 'callMethod("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));' . '\',' . (int)$settings['action_delay'] . ');';
            }
        }
        if ($settings['darktime']) {
            $action_string .= '}';
        }
    } elseif ($link_type == 'set_color') {
        $action_string = 'callMethodSafe("' . $object . '.setColor' . '",array("color"=>"' . processTitle($settings['action_color']) . '","link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        if ($settings['action_delay'] != '') {
            $settings['action_delay'] = (int)processTitle($settings['action_delay']);
            if ($settings['action_delay'] > 0) {
                $action_string = 'setTimeout(\'' . $timer_name . '\',\'' . $action_string . '\',' . (int)$settings['action_delay'] . ');';
            }
        }
        // -----------------------------------------------------------------
        // -----------------------------------------------------------------
    } elseif ($link_type == 'sensor_switch') {
        if ($settings['action_type'] == 'turnoff' && gg($object . '.status')) {
            $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'turnon' && !gg($object . '.status')) {
            $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'open' && gg($object . '.status')) {
            $action_string = 'callMethodSafe("' . $object . '.open' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif ($settings['action_type'] == 'close' && !gg($object . '.status')) {
            $action_string = 'callMethodSafe("' . $object . '.close' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        }

        if ($settings['source_value_type'] != '') {
            $period = (int)processTitle($settings['source_value_time']);
            if ($period < 1) $period = 1;
            if ($settings['source_value_type'] == 'avg') {
                $value = getHistoryAvg($device1['LINKED_OBJECT'] . '.value', (-1) * $period);
            } elseif ($settings['source_value_type'] == 'min') {
                $value = getHistoryMin($device1['LINKED_OBJECT'] . '.value', (-1) * $period);
            } elseif ($settings['source_value_type'] == 'max') {
                $value = getHistoryMax($device1['LINKED_OBJECT'] . '.value', (-1) * $period);
            }
        }

        if ($settings['condition_type'] == 'above' && $value >= (float)processTitle($settings['condition_value'])) {
            //do the action
        } elseif ($settings['condition_type'] == 'below' && $value < (float)processTitle($settings['condition_value'])) {
            //do the action
        } else {
            //do nothing
            $action_string = '';
        }
    } elseif ($link_type == 'sensor_pass') {
        $action_string = 'sg("' . $object . '.value' . '","' . $value . '");';
    } elseif ($link_type == 'open_sensor_pass') {
        $action_string = 'sg("' . $object . '.status' . '","' . $status . '");';
    } elseif ($link_type == 'thermostat_switch') {
        $set_value = 0;
        $current_relay_status = gg($device1['LINKED_OBJECT'] . '.relay_status');
        $current_target_status = gg($object . '.status');
        if ($settings['invert_status']) {
            $set_value = $current_relay_status ? 0 : 1;
        } else {
            $set_value = $current_relay_status;
        }
        if ($set_value && !$current_target_status) {
            // turn on
            $action_string = 'callMethodSafe("' . $object . '.turnOn' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        } elseif (!$set_value && $current_target_status) {
            // turn off
            $action_string = 'callMethodSafe("' . $object . '.turnOff' . '",array("link_source"=>"' . $device1['LINKED_OBJECT'] . '"));';
        }
    }

    $addons_dir = dirname(__FILE__) . '/addons';
    if (is_dir($addons_dir)) {
        $addon_files = scandir($addons_dir);
        foreach ($addon_files as $file) {
            if (preg_match('/\_links_actions\.php$/', $file)) {
                require($addons_dir . '/' . $file);
            }
        }
    }

    // -----------------------------------------------------------------
    if ($action_string != '') {
        SQLExec('UPDATE devices_linked SET LAST_EXECUTED=NOW(), TIMER_NAME="' . $timer_name . '" WHERE ID=' . $links[$i]['ID']);
        try {
            $code = $action_string;
            setEvalCode($code);
            $success = eval($code);
            setEvalCode();
            if ($success === false) {
                registerError('linked_device', sprintf('Error in linked device code "%s". Code: %s', $link_type, $code));
            }
        } catch (Exception $e) {
            registerError('linked_device', sprintf('Error in script "%s": ' . $e->getMessage(), $link_type));
        }
    }

    endMeasure('checkingLinks');

}
