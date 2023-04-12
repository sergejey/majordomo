<?php
/*
* @version 0.1 (wizard)
*/
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'devices';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if (!$id && gr('linked_object')) {
    $rec = SQLSelectOne("SELECT * FROM $table_name WHERE LINKED_OBJECT='" . DBSafe(gr('linked_object')) . "'");
}
if ($this->owner->print == 1) {
    $out['NO_NAV'] = 1;
}

if ($rec['LINKED_OBJECT'] != '') {
    $object_rec = SQLSelectOne("SELECT ID FROM objects WHERE TITLE='" . $rec['LINKED_OBJECT'] . "'");
    if ($object_rec['ID']) {
        $properties = SQLSelect("SELECT pvalues.*, properties.TITLE as PROPERTY FROM pvalues LEFT JOIN properties ON properties.ID=pvalues.PROPERTY_ID WHERE pvalues.OBJECT_ID=" . $object_rec['ID'] . " AND pvalues.LINKED_MODULES!='' ORDER BY UPDATED DESC");
        $total = count($properties);
        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $linked_modules = explode(',', $properties[$i]['LINKED_MODULES']);
                $properties[$i]['VALUE'] = htmlspecialchars($properties[$i]['VALUE']);
                $properties[$i]['LINKED_MODULES'] = array();
                foreach ($linked_modules as $module) {
                    $properties[$i]['LINKED_MODULES'][] = array('MODULE' => $module, 'PROPERTY' => $properties[$i]['PROPERTY'], 'OBJECT' => $rec['LINKED_OBJECT']);
                }
                $out['LINKED_PROPERTIES'] = $properties;
            }
        }
    }
}

$show_methods = array();
if ($rec['TYPE'] != '') {
    $methods = $this->getAllMethods($rec['TYPE']);
    if (is_array($methods)) {
        foreach ($methods as $k => $v) {
            if ($v['_CONFIG_SHOW']) {
                $v['NAME'] = $k;
                $show_methods[] = $v;
            }
        }
    }
}
if (isset($show_methods[0])) {
    usort($show_methods, function ($a, $b) {
        if ($a['_CONFIG_SHOW'] == $b['_CONFIG_SHOW']) {
            return 0;
        }
        return ($a['_CONFIG_SHOW'] > $b['_CONFIG_SHOW']) ? -1 : 1;
    });
    $out['SHOW_METHODS'] = $show_methods;
}

if (gr('ok_msg')) {
    $out['OK_MSG'] = gr('ok_msg');
}
if (gr('err_msg')) {
    $out['ERR_MSG'] = gr('err_msg');
}


