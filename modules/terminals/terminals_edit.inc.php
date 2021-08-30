<?php

/*
* @version 0.3 (auto-set)
*/

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}

$table_name = 'terminals';

$rec = getTerminalByID($id);

$out['LOCATIONS'] = SQLSelect("SELECT TITLE FROM locations ORDER BY TITLE+0");
if (gg($rec['LINKED_OBJECT'] .'.linkedRoom')) {
    $out['LOCATION_TITLE'] = processTitle(gg($rec['LINKED_OBJECT'] .'.linkedRoom'));
}


$out['USER'] = SQLSelect("SELECT ID, USERNAME FROM users ORDER BY USERNAME+0");
if (gg($rec['LINKED_OBJECT'] .'.username')) {
     $out['USER_NAME'] = processTitle(gg($rec['LINKED_OBJECT'] .'.username'));
}

// seting for terminals tts
$out['TTS'] = json_decode($rec['TTS_SETING'], true);
if (!$out['TTS']) {
    // тут берутся настройки для ТТС терминалов
    $out['TTS'] = array('TTS_PORT'=>gr('tts_port'), 
		'TTS_USERNAME'=>gr('tts_username'), 
		'TTS_PASSWORD'=>gr('tts_password'), 
		'TTS_CONTROL_ADDRESS'=>gr('tts_control_address'), 
		'TTS_SOUND_DEVICE'=>gr('tts_sound_device'), 
		'TTS_USE_DISPLAY'=>gr('tts_display_turnonf'), 
		'TTS_BRIGHTNESS_DISPLAY'=>gr('tts_display_brightness_level'),
		'TTS_DINGDONG_FILE'=>gr('tts_dingdong_file'),
		       );
}

// get list of ding dong files
$out['TTS_DINGDONG_FILES'] = getDirFiles(ROOT.'/cms/sounds');

//  get sound device list
if (IsWindowsOS()) {
	$dev_list = exec(DOC_ROOT.'/rc/smallplay -devicelist 2>&1');
	$dev_list = iconv('CP1251', 'utf-8', $dev_list);
	$devices = explode(',', $dev_list);
	$out['TTS_SOUND_DEVICES'] = array();
	foreach ($devices as $dev) {
		if ($dev) {
			$out['TTS_SOUND_DEVICES'][] = array('NAME' => $dev, );
		}
	}
}




if ($this->mode == 'update') {
    $ok = 1;

    //updating 'NAME' (varchar, required)
    $rec['NAME'] = gr('name', 'trim');
    $rec['NAME'] = str_replace(' ', '', $rec['NAME']);
    if ($rec['NAME'] == '') {
        $out['ERR_NAME'] = 1;
        $ok = 0;
    }

    //updating 'TITLE' (varchar, required)
    $rec['TITLE'] = gr('title');
    if ($rec['TITLE'] == '') {
        $out['ERR_TITLE'] = 1;
        $ok = 0;
    }

    //$rec['MAJORDROID_API'] = gr('majordroid_api', 'int');
    $rec['CANPLAY'] = gr('canplay', 'int');
    $rec['PLAYER_TYPE'] = gr('player_type');
    $rec['PLAYER_PORT'] = gr('player_port');
    $rec['PLAYER_USERNAME'] = gr('player_username');
    $rec['PLAYER_PASSWORD'] = gr('player_password');
    $rec['LINKED_OBJECT'] = gr('linked_object');
    $rec['MESSAGE_VOLUME_LEVEL'] = gr('message_volume_level');
    $rec['TERMINAL_VOLUME_LEVEL'] = gr('terminal_volume_level');
    if (gr('systemMML')) {
        $rec['USE_SYSTEM_MML'] = gr('systemMML');
    } else {
	$rec['USE_SYSTEM_MML'] = 0;   
    }
    $rec['PLAYER_CONTROL_ADDRESS'] = gr('player_control_address');

    $rec['CANTTS'] = gr('cantts', 'int');    
    $rec['TTS_TYPE'] = gr('tts_type');
    $rec['CANRECOGNIZE'] = gr('canrecognize', 'int');
    $rec['RECOGNIZE_TYPE'] = gr('recognize_type');

    // write seting for tts terminals тут сохраняются настройки для ТТС терминалов
    $out['TTS'] = array('TTS_PORT'=>gr('tts_port'), 
			'TTS_USERNAME'=>gr('tts_username'), 
			'TTS_PASSWORD'=>gr('tts_password'), 
			'TTS_CONTROL_ADDRESS'=>gr('tts_control_address'), 
			'TTS_SOUND_DEVICE'=>gr('tts_sound_device'), 
			'TTS_USE_DISPLAY'=>gr('tts_display_turnonf'), 
			'TTS_BRIGHTNESS_DISPLAY'=>gr('tts_display_brightness_level'),
			'TTS_DINGDONG_FILE'=>gr('tts_dingdong_file'),
		       );
    $rec['TTS_SETING'] = json_encode($out['TTS']);

    // write info for terminal user and location
    $out['LOCATION_TITLE'] = gr('location');
	
    if ($location_id = SQLSelectOne("SELECT * FROM locations WHERE TITLE = '" . $out['LOCATION_TITLE'] . "'")) {
        $rec['LOCATION_ID'] = $location_id['ID'];
    }

    $out['USER_NAME'] = gr('user');

    // автозаполнение поля 
    if (gr('min_msg_level') == '') {
        $rec['MIN_MSG_LEVEL'] = 1;
    } else {
        $rec['MIN_MSG_LEVEL'] = gr('min_msg_level');
    }
    // автодополнение обьекта 
    if ($rec['LINKED_OBJECT'] != 'terminal_'.$rec['NAME']) {
        addClassObject('Terminals', 'terminal_'.$rec['NAME']);
        $rec['LINKED_OBJECT'] = 'terminal_'.$rec['NAME'];
    }
    $rec['HOST'] = gr('host');

    if (!$rec['HOST']) {
        $out['ERR_HOST'] = 1;
        $ok = 0;
    }

    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
            sg($rec['LINKED_OBJECT'] .'.linkedRoom',$out['LOCATION_TITLE']);
            sg($rec['LINKED_OBJECT'] .'.TerminalState', 0);
            sg($rec['LINKED_OBJECT'] .'.name', $rec['NAME']);
            sg($rec['LINKED_OBJECT'] .'.username', $out['USER_NAME']);
            sg($rec['LINKED_OBJECT'] .'.UPNP_CONTROL_ADDRESS',gr('tts_control_address'));
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}

