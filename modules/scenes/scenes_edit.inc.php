<?php
/*
* @version 0.1 (wizard)
*/

global $view_mode2;
$out['VIEW_MODE2'] = $view_mode2;


if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'scenes';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");


global $open;
if ($open != '') {
    if ($open == 'new') {
        $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&view_mode2=edit_elements&element_id=&top=" . $_GET['top'] . "&left=" . $_GET['left']);
    }
    $element_id = 0;
    if (preg_match('/state_(\d+)/', $open, $m)) {
        $state = SQLSelectOne("SELECT ID, ELEMENT_ID FROM elm_states WHERE ID='" . (int)$m[1] . "'");
        $element_id = (int)$state['ELEMENT_ID'];
    } elseif (preg_match('/state_element_(\d+)/', $open, $m)) {
        $element_id = (int)$m[1];
    } elseif (preg_match('/container_(\d+)/', $open, $m)) {
        $element_id = (int)$m[1];
    }
    if ($element_id) {
        $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&view_mode2=edit_elements&element_id=" . $element_id);
    }
}

global $state_id;

if ($this->tab == 'devices') {
    include DIR_MODULES . 'scenes/devices.inc.php';
}


if ($view_mode2 == '') {

    if ($this->mode == 'update') {
        $ok = 1;
        // step: default
        if ($this->tab == '') {
            //updating 'TITLE' (varchar, required)
            global $title;
            $rec['TITLE'] = $title;
            if ($rec['TITLE'] == '') {
                $out['ERR_TITLE'] = 1;
                $ok = 0;
            }
            //updating 'BACKGROUND' (varchar)
            global $background;
            $rec['BACKGROUND'] = $background;

            global $wallpaper;
            $rec['WALLPAPER'] = $wallpaper;

            global $wallpaper_fixed;
            $rec['WALLPAPER_FIXED'] = (int)$wallpaper_fixed;

            global $wallpaper_norepeat;
            $rec['WALLPAPER_NOREPEAT'] = (int)$wallpaper_norepeat;

            global $auto_scale;
            $rec['AUTO_SCALE'] = (int)$auto_scale;


            //updating 'PRIORITY' (int)
            global $priority;
            $rec['PRIORITY'] = (int)$priority;

            global $hidden;
            $rec['HIDDEN'] = (int)$hidden;

            $rec['DEVICES_BACKGROUND']=gr('devices_background');

            // updating elements array
            /*
             global $elements;
             $elements = json_decode($elements, true);
             $elements = ($elements == null) ? array() : $elements;
             */
        }

        if ($this->tab == 'visual') {
            //updating visual
        }


        //UPDATING RECORD
        if ($ok) {
            if ($rec['ID']) {
                SQLUpdate($table_name, $rec); // update
                /*
                    foreach ($elements as $value) {
                           SQLUpdate('elements', $value);
                    }
                */
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

if ($view_mode2 == 'clone_elements') {
    global $element_id;
    $element = SQLSelectOne("SELECT * FROM elements WHERE ID='" . (int)$element_id . "'");
    $states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $element['ID'] . "'");
    unset($element['ID']);
    $element['TITLE'] = $element['TITLE'] . ' (copy)';
    $element['ID'] = SQLInsert('elements', $element);

    $total = count($states);
    for ($i = 0; $i < $total; $i++) {
        unset($states[$i]['ID']);
        $states[$i]['ELEMENT_ID'] = $element['ID'];
        SQLInsert('elm_states', $states[$i]);
    }
    $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab);
}


if ($view_mode2 == 'delete_elements') {
    global $element_id;
    $element = SQLSelectOne("SELECT * FROM elements WHERE ID='" . (int)$element_id . "'");
    if ($element['ID']) {
        $this->delete_elements($element['ID']);
        $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab);
    }
}

if ($view_mode2 == 'up_elements') {
    global $element_id;
    $this->reorder_elements($element_id, 'up');
    $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab);
}

if ($view_mode2 == 'down_elements') {
    global $element_id;
    $this->reorder_elements($element_id, 'down');
    $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab);
}


