<?php
/*
* @version 0.2 (auto-set)
*/

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}

$table_name = 'objects';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

$device_rec=SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT='".$rec['TITLE']."'");
if ($device_rec['ID']) {
    $out['DEVICE_ID']=$device_rec['ID'];
    $out['DEVICE_TITLE']=$device_rec['TITLE'];
}

if ($this->mode == 'update') {
    $ok = 1;
    // step: default
    if ($this->tab == '') {
        //updating 'TITLE' (varchar, required)

        $rec['TITLE'] = gr('title','trim');
        $rec['TITLE'] = str_replace(' ', '', $rec['TITLE']);

        $tmp = SQLSelectOne("SELECT ID FROM objects WHERE TITLE LIKE '" . DBSafe($rec['TITLE']) . "' AND ID!=" . (int)$rec['ID']);
        if ($tmp['ID']) {
            $rec['TITLE'] = '';
        }

        if ($rec['TITLE'] == '') {
            $out['ERR_TITLE'] = 1;
            $ok = 0;
        }
        //updating 'Class' (select, required)
        global $class_id;
        if ($rec['CLASS_ID'] && $class_id != $rec['CLASS_ID']) {
            $class_changed_from = $rec['CLASS_ID'];
        }
        $rec['CLASS_ID'] = $class_id;
        if (!$rec['CLASS_ID']) {
            $out['ERR_CLASS_ID'] = 1;
            $ok = 0;
        }
        //updating 'Description' (text)
        global $description;
        $rec['DESCRIPTION'] = $description;
        //updating 'Location' (select)
        global $location_id;
        $rec['LOCATION_ID'] = (int)$location_id;

        global $keep_history;
        $rec['KEEP_HISTORY'] = (int)$keep_history;


    }
    // step: properties
    if ($this->tab == 'properties') {
    }
    // step: methods
    if ($this->tab == 'methods') {
    }
    // step: history
    if ($this->tab == 'history') {
    }
    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update

            if ($class_changed_from) {
                objectClassChanged($rec['ID']);
            }

        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        clearCacheData();
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}
// step: default
if ($this->tab == '') {
    //options for 'Class' (select)
    $tmp = SQLSelect("SELECT ID, TITLE FROM classes ORDER BY TITLE");
    $classes_total = count($tmp);
    for ($classes_i = 0; $classes_i < $classes_total; $classes_i++) {
        $class_id_opt[$tmp[$classes_i]['ID']] = $tmp[$classes_i]['TITLE'];
    }
    for ($i = 0; $i < $classes_total; $i++) {
        if ($rec['CLASS_ID'] == $tmp[$i]['ID']) $tmp[$i]['SELECTED'] = 1;
    }
    $out['CLASS_ID_OPTIONS'] = $tmp;
    //options for 'Location' (select)
    $tmp = SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE");
    $locations_total = count($tmp);
    for ($locations_i = 0; $locations_i < $locations_total; $locations_i++) {
        $location_id_opt[$tmp[$locations_i]['ID']] = $tmp[$locations_i]['TITLE'];
    }
    for ($i = 0; $i < $locations_total; $i++) {
        if ($rec['LOCATION_ID'] == $tmp[$i]['ID']) $tmp[$i]['SELECTED'] = 1;
    }
    $out['LOCATION_ID_OPTIONS'] = $tmp;
}
// step: properties
if ($this->tab == 'properties') {

    global $delete_prop;
    if ($delete_prop) {
        $pr = SQLSelectOne("SELECT * FROM properties WHERE ID='" . $delete_prop . "'");
        if ($pr['ID']) {
            $value = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . $delete_prop . "' AND OBJECT_ID='" . $rec['ID'] . "'");
            if ($value['ID']) {
                SQLExec("DELETE FROM phistory WHERE VALUE_ID='" . $value['ID'] . "'");
                SQLExec("DELETE FROM pvalues WHERE PROPERTY_ID='" . $delete_prop . "' AND OBJECT_ID='" . $rec['ID'] . "'");
            }
            if (!$pr['CLASS_ID']) {
                SQLExec("DELETE FROM properties WHERE ID='" . $delete_prop . "' AND OBJECT_ID='" . $rec['ID'] . "'");
            }
        }
        clearCacheData();
    }

    if ($this->mode == 'update') {
        clearCacheData();
        $new_property = gr('new_property','trim');
        $new_property = str_replace(' ','',$new_property);
        $new_value = gr('new_value');

        if ($new_property != '') {
            $tmp = array();
            $tmp['TITLE'] = $new_property;
            $tmp['OBJECT_ID'] = $rec['ID'];
            $tmp['ID'] = SQLInsert('properties', $tmp);
            if ($new_value != '') {
                setGlobal($rec['TITLE'] . '.' . $new_property, $new_value);
            }
        }
    }


    include_once(DIR_MODULES . 'classes/classes.class.php');
    $cl = new classes();
    $props = $cl->getParentProperties($rec['CLASS_ID'], '', 1);

    $my_props = SQLSelect("SELECT * FROM properties WHERE OBJECT_ID='" . $rec['ID'] . "'");
    if ($my_props[0]['ID']) {
        foreach ($my_props as $p) {
            $props[] = $p;
        }
    }

    $total = count($props);
    //print_R($props);exit;
    for ($i = 0; $i < $total; $i++) {
        if (!$props[$i]['KEEP_HISTORY'] && $rec['KEEP_HISTORY'] > 0) {
            $props[$i]['KEEP_HISTORY'] = $rec['KEEP_HISTORY'];
        }
        $value = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . $props[$i]['ID'] . "' AND OBJECT_ID='" . $rec['ID'] . "'");
        if ($this->mode == 'update') {
            global ${"value" . $props[$i]['ID']};
            if (isset(${"value" . $props[$i]['ID']})) {
                $this->class_id = $rec['CLASS_ID'];
                $this->id = $rec['ID'];
                $this->object_title = $rec['TITLE'];
                $this->setProperty($props[$i]['TITLE'], ${"value" . $props[$i]['ID']});
            }
        }
        $props[$i]['VALUE'] = $value['VALUE'];
        $props[$i]['VALUE_HTML'] = htmlspecialchars($props[$i]['VALUE']);
        $props[$i]['SOURCE'] = $value['SOURCE'];
        $props[$i]['UPDATED'] = date('d.m.Y H:i:s', strtotime($value['UPDATED']));
		
		$value['LINKED_MODULES'] = explode(',', $value['LINKED_MODULES']);
		if(is_array($value['LINKED_MODULES'])) {
			foreach($value['LINKED_MODULES'] as $prop_link) {
				if(!$prop_link) break; 
				$props[$i]['LINKED_MODULES'] .= '<span class="label label-success" style="margin-right: 3px;"><a style="color: white;text-decoration: none;" href="?(panel:{action='.$prop_link.'})&md='.$prop_link.'&go_linked_object='.urlencode($rec['TITLE']).'&go_linked_property='.urlencode($props[$i]['TITLE']).'">'.$prop_link.'</a></span>';
			}
		}
    }
    if ($this->mode == 'update') {
        $this->redirect("?view_mode=" . $this->view_mode . "&id=" . $rec['ID'] . "&tab=" . $this->tab);
    }
	
    $out['PROPERTIES'] = $props;
}
// step: methods
if ($this->tab == 'methods') {
	

    global $overwrite;
    global $delete_meth;
	
	if(defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
		$out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
		$out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
		$out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
	}
	
    if ($delete_meth) {
        $method = SQLSelectOne("SELECT * FROM methods WHERE ID='" . (int)$delete_meth . "'");
        $my_meth = SQLSelectOne("SELECT * FROM methods WHERE OBJECT_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($method['TITLE']) . "'");
        SQLExec("DELETE FROM methods WHERE OBJECT_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($method['TITLE']) . "'");
    }

    if ($overwrite) {
        global $method_id;
        $method = SQLSelectOne("SELECT * FROM methods WHERE ID='" . (int)$method_id . "'");

        if ($method['OBJECT_ID']) {
            $obj = SQLSelectOne("SELECT ID, CLASS_ID FROM objects WHERE ID='" . $method['OBJECT_ID'] . "'");
            $method = SQLSelectOne("SELECT * FROM methods WHERE TITLE LIKE '" . $method['TITLE'] . "' AND CLASS_ID='" . $obj['CLASS_ID'] . "'");
        }

        $out['METHOD_CLASS_ID'] = $method['CLASS_ID'];
        $tmp = SQLSelectOne("SELECT * FROM classes WHERE ID='" . $method['CLASS_ID'] . "'");
        $out['METHOD_CLASS_TITLE'] = $tmp['TITLE'];
        $out['METHOD_TITLE'] = $method['TITLE'];
        $out['METHOD_TITLE_URL'] = urlencode($method['TITLE']);
        $out['OBJECT_TITLE'] = $rec['TITLE'];
        $out['OBJECT_TITLE_URL'] = urlencode($rec['TITLE']);
        $out['METHOD_ID'] = $method['ID'];
        $my_meth = SQLSelectOne("SELECT * FROM methods WHERE OBJECT_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($method['TITLE']) . "'");

        if ($this->mode == 'update') {
            $ok = 1;
            global $code;
            global $call_parent;
            global $run_type;

			$old_code=$my_meth['CODE'];
			$my_meth['CODE'] = $code;
			
            $my_meth['CALL_PARENT'] = $call_parent;
            $my_meth['TITLE'] = $method['TITLE'];
            $my_meth['OBJECT_ID'] = $rec['ID'];

            if ($run_type == 'script') {
                global $script_id;
                $my_meth['SCRIPT_ID'] = $script_id;
            } else {
                $my_meth['SCRIPT_ID'] = 0;
            }

            if ($run_type == 'code' && $my_meth['CODE'] != '') {
                //echo $content;
                if (!defined('PYTHON_PATH') and !isItPythonCode($my_meth['CODE'])) {

           
                    $errors = php_syntax_error($my_meth['CODE']);
			
                    if ($errors) {
                        $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18))-2;
                        $out['ERR_CODE'] = 1;
                        $errorStr = explode('Parse error: ', str_replace("'", '', strip_tags(nl2br($errors))));
                        $errorStr = explode('Errors parsing', $errorStr[1]);
                        $errorStr = explode(' in ', $errorStr[0]);
                        //var_dump($errorStr);
                        $out['ERRORS'] = $errorStr[0];
                        $ok = 0;
                        $out['OK'] = $ok;
                        $out['ERR_OLD_CODE'] = $old_code;
                    }
                } else {
                    // chek python code
                }					
                $out['CODE'] = $my_meth['CODE'];
            }

            if ($ok) {
                if ($my_meth['ID']) {
                    SQLUpdate('methods', $my_meth);
                } else {
                    $my_meth['ID'] = SQLInsert('methods', $my_meth);
                }
                $out['OK'] = 1;

            }
        }
        if (!$my_meth['ID']) {
            $out['CALL_PARENT'] = 1;
        } else {
            $out['CODE'] = htmlspecialchars($my_meth['CODE']);
            $out['SCRIPT_ID'] = ($my_meth['SCRIPT_ID']);
            $out['CALL_PARENT'] = (int)($my_meth['CALL_PARENT']);
        }
        $out['OVERWRITE'] = 1;
    }

    include_once(DIR_MODULES . 'classes/classes.class.php');
    $cl = new classes();
    $methods = $cl->getParentMethods($rec['CLASS_ID'], '', 1);
    $total = count($methods);
    for ($i = 0; $i < $total; $i++) {
        $my_meth = SQLSelectOne("SELECT ID FROM methods WHERE OBJECT_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($methods[$i]['TITLE']) . "'");
        $obj_name = SQLSelectOne("SELECT TITLE FROM `objects` WHERE ID = {$rec['ID']}");
			 $methods[$i]['OBJECT_TITLE'] = $obj_name['TITLE'];
		if ($my_meth['ID']) {
            $methods[$i]['CUSTOMIZED'] = 1;
        }
    }
    $out['METHODS'] = $methods;

}
// step: history
if ($this->tab == 'history') {
}
if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);

if (!$rec['ID'] && $this->class_id) {
    $out['CLASS_ID'] = $this->class_id;
}

$out['SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

if ($out['TITLE']) {
    $this->owner->owner->data['TITLE'] = $out['TITLE'];
}