if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);

$out['TTS_ADDONS'] = array();
if (is_dir(DIR_MODULES . 'terminals/tts')) {
    include_once(DIR_MODULES . 'terminals/tts_addon.class.php');
    $addons = scandir(DIR_MODULES . 'terminals/tts');
    if (is_array($addons)) {
        foreach ($addons as $addon_file) {
            $addon_file = DIR_MODULES . 'terminals/tts/' . $addon_file;
            if (is_file($addon_file)) {
                if (strtolower(substr($addon_file, -10)) == '.addon.php') {
                    $addon_name = basename($addon_file, '.addon.php');
                    include_once($addon_file);
                    if (class_exists($addon_name)) {
                        if (is_subclass_of($addon_name, 'tts_addon', TRUE)) {
                            if ($tts = new $addon_name(NULL)) {
                                // Results
                                $out['TTS_ADDONS'][] = array(
                                    'TITLE' => $tts->title,
                                    'NAME' => $addon_name,
                                    'DESCRIPTION' => $tts->description,
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}


if (is_dir(DIR_MODULES . 'app_player/addons')) {
    include_once(DIR_MODULES . 'app_player/addons.php');
    $addons = scandir(DIR_MODULES . 'app_player/addons');
    if (is_array($addons)) {
        foreach ($addons as $addon_file) {
            $addon_file = DIR_MODULES . 'app_player/addons/' . $addon_file;
            if (is_file($addon_file)) {
                if (strtolower(substr($addon_file, -10)) == '.addon.php') {
                    $addon_name = basename($addon_file, '.addon.php');
                    include_once($addon_file);
                    if (class_exists($addon_name)) {
                        if (is_subclass_of($addon_name, 'app_player_addon', TRUE)) {
                            if ($player = new $addon_name(NULL)) {
                                // Get player features
                                $features = array();
                                $reflection = new ReflectionClass($player);
                                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                                    if ($method->getDeclaringClass()->getName() == $reflection->getName()) {
                                        $method_name = $method->getName();
                                        if (substr($method_name, 0, 2) != '__' and !in_array($method_name, array('destroy', 'command'))) {
                                            $features[] = $method_name;
                                        }
                                    }
                                }
                                if (count($features)) {
                                    $player->description .= '<p><b>' . LANG_FEATURES_SUPPORTED . ':</b> ' . implode(', ', $features) . '.</p>';
                                } else {
                                    $player->description .= '<p style="color: #b94a48;"><b>' . LANG_NO_FEATURES_WARNING . '</b></p>';
                                }
                                // Results
                                $out['PLAYER_ADDONS'][] = array(
                                    'TITLE' => $player->title,
                                    'VALUE' => $addon_name,
                                    'DESCRIPTION' => $player->description,
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}

$out['STT_ADDONS'] = array();
if (is_dir(DIR_MODULES . 'terminals/stt')) {
    include_once(DIR_MODULES . 'terminals/stt_addon.class.php');
    $addons = scandir(DIR_MODULES . 'terminals/stt');
    if (is_array($addons)) {
        foreach ($addons as $addon_file) {
            $addon_file = DIR_MODULES . 'terminals/stt/' . $addon_file;
            if (is_file($addon_file)) {
                if (strtolower(substr($addon_file, -10)) == '.addon.php') {
                    $addon_name = basename($addon_file, '.addon.php');
                    include_once($addon_file);
                    if (class_exists($addon_name)) {
                        if (is_subclass_of($addon_name, 'stt_addon', TRUE)) {
                            if ($stt = new $addon_name(NULL)) {
                                // Results
                                $out['STT_ADDONS'][] = array(
                                    'TITLE' => $stt->title,
                                    'NAME' => $addon_name,
                                    'DESCRIPTION' => $stt->description,
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}

?>
