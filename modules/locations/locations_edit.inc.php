<?php
/*
* @version 0.1 (auto-set)
*/

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'locations';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='" . (int)$id . "'");
if ($rec['ID']) {
    $locationObject = getRoomObjectByLocation($rec['ID'], 1);
    $out['LINKED_OBJECT']=$locationObject;
}

if ($this->tab == '') {
    if ($rec['ID']) {
        if (!defined('DISABLE_SIMPLE_DEVICES') || !DISABLE_SIMPLE_DEVICES) {
            require DIR_MODULES . 'devices/devices_structure.inc.php';
            $properties = $this->device_types['rooms']['PROPERTIES'];
            $res_properties = array();
            $onchanges = array();

            foreach ($properties as $k => $v) {
                if ($v['_CONFIG_TYPE']) {
                    if ($this->mode == 'update') {
                        global ${$k . '_value'};
                        if (isset(${$k . '_value'})) {
                            if (is_array(${$k . '_value'})) {
                                $value = implode(',', ${$k . '_value'});
                            } else {
                                $value = trim(${$k . '_value'});
                            }
                            setGlobal($locationObject . '.' . $k, $value);
                        }
                        $out['OK'] = 1;
                        if ($v['ONCHANGE'] != '') {
                            $onchanges[$v['ONCHANGE']] = 1;
                        }
                    }
                    $v['NAME'] = $k;
                    if (isset($v['_CONFIG_HELP'])) $v['CONFIG_HELP'] = $v['_CONFIG_HELP'];
                    $v['CONFIG_TYPE'] = $v['_CONFIG_TYPE'];
                    $v['VALUE'] = getGlobal($locationObject . '.' . $k);
                    if ($v['CONFIG_TYPE'] == 'select' || $v['CONFIG_TYPE'] == 'multi_select') {
                        $selected_options = explode(',', gg($locationObject . '.' . $k));
                        $tmp = explode(',', $v['_CONFIG_OPTIONS']);
                        $total = count($tmp);
                        for ($i = 0; $i < $total; $i++) {
                            $data_s = explode('=', trim($tmp[$i]));
                            $value = $data_s[0];
                            if (isset($data_s[1])) {
                                $title = $data_s[1];
                            } else {
                                $title = $value;
                            }
                            $option = array('VALUE' => $value, 'TITLE' => $title);
                            if (in_array($value, $selected_options)) $option['SELECTED'] = 1;
                            $v['OPTIONS'][] = $option;
                        }
                    } elseif ($v['CONFIG_TYPE'] == 'style_image') {
                        include_once(DIR_MODULES . 'scenes/scenes.class.php');
                        $scene_class = new scenes();
                        $styles = $scene_class->getAllTypes();
                        $v['FOLDERS'] = $styles;
                    }
                    $res_properties[] = $v;
                }

                $out['PROPERTIES'] = $res_properties;
            }
        }
    }

    if ($this->mode == 'update') {
        $ok = 1;
        //updating 'Title' (varchar, required)
        global $title;
        $rec['TITLE'] = $title;

        global $priority;
        $rec['PRIORITY'] = (int)$priority;

        if ($rec['TITLE'] == '') {
            $out['ERR_TITLE'] = 1;
            $ok = 0;
        }
        //UPDATING RECORD
        if ($ok) {
            if ($rec['ID']) {
                SQLUpdate($table_name, $rec); // update
            } else {
                $new_rec = 1;
                $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
            }
            $out['OK'] = 1;
        } else {
            $out['ERR'] = 1;
        }
    }
}

if ($this->tab == 'logic' && $rec['ID']) {
    $method_name = gr('method');
    $object = getObject($locationObject);

    $methods = $object->getParentMethods($object->class_id, '', 1);
    $total = count($methods);

    if (!$method_name) {
        $method_name = $methods[0]['TITLE'];
    }
    $out['METHOD'] = $method_name;


    for ($i = 0; $i < $total; $i++) {
        if ($methods[$i]['TITLE'] == $out['METHOD']) {
            $methods[$i]['SELECTED'] = 1;
        }
        if ($methods[$i]['DESCRIPTION'] != '') {
            $methods[$i]['DESCRIPTION'] = $methods[$i]['TITLE'] . ' - ' . $methods[$i]['DESCRIPTION'];
        } else {
            $methods[$i]['DESCRIPTION'] = $methods[$i]['TITLE'];
        }
    }
    $out['METHODS'] = $methods;

    $method_id = $object->getMethodByName($method_name, $object->class_id, $object->id);


    $method_rec = SQLSelectOne("SELECT * FROM methods WHERE ID=" . (int)$method_id);

    if ($method_rec['OBJECT_ID'] != $object->id) {
        $method_rec = array();
        $method_rec['OBJECT_ID'] = $object->id;
        $method_rec['TITLE'] = $method_name;
        $method_rec['CALL_PARENT'] = 1;
        $method_rec['ID'] = SQLInsert('methods', $method_rec);
    }


    if (defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
        $out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
        $out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
        $out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
    }

    if ($this->mode == 'update') {
        $code = gr('code');
        $old_code = $method_rec['CODE'];
        $method_rec['CODE'] = $code;

        $ok = 1;
        if ($method_rec['CODE'] != '') {
            $errors = php_syntax_error($method_rec['CODE']);

            if ($errors) {
                $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18)) - 2;
                $out['ERR_CODE'] = 1;
                $errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
                $errorStr = explode('Errors parsing', $errorStr[1]);
                $errorStr = explode(' in ', $errorStr[0]);
                //var_dump($errorStr);
                $out['ERRORS'] = $errorStr[0];
                $out['ERR_FULL'] = $errorStr[0] . ' ' . $errorStr[1];
                $out['ERR_OLD_CODE'] = $old_code;
                $ok = 0;
            }
        }
        if ($ok) {
            SQLUpdate('methods', $method_rec);
            $out['OK'] = 1;
        } else {
            $out['ERR'] = 1;
        }
    }
    $out['CODE'] = htmlspecialchars($method_rec['CODE']);
    $out['OBJECT_ID'] = $method_rec['OBJECT_ID'];

    $parent_method_id = $object->getMethodByName($method_name, $object->class_id, 0);
    if ($parent_method_id) {
        $out['METHOD_ID'] = $parent_method_id;
    } else {
        $out['METHOD_ID'] = $method_rec['ID'];
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