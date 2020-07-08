<?php
/*
* @version 0.1 (auto-set)
*/


global $filter_name;
global $clear_codeeditor;

if ($this->filter_name) {
    $out['FILTER_SET'] = $this->filter_name;
}

if ($filter_name) {
    $this->filter_name = $filter_name;
}

if ($clear_codeeditor) {
    $this->clear_codeeditor = $clear_codeeditor;
}


$sections = array();
$filters = array('', 'system', 'behavior', 'hook', 'backup', 'scenes', 'calendar', 'codeeditor');
$total = count($filters);
for ($i = 0; $i < $total; $i++) {
    $rec = array();
    $rec['FILTER'] = $filters[$i];
    if ($rec['FILTER'] == $this->filter_name) {
        $rec['SELECTED'] = 1;
    }
    if (defined('LANG_SETTINGS_SECTION_' . strtoupper($rec['FILTER']))) {
        $rec['TITLE'] = constant('LANG_SETTINGS_SECTION_' . strtoupper($rec['FILTER']));
    } elseif ($rec['FILTER']=='system') {
        $rec['TITLE'] = LANG_SECTION_SYSTEM;
    } else {
        $rec['TITLE'] = ucfirst($rec['FILTER']);
    }
    $sections[] = $rec;
    if ($filters[$i]) {
        $words[] = $filters[$i];
    }
}
$out['SECTIONS'] = $sections;

