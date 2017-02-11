<?php

$links=SQLSelect("SELECT devices_linked.*, devices.LINKED_OBJECT FROM devices_linked LEFT JOIN devices ON devices_linked.DEVICE2_ID=devices.ID WHERE DEVICE1_ID=".(int)$device1['ID']);
$total = count($links);
for ($i = 0; $i < $total; $i++) {
    $link_type=$links[$i]['LINK_TYPE'];
    $object=$links[$i]['LINKED_OBJECT'];
    $settings=unserialize($links[$i]['LINK_SETTINGS']);
    $timer_name='linkTimer'.$links[$i]['ID'];
    $action_string='';
    // -----------------------------------------------------------------
    if ($link_type=='switch_it') {
        if ($settings['action_type']=='turnoff') {
            $action_string='callMethod("'.$object.'.turnOff'.'");';
        } elseif ($settings['action_type']=='turnon') {
            $action_string='callMethod("'.$object.'.turnOn'.'");';
        } elseif ($settings['action_type']=='switch') {
            $action_string='callMethod("'.$object.'.switch'.'");';
        }
        if ((int)$settings['action_delay']>0) {
            $action_string='setTimeout(\''.$timer_name.'\',\''.$action_string.'\','.(int)$settings['action_delay'].');';
        }
    // -----------------------------------------------------------------
    } elseif ($link_type=='sensor_switch') {
        if ($settings['action_type']=='turnoff' && gg($object.'.status')) {
            $action_string='callMethod("'.$object.'.turnOff'.'");';
        } elseif ($settings['action_type']=='turnon' && !gg($object.'.status')) {
            $action_string='callMethod("'.$object.'.turnOn'.'");';
        }
        if ($settings['condition_type']=='above' && $value>=(float)$settings['condition_value']) {
            //do the action
        } elseif ($settings['condition_type']=='below' && $value<(float)$settings['condition_value']) {
            //do the action
        } else {
            //do nothing
            $action_string='';
        }
    }

    // -----------------------------------------------------------------
    if ($action_string!='') {
        //DebMes("Action string: ".$action_string);
        try {
            $code = $action_string;
            $success = eval($code);
            if ($success === false) {
                registerError('linked_device', sprintf('Error in linked device code "%s". Code: %s', $link_type, $code));
            }
        } catch (Exception $e) {
            registerError('linked_device', sprintf('Error in script "%s": '.$e->getMessage(), $link_type));
        }

    }

}