if ($view_mode2 == 'edit_elements') {
    global $element_id;
    $element = SQLSelectOne("SELECT * FROM elements WHERE ID='" . (int)$element_id . "'");
    $states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $element['ID'] . "'");

    if (!$element['SCENE_ID']) {
        $out['ELEMENT_SCENE_ID'] = $rec['ID'];
    }

    if ($state_id) {
        $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $state_id . "'");
        if (!$rec['ID']) {
            $state_id = '';
        }
    } else {
        $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $element_id . "' ORDER BY ID");
        $state_id = $state_rec['ID'];
    }


    global $state_clone;
    if ($state_clone && $state_rec['ID']) {
        $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $state_id . "'");
        $state_rec['TITLE'] .= ' copy';
        unset($state_rec['ID']);
        $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
        $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&view_mode2=" . $view_mode2 . "&element_id=" . $element_id . "&state_id=" . $state_rec['ID']);
    }


    if ($this->mode == 'update') {
        $ok = 1;
        global $title;
        $element['TITLE'] = $title;
        if (!$element['TITLE']) {
            $ok = 0;
            $out['ERR_TITLE'] = 1;
        }

        global $priority;
        $element['PRIORITY'] = (int)$priority;


        global $position_type;
        $element['POSITION_TYPE'] = (int)$position_type;


        if ($element['POSITION_TYPE'] == 0) {
            global $linked_element_id;
            if ($linked_element_id == $element['ID']) {
                $linked_element_id = 0;
            }
            $element['LINKED_ELEMENT_ID'] = (int)$linked_element_id;

            global $top;
            $element['TOP'] = (int)$top;

            global $left;
            $element['LEFT'] = (int)$left;
        }

        global $type;
        $element['TYPE'] = $type;

        global $appear_animation;
        $element['APPEAR_ANIMATION'] = (int)$appear_animation;

        global $smart_repeat;
        $element['SMART_REPEAT'] = (int)$smart_repeat;

        global $s3d_scene;
        $element['S3D_SCENE'] = $s3d_scene . '';


        global $easy_config;
        if ($element['TYPE'] == 'switch' || $element['TYPE'] == 'informer' || $element['TYPE'] == 'warning' || $element['TYPE'] == 'menuitem' || $element['TYPE'] == 'object') {
            $element['EASY_CONFIG'] = (int)$easy_config;
        } else {
            $element['EASY_CONFIG'] = 0;
        }

        if ($element['TYPE'] == 'device') {
            $element['EASY_CONFIG'] = 1;
        }

        $element['DEVICE_ID'] = gr('device_id', 'int');
        $element['CLASS_TEMPLATE'] = gr('class_template');

        global $linked_object;
        $element['LINKED_OBJECT'] = $linked_object . '';

        global $linked_property;
        $element['LINKED_PROPERTY'] = $linked_property . '';

        global $linked_method;
        $element['LINKED_METHOD'] = $linked_method . '';


        global $css_style;
        $element['CSS_STYLE'] = $css_style;
        if (!$element['CSS_STYLE']) {
            $element['CSS_STYLE'] = 'default';
        }

        global $container_id;
        if ($element['TYPE'] != 'container') {
            $element['CONTAINER_ID'] = (int)$container_id;
        } else {
            $element['CONTAINER_ID'] = 0;
        }


        global $scene_id;
        $element['SCENE_ID'] = $scene_id;

        global $height;
        $element['HEIGHT'] = (int)$height;

        global $width;
        $element['WIDTH'] = (int)$width;

        if ($element['TYPE'] == 'menuitem' && !$element['WIDTH'] && !$element['HEIGHT']) {
            $element['HEIGHT'] = 0;
            $element['WIDTH'] = 200;
        }


        global $background;
        $element['BACKGROUND'] = (int)$background;


        global $use_javascript;
        if ($use_javascript) {
            global $javascript;
            $element['JAVASCRIPT'] = $javascript;
        } else {
            $element['JAVASCRIPT'] = '';
        }

        global $use_css;
        if ($use_css) {
            global $css;
            $element['CSS'] = $css;
        } else {
            $element['CSS'] = '';
        }


        global $cross_scene;
        $element['CROSS_SCENE'] = (int)$cross_scene;

        //$element['SCENE_ID']=$rec['ID'];

        if ($ok) {
            $out['OK'] = 1;
            if ($element['ID']) {
                SQLUpdate('elements', $element);
            } else {
                $element['ID'] = SQLInsert('elements', $element);
            }
        }

        global $state_title_new;
        global $html_new;
        global $image_new;
        global $script_id_new;
        global $menu_item_id_new;
        global $action_object_new;
        global $action_method_new;
        global $is_dynamic_new;
        global $linked_object_new;
        global $linked_property_new;
        global $condition_new;
        global $condition_value_new;
        global $condition_advanced_new;
        global $switch_scene_new;
        global $state_id;
        global $state_delete;
        global $state_clone;
        global $ext_url_new;
        global $homepage_id_new;
        global $open_scene_id_new;
        global $do_on_click_new;
        global $priority_new;
        global $code_new;
        global $s3d_object_new;
        global $s3d_camera_new;

        if ($element['TYPE'] == 'container') {
            $state_title_new = 'Default';
        }

        if ($state_delete && $state_rec['ID']) {

            $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $state_id . "'");
            foreach ($state_rec as $k => $v) {
                $out['STATE_' . $k] = '';
            }
            SQLExec("DELETE FROM elm_states WHERE ID='" . $state_rec['ID'] . "'");

        } elseif ($state_title_new && !$element['EASY_CONFIG']) {

            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['TITLE'] = $state_title_new;
            $state_rec['IMAGE'] = $image_new . '';
            $state_rec['HTML'] = $html_new . '';
            $state_rec['IS_DYNAMIC'] = $is_dynamic_new;
            $state_rec['LINKED_OBJECT'] = $linked_object_new . '';
            $state_rec['LINKED_PROPERTY'] = $linked_property_new . '';
            $state_rec['CONDITION'] = $condition_new;
            $state_rec['CONDITION_VALUE'] = $condition_value_new;
            $state_rec['CONDITION_ADVANCED'] = $condition_advanced_new;
            $state_rec['PRIORITY'] = (int)$priority_new;


            if ($do_on_click_new != 'run_script') {
                $script_id_new = 0;
            }
            if ($do_on_click_new != 'run_method') {
                $action_object_new = '';
                $action_method_new = '';
            }
            if ($do_on_click_new != 'open_menu') {
                $menu_item_id_new = 0;
            }
            if ($do_on_click_new != 'show_homepage') {
                $homepage_id_new = 0;
            }

            if ($do_on_click_new != 'show_scene') {
                $open_scene_id_new = 0;
            }

            if ($do_on_click_new != 'show_url') {
                $ext_url_new = '';
            }
            if ($do_on_click_new != 'run_code') {
                $code_new = '';
            }

            $state_rec['CODE'] = $code_new;
            $state_rec['SCRIPT_ID'] = $script_id_new;
            $state_rec['MENU_ITEM_ID'] = $menu_item_id_new;
            $state_rec['ACTION_OBJECT'] = $action_object_new;
            $state_rec['ACTION_METHOD'] = $action_method_new;
            $state_rec['HOMEPAGE_ID'] = (int)$homepage_id_new;
            $state_rec['OPEN_SCENE_ID'] = (int)$open_scene_id_new;
            $state_rec['EXT_URL'] = $ext_url_new;

            if ($state_rec['CONDITION_ADVANCED']) {
                $errors = php_syntax_error($state_rec['CONDITION_ADVANCED']);
                if ($errors) {
                    $state_rec['CONDITION_ADVANCED'] = '';;
                }
            }

            if ($element['TYPE'] == 's3d') {
                $state_rec['S3D_OBJECT'] = trim($s3d_object_new);
                $state_rec['S3D_CAMERA'] = trim($s3d_camera_new);
            }


            $state_rec['SWITCH_SCENE'] = (int)$switch_scene_new;

            if ($state_rec['ID']) {
                SQLUpdate('elm_states', $state_rec);
            } else {
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
                $state_id = $state_rec['ID'];
            }

        } elseif ($element['TYPE'] == 'container') {

            $state_rec['TITLE'] = 'default';
            $state_rec['ELEMENT_ID'] = $element['ID'];

            if ($state_rec['ID']) {
                SQLUpdate('elm_states', $state_rec);
            } else {
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
                $state_id = $state_rec['ID'];
            }

        } elseif (($element['TYPE'] == 'nav' || $element['TYPE'] == 'navgo') && !$state_rec['ID']) {

            $state_rec = array();
            $state_rec['TITLE'] = 'default';
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['HTML'] = $element['TITLE'];
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];

        } elseif ($element['TYPE'] == 'button' && !$state_rec['ID']) {

            global $linked_object;
            global $linked_method;

            $state_rec = array();
            $state_rec['TITLE'] = 'default';
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['HTML'] = $element['TITLE'];

            if ($linked_object && $linked_method) {
                $state_rec['ACTION_OBJECT'] = $linked_object;
                $state_rec['ACTION_METHOD'] = $linked_method;
            }

            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];


        } elseif (($element['TYPE'] == 'warning') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);
            global $linked_object;
            global $linked_property;
            $state_rec = array();
            $state_rec['TITLE'] = 'default';
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['HTML'] = $element['TITLE'] . '<br/>detected';
            $state_rec['LINKED_OBJECT'] = $linked_object . '';
            if (!$linked_property) {
                $linked_property = 'motionDetected';
            }
            $state_rec['LINKED_PROPERTY'] = $linked_property;
            $state_rec['IS_DYNAMIC'] = 1;
            $state_rec['CONDITION'] = 1;
            $state_rec['CONDITION_VALUE'] = 1;
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];


        } elseif (($element['TYPE'] == 'menuitem') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

            $wizard_data = array();

            global $menuitem_select_id;
            $wizard_data['MENU_ITEM_ID'] = (int)$menuitem_select_id;

            $element['WIZARD_DATA'] = json_encode($wizard_data) . '';

            SQLUpdate('elements', $element);

            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);

            $state_rec = array();
            $state_rec['TITLE'] = 'default';
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['HTML'] = '<iframe src="/menu.html?parent=' . (int)$wizard_data['MENU_ITEM_ID'] . '&from_scene=1" frameBorder="0" width="100%"></iframe>';
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];


        } elseif (($element['TYPE'] == 'informer') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);
            global $linked_object;
            global $linked_property;
            global $state_high;
            global $state_high_value;
            global $state_low;
            global $state_low_value;
            global $linked_property_unit;

            $wizard_data = array();
            $wizard_data['STATE_HIGH'] = (int)$state_high;
            if ($wizard_data['STATE_HIGH']) {
                $wizard_data['STATE_HIGH_VALUE'] = $state_high_value;
            }
            $wizard_data['STATE_LOW'] = (int)$state_low;
            if ($wizard_data['STATE_LOW']) {
                $wizard_data['STATE_LOW_VALUE'] = $state_low_value;
            }
            $wizard_data['UNIT'] = $linked_property_unit;

            $element['WIZARD_DATA'] = json_encode($wizard_data);
            SQLUpdate('elements', $element);


            if ($state_low_value != '' && !is_numeric($state_low_value) && !preg_match('/^%/', $state_low_value)) {
                $state_low_value = '%' . $state_low_value . '%';
            }
            if ($state_high_value != '' && !is_numeric($state_high_value) && !preg_match('/^%/', $state_high_value)) {
                $state_high_value = '%' . $state_high_value . '%';
            }


            if ($state_high) {
                $state_rec = array();
                $state_rec['TITLE'] = 'high';
                $state_rec['ELEMENT_ID'] = $element['ID'];
                $state_rec['HTML'] = '%' . $linked_object . '.' . $linked_property . '%';
                if ($linked_property_unit) {
                    $state_rec['HTML'] .= ' ' . $linked_property_unit;
                }
                $state_rec['LINKED_OBJECT'] = $linked_object . '';
                $state_rec['LINKED_PROPERTY'] = $linked_property . '';
                $state_rec['IS_DYNAMIC'] = 1;
                if ($state_high_value) {
                    $state_rec['CONDITION'] = 2;
                    $state_rec['CONDITION_VALUE'] = $state_high_value;
                }
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            }

            if ($state_low) {
                $state_rec = array();
                $state_rec['TITLE'] = 'low';
                $state_rec['ELEMENT_ID'] = $element['ID'];
                $state_rec['HTML'] = '%' . $linked_object . '.' . $linked_property . '%';
                if ($linked_property_unit) {
                    $state_rec['HTML'] .= ' ' . $linked_property_unit;
                }
                $state_rec['LINKED_OBJECT'] = $linked_object . '';
                $state_rec['LINKED_PROPERTY'] = $linked_property . '';
                $state_rec['IS_DYNAMIC'] = 1;
                if ($state_low_value) {
                    $state_rec['CONDITION'] = 3;
                    $state_rec['CONDITION_VALUE'] = $state_low_value;
                }
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            }

            $state_rec = array();
            $state_rec['TITLE'] = 'default';
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['HTML'] = '%' . $linked_object . '.' . $linked_property . '%';
            if ($linked_property_unit) {
                $state_rec['HTML'] .= ' ' . $linked_property_unit;
            }
            if ($state_high || $state_low) {
                $state_rec['IS_DYNAMIC'] = 1;
                $state_rec['LINKED_OBJECT'] = $linked_object . '';
                $state_rec['LINKED_PROPERTY'] = $linked_property . '';
                //is_dynamic 2
                if ($state_high && $state_low) {
                    $state_rec['IS_DYNAMIC'] = 2;
                    $state_rec['CONDITION_ADVANCED'] = 'if (gg(\'' . $linked_object . '.' . $linked_property . '\')>=(float)\'' . $state_low_value . '\' && gg(\'' . $linked_object . '.' . $linked_property . '\')<=(float)\'' . $state_high_value . '\') {' . "\n " . '$display=1;' . "\n" . '} else {' . "\n " . '$display=0;' . "\n" . '}';
                } elseif ($state_high) {
                    $state_rec['IS_DYNAMIC'] = 1;
                    $state_rec['CONDITION'] = 3;
                    $state_rec['CONDITION_VALUE'] = $state_high_value;
                } elseif ($state_low) {
                    $state_rec['IS_DYNAMIC'] = 1;
                    $state_rec['CONDITION'] = 2;
                    $state_rec['CONDITION_VALUE'] = $state_low_value;
                }
            }
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];


        } elseif (($element['TYPE'] == 'device') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);

        } elseif (($element['TYPE'] == 'object') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);
            global $linked_object;

            if (!$linked_object) {
                $linked_object = 'myObject';
            }


        } elseif (($element['TYPE'] == 'switch') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);
            global $linked_object;

            if (!$linked_object) {
                $linked_object = 'myObject';
            }

            $state_rec = array();
            $state_rec['TITLE'] = 'off';
            $state_rec['HTML'] = $element['TITLE'];
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['IS_DYNAMIC'] = 1;
            $state_rec['LINKED_OBJECT'] = $linked_object . '';
            $state_rec['LINKED_PROPERTY'] = 'status';
            $state_rec['CONDITION'] = 4;
            $state_rec['CONDITION_VALUE'] = 1;
            $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'] . '';
            $state_rec['ACTION_METHOD'] = 'turnOn';
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);


            $state_rec = array();
            $state_rec['TITLE'] = 'on';
            $state_rec['HTML'] = $element['TITLE'];
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['IS_DYNAMIC'] = 1;
            $state_rec['LINKED_OBJECT'] = $linked_object . '';
            $state_rec['LINKED_PROPERTY'] = 'status';
            $state_rec['CONDITION'] = 1;
            $state_rec['CONDITION_VALUE'] = 1;
            $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'] . '';
            $state_rec['ACTION_METHOD'] = 'turnOff';
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];


        } elseif (($element['TYPE'] == 'mode') && !$state_rec['ID']) {

            global $linked_object;

            if (!$linked_object) {
                $linked_object = 'myObject';
            }

            $state_rec = array();
            $state_rec['TITLE'] = 'off';
            $state_rec['HTML'] = $element['TITLE'];
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['IS_DYNAMIC'] = 1;
            $state_rec['LINKED_OBJECT'] = $linked_object . '';
            $state_rec['LINKED_PROPERTY'] = 'active';
            $state_rec['CONDITION'] = 4;
            $state_rec['CONDITION_VALUE'] = 1;
            $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'] . '';
            $state_rec['ACTION_METHOD'] = 'activate';
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);


            $state_rec = array();
            $state_rec['TITLE'] = 'on';
            $state_rec['HTML'] = $element['TITLE'];
            $state_rec['ELEMENT_ID'] = $element['ID'];
            $state_rec['IS_DYNAMIC'] = 1;
            $state_rec['LINKED_OBJECT'] = $linked_object . '';
            $state_rec['LINKED_PROPERTY'] = 'active';
            $state_rec['CONDITION'] = 1;
            $state_rec['CONDITION_VALUE'] = 1;
            $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'];
            $state_rec['ACTION_METHOD'] = 'deactivate';
            $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            $state_id = $state_rec['ID'];


        }


    }

    if (is_array($state_rec)) {
        foreach ($state_rec as $k => $v) {
            $out['STATE_' . $k] = htmlspecialchars($v);
        }
    }


    if (is_array($element)) {
        foreach ($element as $k => $v) {
            $out['ELEMENT_' . $k] = htmlspecialchars($v);
        }
        if ($element['CSS_STYLE'] != 'default') {
            $out['ELEMENT_CSS_IMAGE'] = $this->getCSSImage($element['TYPE'], $element['CSS_STYLE']);
        }
        if ($element['WIZARD_DATA'] != '') {
            $wizard_data = json_decode($element['WIZARD_DATA'], TRUE);
            foreach ($wizard_data as $k => $v) {
                $out['WIZARD_' . $k] = $v;
            }
        }
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
$this->owner->data['TITLE'] = $rec['TITLE'];
if ($out['ELEMENT_TITLE']) {
    $this->owner->data['TITLE'] .= ' - ' . $out['ELEMENT_TITLE'];
}

if ($element['TYPE']) {
    $styles = $this->getStyles($element['TYPE']);
    if (is_array($styles)) {
        $out['STYLES'] = $styles;
    }

    $styles = $this->getStyles('common');
    if (is_array($styles)) {
        $out['COMMON_STYLES'] = $styles;
    }

} else {
    $out['ELEMENT_TOP'] = $_GET['top'];
    $out['ELEMENT_LEFT'] = $_GET['left'];
}

if ($this->tab == 'elements') {
    $out['OTHER_SCENES'] = SQLSelect("SELECT ID, TITLE FROM scenes ORDER BY PRIORITY DESC, TITLE");
    $out['HOMEPAGES'] = SQLSelect("SELECT ID, TITLE FROM layouts ORDER BY TITLE");
    $out['SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
    $menu_items = SQLSelect("SELECT ID, TITLE, PARENT_ID FROM commands WHERE EXT_ID=0 ORDER BY PARENT_ID, TITLE");
    $titles = array();
    foreach ($menu_items as $k => $v) {
        $titles[$v['ID']] = $v['TITLE'];
    }
    $total = count($menu_items);
    for ($i = 0; $i < $total; $i++) {
        if ($titles[$menu_items[$i]['PARENT_ID']]) {
            $menu_items[$i]['TITLE'] = $titles[$menu_items[$i]['PARENT_ID']] . ' &gt; ' . $menu_items[$i]['TITLE'];
        }
    }
    $out['MENU_ITEMS'] = $menu_items;
    $out['STATES'] = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $element['ID'] . "' ORDER BY elm_states.PRIORITY DESC");
    $out['STATE_ID'] = $state_id;

    if ($element['TYPE'] == 's3d') {
        if (file_exists(ROOT . $element['S3D_SCENE'])) {
            $scene_text = LoadFile(ROOT . $element['S3D_SCENE']);
            $scene_data = json_decode($scene_text, true);
            if (is_array($scene_data['object']['children'])) {
                function processObjectsTree($objects, &$result)
                {
                    $total = count($objects);
                    for ($i = 0; $i < $total; $i++) {
                        if ($objects[$i]['name']) {
                            $result[] = array('TITLE' => $objects[$i]['name'], 'TYPE' => $objects[$i]['type']);
                        } else {
                            $result[] = array('TITLE' => $objects[$i]['uuid'], 'TYPE' => $objects[$i]['type']);
                        }
                        if (is_array($objects[$i]['children'])) {
                            processObjectsTree($objects[$i]['children'], $result);
                        }
                    }
                }

                $res = array();
                processObjectsTree($scene_data['object']['children'], $res);
                $out['S3D_OBJECTS'] = $res;
                $out['S3D_CAMERAS'] = array();
                foreach ($res as $k => $v) {
                    if (is_integer(strpos(strtolower($v['TYPE']), 'camera'))) {
                        $out['S3D_CAMERAS'][] = $v;
                    }
                }

            }
        }
    }

}

//$elements=SQLSelect("SELECT `ID`, `SCENE_ID`, `TITLE`, `TYPE`, `TOP`, `LEFT`, `WIDTH`, `HEIGHT`, `CROSS_SCENE`, PRIORITY, (SELECT `IMAGE` FROM elm_states WHERE elements.ID = elm_states.element_ID LIMIT 1) AS `IMAGE` FROM elements WHERE SCENE_ID='".$rec['ID']."' ORDER BY PRIORITY DESC, TITLE");

if ($element['ID']) {
    $elements = SQLSelect("SELECT `ID`, `SCENE_ID`, `TITLE`, `TYPE`, `TOP`, `LEFT`, `WIDTH`, `HEIGHT`, `CROSS_SCENE`, PRIORITY, (SELECT `IMAGE` FROM elm_states WHERE elements.ID = elm_states.element_ID LIMIT 1) AS `IMAGE` FROM elements WHERE SCENE_ID='" . $rec['ID'] . "' AND CONTAINER_ID=0 ORDER BY PRIORITY DESC, TITLE");
} else {
    $elements = $this->getElements("SCENE_ID='" . $rec['ID'] . "' AND CONTAINER_ID=0");
}
//
if (count($elements)) {
    $out['ELEMENTS'] = $elements;
}


if ($element['TYPE'] == 'container') {
    $sub_elements = SQLSelect("SELECT ID, TITLE FROM elements WHERE CONTAINER_ID=" . (int)$element['ID'] . " ORDER BY PRIORITY DESC, TITLE");
} elseif ($element['ID']) {
    $sub_elements = SQLSelect("SELECT ID, TITLE FROM elements WHERE CONTAINER_ID=" . (int)$element['CONTAINER_ID'] . " AND SCENE_ID='" . $rec['ID'] . "' ORDER BY PRIORITY DESC, TITLE");
}
if ($sub_elements[0]['ID']) {
    $out['SUB_ELEMENTS'] = $sub_elements;
}


$containers = SQLSelect("SELECT `ID`, `TITLE` FROM elements WHERE SCENE_ID='" . $rec['ID'] . "' AND TYPE='container' ORDER BY PRIORITY DESC, TITLE");
if ($element['CONTAINER_ID']) {
    $total = count($containers);
    for ($i = 0; $i < $total; $i++) {
        if ($containers[$i]['ID'] == $element['CONTAINER_ID']) {
            $out['CURRENT_CONTAINER_TITLE'] = $containers[$i]['TITLE'];
        }
    }
}


$out['CONTAINERS'] = $containers;

$out['SCENES'] = SQLSelect("SELECT * FROM scenes ORDER BY TITLE");
$out['DEVICES'] = SQLSelect("SELECT ID, TITLE FROM devices ORDER BY TITLE");