if ($this->filter_name == '' && !defined('SETTINGS_GENERAL_ALICE_NAME')) {
    $options = array(
        'GENERAL_ALICE_NAME' => 'Computer\'s name'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'text';
            $tmp['DEFAULTVALUE'] = '';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == '' && !defined('SETTINGS_GENERAL_START_LAYOUT')) {
    $options = array(
        'GENERAL_START_LAYOUT' => 'Homepage Layout'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'select';
            $tmp['DEFAULTVALUE'] = '';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '=Default|homepages=Home Pages|menu=Menu|apps=Applications|cp=Control Panel';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'system' && !defined('SETTINGS_SYSTEM_DISABLE_DEBMES')) {
    $options = array(
        'SYSTEM_DISABLE_DEBMES' => 'Disable logging (DebMes)'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'onoff';
            $tmp['DEFAULTVALUE'] = '0';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'system' && !defined('SETTINGS_SYSTEM_DEBMES_PATH')) {
    $options = array(
        'SYSTEM_DEBMES_PATH' => 'Path to DebMes logs'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'text';
            $tmp['DEFAULTVALUE'] = '';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'system' && !defined('SETTINGS_SYSTEM_DB_MAIN_SAVE_PERIOD')) {
    $options = array(
        'SYSTEM_DB_MAIN_SAVE_PERIOD' => array(
            'TITLE' => 'Database save period (main data), minutes',
            'DEFAULTVALUE' => 15,
            'NOTES' => ''
        ),
        'SYSTEM_DB_HISTORY_SAVE_PERIOD' => array(
            'TITLE' => 'Database save period (history data), minutes',
            'DEFAULTVALUE' => 60,
            'NOTES' => ''
        )
    );

    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v['TITLE'];
            $tmp['TYPE'] = 'text';
            $tmp['DEFAULTVALUE'] = $v['DEFAULTVALUE'];
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'behavior' && !defined('SETTINGS_BEHAVIOR_NOBODYHOME_TIMEOUT')) {

    $options = array(
        'BEHAVIOR_NOBODYHOME_TIMEOUT' => array(
            'TITLE' => 'NobodyHome mode activation timeout (minutes)',
            'DEFAULTVALUE' => 60,
            'NOTES' => 'Set 0 to disable'
        )
    );

    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v['TITLE'];
            $tmp['TYPE'] = 'text';
            $tmp['DEFAULTVALUE'] = $v['DEFAULTVALUE'];
            $tmp['NOTES'] = $v['NOTES'];
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }

}

if ($this->filter_name == 'hook' && !defined('SETTINGS_HOOK_BARCODE')) {
    //SETTINGS_HOOK_BEFORE_PLAYSOUND
    //SETTINGS_HOOK_AFTER_PLAYSOUND
    $options = array(
        'HOOK_BARCODE' => 'Bar-code reading (code)',
        'HOOK_PLAYMEDIA' => 'Playmedia (code)',
        'HOOK_BEFORE_PLAYSOUND' => 'Before PlaySound (code)',
        'HOOK_AFTER_PLAYSOUND' => 'After PlaySound (code)'
    );

    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'text';
            $tmp['DEFAULTVALUE'] = '';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'codeeditor') {
	
	if(!defined('SETTINGS_CODEEDITOR_TURNONSETTINGS') || !defined('SETTINGS_CODEEDITOR_SHOWLINE') || 
		!defined('SETTINGS_CODEEDITOR_MIXLINE') || !defined('SETTINGS_CODEEDITOR_UPTOLINE') || 
		!defined('SETTINGS_CODEEDITOR_SHOWERROR') || !defined('SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES') || 
		!defined('SETTINGS_CODEEDITOR_WRAPLINES') || !defined('SETTINGS_CODEEDITOR_AUTOCOMPLETE') || 
		!defined('SETTINGS_CODEEDITOR_AUTOCOMPLETE_TYPE') || !defined('SETTINGS_CODEEDITOR_THEME') || !defined('SETTINGS_CODEEDITOR_AUTOSAVE')) {
			
			$cmd = "DELETE FROM `settings` WHERE `NAME` LIKE '%CODEEDITOR_%'";
			SQLExec($cmd);
	}
	
	$options = array(
		'CODEEDITOR_TURNONSETTINGS' => LANG_CODEEDITOR_TURNONSETTINGS,
        'CODEEDITOR_SHOWLINE' => LANG_CODEEDITOR_SHOWLINE,
        'CODEEDITOR_MIXLINE' => LANG_CODEEDITOR_MIXLINE,
        'CODEEDITOR_UPTOLINE' => LANG_CODEEDITOR_UPTOLINE,
        'CODEEDITOR_SHOWERROR' => LANG_CODEEDITOR_SHOWERROR,
        'CODEEDITOR_AUTOCLOSEQUOTES' => LANG_CODEEDITOR_AUTOCLOSEQUOTES,
        'CODEEDITOR_WRAPLINES' => LANG_CODEEDITOR_WRAPLINES,
        'CODEEDITOR_AUTOCOMPLETE' => LANG_CODEEDITOR_AUTOCOMPLETE,
        'CODEEDITOR_AUTOCOMPLETE_TYPE' => LANG_CODEEDITOR_AUTOCOMPLETE_TYPE,
		'CODEEDITOR_THEME' => LANG_CODEEDITOR_THEME,
		'CODEEDITOR_AUTOSAVE' => LANG_CODEEDITOR_AUTOSAVE,
    );
	
	
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
			$tmp['DATA'] = '';
			$tmp['DEFAULTVALUE'] = '';
			
			if ($k == 'CODEEDITOR_SHOWLINE') {
				$tmp['TYPE'] = 'select';
				$tmp['DATA'] = '10=10|35=35|45=45|100=100|500=500|1000=1000|99999='.LANG_CODEEDITOR_BYCODEHEIGHT;
			} elseif ($k == 'CODEEDITOR_MIXLINE') {
				$tmp['TYPE'] = 'select';
				$tmp['DATA'] = '5=5|10=10|25=25|40=40|1='.LANG_CODEEDITOR_BYCODEHEIGHT;
			} elseif ($k == 'CODEEDITOR_AUTOSAVE') {
				$tmp['TYPE'] = 'select';
				$tmp['DATA'] = '0='.LANG_CODEEDITOR_AUTOSAVE_PARAMS_ONLY_HANDS.'|5='.LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_5.'|10='.LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_10.'|15='.LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_15.'|30='.LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_30.'|60='.LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_60;
			} elseif ($k == 'CODEEDITOR_THEME') {
				$tmp['TYPE'] = 'select';
				$tmp['DATA'] = 'codemirror='.LANG_DEFAULT.'|smoke_theme=SmoKE xD Theme|ambiance=Ambiance|base16-light=base16-light|dracula=Dracula|icecoder=Icecoder|material=Material|moxer=Moxer|neat=Neat';
				$tmp['DEFAULTVALUE'] = 'codemirror';
			} elseif ($k == 'CODEEDITOR_AUTOCOMPLETE_TYPE') {
				$tmp['TYPE'] = 'select';
				$tmp['DATA'] = 'none='.LANG_DEFAULT.'|php='.LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_ONLYPHP.'|phpmjdm='.LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_PHPMJDM.'|mjdmuser='.LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_MJDMUSER.'|user='.LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_USER.'|all='.LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_PHPMJDMUSER.'';
				$tmp['DEFAULTVALUE'] = 'codemirror';
			} else {
				$tmp['TYPE'] = 'onoff';
			}
			
			
            $tmp['NOTES'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'scenes' && !defined('SETTINGS_SCENES_VERTICAL_NAV')) {
    $options = array(
        'SCENES_VERTICAL_NAV' => 'Vertical navigation'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'onoff';
            $tmp['DEFAULTVALUE'] = '0';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }
}

if ($this->filter_name == 'scenes' && !defined('SETTINGS_SCENES_BACKGROUND_VIDEO')) {

    $options = array(
        'SCENES_BACKGROUND' => 'Path to background',
        'SCENES_BACKGROUND_VIDEO' => 'Path to video background',
        'SCENES_CLICKSOUND' => 'Path to click-sound file'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'path';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }

    $options = array(
        'SCENES_BACKGROUND_FIXED' => 'Backround Fixed',
        'SCENES_BACKGROUND_NOREPEAT' => 'Background No repeat'
    );

    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'onoff';
            $tmp['DEFAULTVALUE'] = '0';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }


}

if ($this->filter_name == 'backup' && !defined('SETTINGS_BACKUP_PATH')) {

    $options = array(
        'BACKUP_PATH' => 'Path to store backup'
    );
    foreach ($options as $k => $v) {
        $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
        if (!$tmp['ID']) {
            $tmp = array();
            $tmp['NAME'] = $k;
            $tmp['TITLE'] = $v;
            $tmp['TYPE'] = 'text';
            $tmp['NOTES'] = '';
            $tmp['DATA'] = '';
            SQLInsert('settings', $tmp);
        }
    }

}

// if (!empty($options)) {
// }


global $session;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";
// search filters


// search filters
if ($this->filter_name != '') {
    $qry .= " AND NAME LIKE '%" . DBSafe($this->filter_name) . "%'";
    $out['FILTER_NAME'] = $this->filter_name;
}


if ($this->filter_exname != '') {
    $qry .= " AND NAME NOT LIKE '%" . DBSafe($this->filter_exname) . "%'";
    $out['FILTER_EXNAME'] = $this->filter_exname;
}

if (!$this->filter_name) {
    //$words=array('HP', 'PROFILE');
    foreach ($words as $wrd) {
        $qry .= " AND NAME NOT LIKE '%" . DBSafe($wrd) . "%'";
    }
}


if ($this->section_title != '') {
    $out['SECTION_TITLE'] = $this->section_title;


}

if (($this->filter_name == '') and ($this->name == 'settings')) {
    $qry .= " and NAME IN('GENERAL_START_LAYOUT','SCENES_WIDTH','SCENES_HEIGHT','VOICE_LANGUAGE','THEME','SPEAK_SIGNAL','HOOK_BEFORE_SAY',	
'HOOK_AFTER_SAY','BACKUP_PATH',	'GENERAL_ALICE_NAME','SITE_TIMEZONE','TTS_GOOGLE','SITE_LANGUAGE','HOOK_EVENT_SAY','HOOK_EVENT_HOURLY',
'HOOK_BARCODE',	'HOOK_PLAYMEDIA','HOOK_BEFORE_PLAYSOUND','HOOK_AFTER_PLAYSOUND','HOOK_EVENT_COMMAND','HOOK_EVENT_SAYREPLY','HOOK_EVENT_SAYTO','HOOK_EVENT_ASK')";
}

// QUERY READY

// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['settings_qry'];
} else {
    $session->data['settings_qry'] = $qry;
}
if (!$qry) $qry = "1";
// FIELDS ORDER

// FIELDS ORDER
global $sortby;
if (!$sortby) {
    $sortby = $session->data['settings_sort'];
} else {
    if ($session->data['settings_sort'] == $sortby) {
        if (Is_Integer(strpos($sortby, ' DESC'))) {
            $sortby = str_replace(' DESC', '', $sortby);
        } else {
            $sortby = $sortby . " DESC";
        }
    }
    $session->data['settings_sort'] = $sortby;
}

$sortby = "PRIORITY DESC, NAME";

$out['SORTBY'] = $sortby;
// SEARCH RESULTS

// SEARCH RESULTS


$sql = "SELECT * FROM settings WHERE $qry ORDER BY $sortby";
$res = SQLSelect($sql);
debmes($sql, 'settings');

if ($res[0]['ID']) {
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        // some action for every record if required


        // some action for every record if required
        if ($this->mode == 'update') {
            global ${'value_' . $res[$i]['ID']};
            global ${'notes_' . $res[$i]['ID']};

            if ($res[$i]['TYPE'] == 'json' && preg_match('/^hook/is', $res[$i]['NAME'])) {
                $data = json_decode($res[$i]['VALUE'], true);
                foreach ($data as $k => $v) {
                    $data[$k]['filter'] = gr($k . '_' . $res[$i]['ID'] . '_filter');
                    if ($data[$k]['filter'] == '') {
                        unset($data[$k]['filter']);
                    }
                    $data[$k]['priority'] = gr($k . '_' . $res[$i]['ID'] . '_priority', 'int');
                }
                ${'value_' . $res[$i]['ID']} = json_encode($data);
            }

            if (!isset(${'value_' . $res[$i]['ID']})) continue;
            $all_settings[$res[$i]['NAME']] = ${'value_' . $res[$i]['ID']};
            $res[$i]['VALUE'] = ${'value_' . $res[$i]['ID']};
            $res[$i]['NOTES'] = htmlspecialchars(${'notes_' . $res[$i]['ID']});
            SQLUpdate('settings', $res[$i]);
        }
        if ($this->mode == 'reset') {
            $res[$i]['VALUE'] = $res[$i]['DEFAULTVALUE'];
            SQLUpdate('settings', $res[$i]);
        }

        if ($res[$i]['TYPE'] == 'select') {
            $data = explode('|', $res[$i]['DATA']);
            foreach ($data as $v) {
                list($ov, $ot) = explode('=', $v);
                $res[$i]['OPTIONS'][] = array('OPTION_TITLE' => $ot, 'OPTION_VALUE' => $ov);
            }
        } elseif ($res[$i]['TYPE'] == 'json' && preg_match('/^hook/is', $res[$i]['NAME'])) {
            $data = json_decode($res[$i]['VALUE'], true);
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $row = array('OPTION_TITLE' => $k, 'FILTER' => $v['filter'], 'PRIORITY' => (int)$v['priority']);
                    $res[$i]['OPTIONS'][] = $row;
                }
            }
            if (is_array($res[$i]['OPTIONS'])) {
                usort($res[$i]['OPTIONS'], function ($a, $b) {
                    if ($a['PRIORITY'] == $b['PRIORITY']) {
                        return 0;
                    }
                    return ($a['PRIORITY'] > $b['PRIORITY']) ? -1 : 1;
                });
            }

        }
        if ($res[$i]['VALUE'] == $res[$i]['DEFAULTVALUE']) {
            $res[$i]['ISDEFAULT'] = '1';
        }
        $res[$i]['VALUE'] = htmlspecialchars($res[$i]['VALUE']);
        $res[$i]['HINT_NAME'] = 'settings' . str_replace('_', '', $res[$i]['NAME']);
    }
    $out['RESULT'] = $res;
}


// some action for every record if required
if ($this->mode == 'update') {
    if ($this->filter_name == 'system' && file_exists(ROOT.'scripts/cycle_db_save.php')) {
        $service = 'cycle_db_save';
        sg($service . 'Run', '');
        sg($service . 'Control', 'restart');
    }
    $this->redirect("?updated=1&filter_name=" . $this->filter_name);
}
