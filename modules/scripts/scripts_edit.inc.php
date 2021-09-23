<?php
/*
* @version 0.2 (wizard)
*/
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'scripts';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

if(defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
	$out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
	$out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
	$out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
}
if ($this->mode == 'update') {

    //print_r($_REQUEST);exit;

    $ok = 1;
    //updating 'TITLE' (varchar)
    global $title;
    $rec['TITLE'] = $title;
    //updating 'CODE' (text)
    global $type;
    $rec['TYPE'] = (int)$type;

    global $category_id;
    $rec['CATEGORY_ID'] = $category_id;

    global $description;
    $rec['DESCRIPTION'] = $description;

    global $code;

    if ($rec['TYPE'] == 1) {
        global $xml;
        $rec['XML'] = $xml;
        global $blockly_code;
        $code = $blockly_code;
    }


    //echo $code;exit;

    $old_code=$rec['CODE'];
    $rec['CODE'] = $code;
	
    if ($rec['CODE'] != '') {
        //echo $content;
		
        $errors = php_syntax_error($rec['CODE']);
		
        if ($errors) {
            $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18))-2;
            $out['ERR_CODE'] = 1;
			$errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
			$errorStr = explode('Errors parsing', $errorStr[1]);
			$errorStr = explode(' in ', $errorStr[0]);
			//var_dump($errorStr);
            $out['ERRORS'] = $errorStr[0];
			$out['ERR_FULL'] = $errorStr[0].' '.$errorStr[1];
			$out['ERR_OLD_CODE'] = $old_code;
            $ok = 0;
        }
    }

    global $run_periodically;
    global $run_days;
    global $run_minutes;
    global $run_hours;	
 
    if ($run_periodically && isset($run_days)) {
        $rec['RUN_PERIODICALLY'] = (int)$run_periodically;
        $rec['RUN_DAYS'] = @implode(',', $run_days);
        if (is_null($rec['RUN_DAYS'])) {
            $rec['RUN_DAYS'] = '';
        }
        $rec['RUN_TIME'] = $run_hours . ':' . $run_minutes;
    } else {
        $rec['RUN_PERIODICALLY'] = 0;
        $rec['RUN_DAYS'] = false;
        $rec['RUN_TIME'] = false;
	}
	
    //$rec['EXECUTED']='0000-00-00 00:00:00';
    unset($rec['EXECUTED']);

    global $auto_link;
    $rec['AUTO_LINK']=(int)$auto_link;

    //UPDATING RECORD
    if ($ok) {
        $linked_object = '';
        $linked_property = '';

        if (!isset($_REQUEST['auto_link']) || $_REQUEST['auto_link']==1) {
            if (preg_match('/^if(.+?){/is', $rec['CODE'], $m)) {
                $conditions = trim($m[1], '()');
                if (preg_match('/getglobal\(["\'](\w+)\.(\w+)["\']\)/is', $conditions, $m2)) {
                    $linked_object=$m2[1];
                    $linked_property=$m2[2];
                } elseif (preg_match('/gg\(["\'](\w+)\.(\w+)["\']\)/is', $conditions, $m2)) {
                    $linked_object=$m2[1];
                    $linked_property=$m2[2];
                } elseif (preg_match('/timeis/is', $conditions) ||
                    preg_match('/timebefore/is', $conditions) ||
                    preg_match('/timeafter/is', $conditions) ||
                    preg_match('/timebetween/is', $conditions)) {
                    $linked_object='ClockChime';
                    $linked_property='time';
                }
            }

            if ($linked_object!='' && $linked_property!='') {
                $rec['AUTO_LINK_AVAILABLE']=1;
                $rec['AUTO_LINK']=1;
            } else {
                $rec['AUTO_LINK_AVAILABLE']=0;
            }
        } else {
            $rec['AUTO_LINK']=0;
        }

        $rec['LINKED_OBJECT'] = $linked_object;
        $rec['LINKED_PROPERTY'] = $linked_property;
        if ($linked_object != '' && $linked_property != '') {
            addLinkedProperty($linked_object, $linked_property, $this->name);
        }

        $rec['UPDATED']=date('Y-m-d H:i:s');

        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;

        global $edit_run;
        if ($edit_run) {
			echo '<div style="margin: 30px 0px;border: 1px solid #dddddd;padding: 10px;border-left: 10px solid #4d96d3;resize: vertical;height: 400px;min-height: 100px;overflow: auto;">
			<h3 style="margin: 0px;border-bottom: 1px solid #dddddd;padding-bottom: 5px;margin-bottom: 10px;">'.LANG_NEWDASH_RESULT.':</h3>
			<pre>';
			$this->runScript($rec['ID']);
			echo '</pre></div>';
           
			$rec['EDIT_RUN'] = $edit_run;
        }


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

if ($out['XML']) {
    $this->xml = $out['XML'];
}


$run_time = array('00', '00');
if ($rec['RUN_TIME']) {
    $run_time = explode(':', $rec['RUN_TIME']);
}

$total = 24;
for ($i = 0; $i < $total; $i++) {
    if ($i < 10) {
        $t = '0' . $i;
    } else {
        $t = $i;
    }
    $h = array('TITLE' => $t);
    if ($t == $run_time[0]) {
        $h['SELECTED'] = 1;
    }
    $out['HOURS'][] = $h;
}

$total = 60;
for ($i = 0; $i < $total; $i++) {
    if ($i < 10) {
        $t = '0' . $i;
    } else {
        $t = $i;
    }
    $m = array('TITLE' => $t);
    if ($t == $run_time[1]) {
        $m['SELECTED'] = 1;
    }
    $out['MINUTES'][] = $m;
}

$run_days = array();
if ($rec['RUN_DAYS']!=='') {
    $run_days = explode(',', $rec['RUN_DAYS']);
}

$days = array(LANG_WEEK_SUN, LANG_WEEK_MON, LANG_WEEK_TUE, LANG_WEEK_WED, LANG_WEEK_THU, LANG_WEEK_FRI, LANG_WEEK_SAT);
$total = 7;
for ($i = 0; $i < $total; $i++) {
    $d = array('TITLE' => $days[$i], 'VALUE' => $i);
    if (in_array($i, $run_days)) {
        $d['SELECTED'] = 1;
    }
    $out['DAYS'][] = $d;
}

if ($rec['CATEGORY_ID']) {
    $out['OTHER_SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts WHERE CATEGORY_ID='" . (int)$rec['CATEGORY_ID'] . "' ORDER BY TITLE");
}


$out['CATEGORIES'] = SQLSelect("SELECT * FROM script_categories ORDER BY TITLE");

if ($out['TITLE']) {
    $this->owner->data['TITLE'] = $out['TITLE'];
}