if ($this->tab == 'logic') {

    $method_name = gr('method');
    if (!$method_name) {
        $method_name = 'logicAction';
    }

    $out['METHOD'] = $method_name;

    $object = getObject($rec['LINKED_OBJECT']);


    $methods = $object->getParentMethods($object->class_id, '', 1);
    $total = count($methods);
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
        global $code;

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

if ($this->tab == 'settings') {
    $properties = $this->getAllProperties($rec['TYPE']);
    //print_r($properties);exit;
    if ($rec['LINKED_OBJECT'] && is_array($properties)) {
        $res_properties = array();
        $onchanges = array();
        $apply_others = gr('apply_others');
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
                        setGlobal($rec['LINKED_OBJECT'] . '.' . $k, $value);
                        if (is_array($apply_others)) {
                            foreach ($apply_others as $other_dev) {
                                setGlobal($other_dev . '.' . $k, $value);
                                if ($v['_CONFIG_RESTRICTIONS'] && checkAccessDefined('prop_' . $k, $rec['ID'])) {
                                    $other_obj = getObject($other_dev);
                                    if (is_object($other_obj) && $other_obj->device_id) {
                                        checkAccessCopy('prop_' . $k, $rec['ID'], $other_obj->device_id);
                                    }
                                }
                            }
                        }
                    }
                    $out['OK'] = 1;
                    if ($v['ONCHANGE'] != '') {
                        $onchanges[$v['ONCHANGE']] = 1;
                    }
                }
                $v['NAME'] = $k;
                if (isset($v['_CONFIG_HELP'])) $v['CONFIG_HELP'] = $v['_CONFIG_HELP'];
                $v['CONFIG_TYPE'] = $v['_CONFIG_TYPE'];
                $v['VALUE'] = getGlobal($rec['LINKED_OBJECT'] . '.' . $k);
                if ($v['CONFIG_TYPE'] == 'select' || $v['CONFIG_TYPE'] == 'multi_select') {
                    $selected_options = explode(',', gg($rec['LINKED_OBJECT'] . '.' . $k));
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

                if ($v['_CONFIG_RESTRICTIONS'] && checkAccessDefined('prop_' . $v['NAME'], $rec['ID'])) {
                    $v['_CONFIG_RESTRICTIONS_SET'] = 1;
                }

                $res_properties[] = $v;
            }
        }
        if ($this->mode == 'update') {
            foreach ($onchanges as $k => $v) {
                callMethod($rec['LINKED_OBJECT'] . '.' . $k);
            }
            $this->homebridgeSync($rec['ID'], 1);
        }
        //print_r($res_properties);exit;
        $out['PROPERTIES'] = $res_properties;
    }
    $groups = $this->getAllGroups($rec['TYPE']);

    global $apply_groups;
    if ($this->mode == 'update') {
        if (!is_array($apply_groups)) {
            $apply_groups = array();
        }
    } else {
        $apply_groups = array();
    }

    $total = count($groups);

    $object_id = gg($rec['LINKED_OBJECT'] . '.object_id');

    if ($total > 0) {
        for ($i = 0; $i < $total; $i++) {
            $property_title = 'group' . $groups[$i]['SYS_NAME'];
            if ($this->mode == 'update') {
                if (in_array($groups[$i]['SYS_NAME'], $apply_groups)) {
                    sg($rec['LINKED_OBJECT'] . '.' . $property_title, 1);
                } elseif (gg($rec['LINKED_OBJECT'] . '.' . $property_title)) {
                    sg($rec['LINKED_OBJECT'] . '.' . $property_title, 0);
                    $property_id = current(SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID=" . (int)$object_id . " AND TITLE='" . DBSafe($property_title) . "'"));
                    if ($property_id) {
                        SQLExec("DELETE FROM pvalues WHERE PROPERTY_ID=" . $property_id . " AND OBJECT_ID=" . $object_id);
                        SQLExec("DELETE FROM properties WHERE ID=" . $property_id);
                    }
                    //echo $property_id;exit;
                }
            }
            if (gg($rec['LINKED_OBJECT'] . '.' . $property_title)) {
                $groups[$i]['SELECTED'] = 1;
            }
        }
        $out['GROUPS'] = $groups;
    }
}

if ($this->tab == 'interface') {
    if ($this->mode == 'update') {
        global $add_menu;
        global $add_menu_id;

        global $add_scene;
        global $add_scene_id;

        if (!$add_scene) {
            $add_scene_id = 0;
        }
        if (!$add_scene_id) {
            $add_scene = 0;
        }

        $out['ADD_MENU'] = $add_menu;
        $out['ADD_MENU_ID'] = $add_menu_id;
        $out['ADD_SCENE'] = $add_scene;
        $out['ADD_SCENE_ID'] = $add_scene_id;

        if ($out['ADD_MENU']) {
            $this->addDeviceToMenu($rec['ID'], $add_menu_id);
        }

        if ($out['ADD_SCENE'] && $out['ADD_SCENE_ID']) {
            $this->addDeviceToScene($rec['ID'], $add_scene_id);
        }

        $out['OK'] = 1;
    }

    $out['SCENES'] = SQLSelect("SELECT ID,TITLE FROM scenes ORDER BY TITLE");
    $menu_items = SQLSelect("SELECT ID, TITLE FROM commands ORDER BY PARENT_ID,TITLE");
    $res_items = array();
    $total = count($menu_items);
    for ($i = 0; $i < $total; $i++) {
        $sub = SQLSelectOne("SELECT ID FROM commands WHERE PARENT_ID=" . $menu_items[$i]['ID']);
        if ($sub['ID']) {
            $res_items[] = $menu_items[$i];
        }
    }
    $out['MENU'] = $res_items;

}


