<?php
/*
* @version 0.1 (auto-set)
*/


global $filter_name;
global $clear_codeeditor;

if (!isset($this->filter_name)) {
    $this->filter_name = '';
}

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
$filters = array('', 'system', 'behavior', 'hook', 'backup', 'remote', 'scenes', 'calendar', 'codeeditor','mail');
$total = count($filters);
for ($i = 0; $i < $total; $i++) {
    $rec = array();
    $rec['FILTER'] = $filters[$i];
    if ($rec['FILTER'] == $this->filter_name) {
        $rec['SELECTED'] = 1;
    }
    if (defined('LANG_SETTINGS_SECTION_' . strtoupper($rec['FILTER']))) {
        $rec['TITLE'] = constant('LANG_SETTINGS_SECTION_' . strtoupper($rec['FILTER']));
    } elseif ($rec['FILTER'] == 'system') {
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

include_once DIR_MODULES . 'settings/settings_structure.inc.php';
if ($this->filter_name == '') {
    $options = $settings_structure['default'];
} elseif (isset($settings_structure[$this->filter_name])) {
    $options = $settings_structure[$this->filter_name];
} else {
    $options = array();
}

foreach ($options as $k => $v) {
    $tmp = SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '" . $k . "'");
    if (!$tmp['ID']) {
        $tmp = array();
        $tmp['NAME'] = $k;
        $tmp['TITLE'] = isset($v['title']) ? $v['title'] : $k;
        $tmp['TYPE'] = isset($v['type']) ? $v['type'] : 'text';
        $tmp['DEFAULTVALUE'] = isset($v['default']) ? $v['default'] : '';
        $tmp['NOTES'] = isset($v['notes']) ? $v['notes'] : '';
        $tmp['DATA'] = isset($v['data']) ? $v['data'] : '';
        $tmp['PRIORITY'] = isset($v['priority']) ? $v['priority'] : 0;
        SQLInsert('settings', $tmp);
    }
}

global $session;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";

// search filters
if ($this->filter_name != '') {
    $qry .= " AND NAME LIKE '%" . DBSafe($this->filter_name) . "%'";
    $out['FILTER_NAME'] = $this->filter_name;
}


if (isset($this->filter_exname)) {
    $qry .= " AND NAME NOT LIKE '%" . DBSafe($this->filter_exname) . "%'";
    $out['FILTER_EXNAME'] = $this->filter_exname;
}

if (!$this->filter_name) {
    //$words=array('HP', 'PROFILE');
    foreach ($words as $wrd) {
        $qry .= " AND NAME NOT LIKE '%" . DBSafe($wrd) . "%'";
    }
}


if (isset($this->section_title)) {
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
    $sortby = isset($session->data['settings_sort']) ? $session->data['settings_sort'] : '';
} else {
    if (isset($session->data['settings_sort']) ? $session->data['settings_sort'] : '' == $sortby) {
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

if ($res[0]['ID']) {
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {

        // some action for every record if required
        if ($this->mode == 'update') {
            ${'value_' . $res[$i]['ID']} = gr('value_' . $res[$i]['ID']);
            ${'notes_' . $res[$i]['ID']} = gr('notes_' . $res[$i]['ID']);

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
                    $row = array('OPTION_TITLE' => $k, 'FILTER' => isset($v['filter']) ? $v['filter'] : '', 'PRIORITY' => isset($v['priority']) ? $v['priority'] : 0);
                    $res[$i]['OPTIONS'][] = $row;
                }
            }
            if (isset($res[$i]['OPTIONS']) && is_array($res[$i]['OPTIONS'])) {
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
        if ($res[$i]['VALUE']) {
            $res[$i]['VALUE'] = htmlspecialchars($res[$i]['VALUE']);
        }
        $res[$i]['HINT_NAME'] = 'settings' . str_replace('_', '', $res[$i]['NAME']);
    }
    $out['RESULT'] = $res;
}


// some action for every record if required
if ($this->mode == 'update') {
    if ($this->filter_name == 'system' && file_exists(ROOT . 'scripts/cycle_db_save.php')) {
        $service = 'cycle_db_save';
        sg($service . 'Run', '');
        sg($service . 'Control', 'restart');
    }
    $this->redirect("?updated=1&filter_name=" . $this->filter_name);
}