if ($this->tab == '') {

    for ($i = 1; $i < 100; $i++) {
        $out['PRIORITIES'][] = array('VALUE' => $i);
    }

    global $prefix;
    $out['PREFIX'] = $prefix;
    global $source_table;
    $out['SOURCE_TABLE'] = $source_table;
    global $source_table_id;
    $out['SOURCE_TABLE_ID'] = $source_table_id;
    global $type;
    $out['TYPE'] = $type;
    global $linked_object;
    if ($linked_object != '') {
        if (!getObject($linked_object)) {
            $linked_object = '';
        }
    }
    $out['LINKED_OBJECT'] = trim($linked_object);
    if ($out['LINKED_OBJECT'] && !$rec['ID']) {
        $old_rec = SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '" . DBSafe($out['LINKED_OBJECT']) . "'");
        if ($old_rec['ID']) {
            $rec = $old_rec;
        }
    }
    global $add_title;
    if ($add_title) {
        $out['TITLE'] = $add_title;
    }


    if ($out['SOURCE_TABLE'] && !$rec['ID']) {
        $qry_devices = 1;
        if ($out['TYPE']) {
            $qry_devices .= " AND devices.TYPE='" . DBSafe($out['TYPE']) . "'";
        }
        $existing_devices = SQLSelect("SELECT ID, TITLE FROM devices WHERE $qry_devices ORDER BY TITLE");
        if ($existing_devices[0]['ID']) {
            $out['SELECT_EXISTING'] = 1;
            $out['EXISTING_DEVICES'] = $existing_devices;
        }
    }


}

if ($this->tab == 'links') {
    include_once(dirname(__FILE__) . '/devices_links.inc.php');
}

if ($this->tab == 'schedule') {
    include_once(dirname(__FILE__) . '/devices_schedule.inc.php');
}

if ($this->mode == 'update' && $this->tab == '') {
    $ok = 1;
    $rec['TITLE'] = gr('title', 'trim');
    if ($rec['TITLE'] == '') {
        $out['ERR_TITLE'] = 1;
        $ok = 0;
    }

    $rec['ALT_TITLES'] = gr('alt_titles', 'trim');

    $rec['TYPE'] = $type;
    if ($rec['TYPE'] == '') {
        $out['ERR_TYPE'] = 1;
        $ok = 0;
    }

    global $location_id;
    $rec['LOCATION_ID'] = (int)$location_id;

    if (gr('favorite', 'int')) {
        $rec['FAVORITE'] = gr('favorite_priority', 'int');
    } else {
        $rec['FAVORITE'] = 0;
    }

    $rec['SYSTEM_DEVICE'] = gr('system_device', 'int');
    $rec['ARCHIVED'] = gr('archived', 'int');


    $rec['LINKED_OBJECT'] = $linked_object;
    if ($rec['LINKED_OBJECT'] && !$rec['ID']) {
        $other_device = SQLSelectOne("SELECT ID FROM devices WHERE LINKED_OBJECT LIKE '" . DBSafe($rec['LINKED_OBJECT']) . "'");
        if ($other_device['ID']) {
            $out['ERR_LINKED_OBJECT'] = 1;
            $ok = 0;
        }
    }

    global $add_object;
    $out['ADD_OBJECT'] = $add_object;
    if ($add_object) {
        $rec['LINKED_OBJECT'] = '';
    }


    //UPDATING RECORD
    if ($ok) {

        $this->renderStructure();

        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
            $added = 1;
        }

        if ($rec['LOCATION_ID']) {
            $location_title = getRoomObjectByLocation($rec['LOCATION_ID'], 1);
        }

        $out['OK'] = 1;

        $type_details = $this->getTypeDetails($rec['TYPE']);
        if (!$rec['LINKED_OBJECT'] && $out['ADD_OBJECT']) {
            $prefix = $out['PREFIX'] . ucfirst($rec['TYPE']);
            $new_object_title = $prefix . $this->getNewObjectIndex($type_details['CLASS'], $prefix);
            $object_id = addClassObject($type_details['CLASS'], $new_object_title, 'sdevice' . $rec['ID']);
            $rec['LINKED_OBJECT'] = $new_object_title;
            SQLUpdate('devices', $rec);
        }

        $object_id = addClassObject($type_details['CLASS'], $rec['LINKED_OBJECT'], 'sdevice' . $rec['ID']);
        $class_id = current(SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($type_details['CLASS']) . "'"));


        $object_rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . $object_id);
        $object_rec['DESCRIPTION'] = $rec['TITLE'];
        $object_rec['LOCATION_ID'] = $rec['LOCATION_ID'];
        $class_changed = 0;

        $class_2b_changed = 1;
        $tmp_class_id = $object_rec['CLASS_ID'];
        while (isset($tmp_class_id)) {
            if ($tmp_class_id == $class_id) {
                $class_2b_changed = 0;
                break;
            }
            $tmp = SQLSelectOne("SELECT PARENT_ID FROM classes WHERE ID=" . (int)$tmp_class_id);
            $tmp_class_id = (int)$tmp['PARENT_ID'];
        }
        if ($class_2b_changed) {
            //move object to new class
            $object_rec['CLASS_ID'] = $class_id;
            $class_changed = 1;
        }
        SQLUpdate('objects', $object_rec);
        if ($class_changed) {
            objectClassChanged($object_rec['ID']);
        }

        if ($location_title) {
            setGlobal($object_rec['TITLE'] . '.linkedRoom', $location_title);
        }

        if ($added && is_array($type_details['PROPERTIES'])) {
            foreach ($type_details['PROPERTIES'] as $property => $details) {
                if (isset($details['_CONFIG_DEFAULT'])) {
                    setGlobal($object_rec['TITLE'] . '.' . $property, $details['_CONFIG_DEFAULT']);
                }
            }
        }

        if ($added && $rec['TYPE'] == 'sensor_temp') {
            setGlobal($object_rec['TITLE'] . '.minValue', 16);
            setGlobal($object_rec['TITLE'] . '.maxValue', 25);
        }
        if ($added && $rec['TYPE'] == 'sensor_humidity') {
            setGlobal($object_rec['TITLE'] . '.minValue', 30);
            setGlobal($object_rec['TITLE'] . '.maxValue', 60);
        }

        clearCacheData();
        addToOperationsQueue('connect_sync_devices', 'required');

        if ($out['SOURCE_TABLE'] && $out['SOURCE_TABLE_ID']) {
            $this->addDeviceToSourceTable($out['SOURCE_TABLE'], $out['SOURCE_TABLE_ID'], $rec['ID']);
        }

        $this->homebridgeSync($rec['ID'], 1);

        if ($added) {
            $this->redirect("?view_mode=edit_devices&id=" . $rec['ID'] . "&tab=settings");
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

if (!$rec['LOCATION_ID']) {
    $rec['LOCATION_ID'] = gr('location_id', 'int');
}


outHash($rec, $out);


$types = array();
foreach ($this->device_types as $k => $v) {
    if ($v['TITLE']) {
        $types[] = array('NAME' => $k, 'TITLE' => $v['TITLE']);
    }
    if ($k == $rec['TYPE'] && $rec['TYPE'] != '') {
        $out['TYPE_TITLE'] = $v['TITLE'];
    }
}


if ($rec['LINKED_OBJECT']) {
    $processed = $this->processDevice($rec['ID']);
    $out['HTML'] = $processed['HTML'];
}

usort($types, function ($a, $b) {
    return strcmp($a['TITLE'], $b['TITLE']);
});
$out['TYPES'] = $types;

$out['LOCATIONS'] = SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE+0");


if ($rec['LOCATION_ID']) {
    $location_rec = SQLSelectOne("SELECT ID,TITLE FROM locations WHERE ID=" . $rec['LOCATION_ID']);
    $out['LOCATION_TITLE'] = processTitle($location_rec['TITLE']);
    $other_devices = SQLSelect("SELECT ID, TITLE, ARCHIVED FROM devices WHERE LOCATION_ID=" . (int)$rec['LOCATION_ID'] . " ORDER BY TITLE");
    $out['OTHER_DEVICES'] = $other_devices;
}

if ($rec['TYPE']) {
    $other_devices_type = SQLSelect("SELECT ID, TITLE, ARCHIVED, LINKED_OBJECT FROM devices WHERE TYPE='" . $rec['TYPE'] . "' ORDER BY TITLE");
    $out['OTHER_DEVICES_TYPE'] = $other_devices_type;
}
