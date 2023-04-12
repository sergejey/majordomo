<?php

$element_id = gr('element_id', 'int');
$element = SQLSelectOne("SELECT * FROM elements WHERE ID='" . (int)$element_id . "'");

if ($element['TYPE'] == 'widget') {
    $this->redirect("?id=" . $element['SCENE_ID'] . "&view_mode=" . $this->view_mode . "&tab=widgets&mode=edit_widget&element_id=" . $element['ID']);
}


$states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $element['ID'] . "'");

if (!$element['SCENE_ID']) {
    $out['ELEMENT_SCENE_ID'] = $rec['ID'];
}

$state_id = gr('state_id');

if ($state_id) {
    $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . (int)$state_id . "'");
    if (!$rec['ID']) {
        $state_id = '';
    }
} else {
    $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $element_id . "' ORDER BY ID");
    $state_id = $state_rec['ID'];
}


$state_clone = gr('state_clone');
if ($state_clone && $state_rec['ID']) {
    $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $state_id . "'");
    $state_rec['TITLE'] .= ' copy';
    unset($state_rec['ID']);
    $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
    $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&view_mode2=" . $view_mode2 . "&element_id=" . $element_id . "&state_id=" . $state_rec['ID']);
}


if ($this->mode == 'update') {
    $ok = 1;

    $element['TITLE'] = gr('title');
    if (!$element['TITLE']) {
        $ok = 0;
        $out['ERR_TITLE'] = 1;
    }

    $element['PRIORITY'] = gr('priority', 'int');
    $element['POSITION_TYPE'] = gr('position_type', 'int');
    if ($element['POSITION_TYPE'] == 0) {
        $linked_element_id = gr('linked_element_id', 'int');
        if ($linked_element_id == $element['ID']) {
            $linked_element_id = 0;
        }
        $element['LINKED_ELEMENT_ID'] = (int)$linked_element_id;
        $element['TOP'] = gr('top', 'int');
        $element['LEFT'] = gr('left', 'int');
    }

    $element['TYPE'] = gr('type');
    $element['APPEAR_ANIMATION'] = gr('appear_animation', 'int');
    $element['SMART_REPEAT'] = gr('smart_repeat', 'int');

    $element['S3D_SCENE'] = gr('s3d_scene', 'trim');

    $easy_config = gr('easy_config', 'int');
    if ($element['TYPE'] == 'switch'
        || $element['TYPE'] == 'informer'
        || $element['TYPE'] == 'warning'
        || $element['TYPE'] == 'menuitem'
        || $element['TYPE'] == 'object') {
        $element['EASY_CONFIG'] = (int)$easy_config;
    } else {
        $element['EASY_CONFIG'] = 0;
    }

    if ($element['TYPE'] == 'device' || $element['TYPE'] == 'widget') {
        $element['EASY_CONFIG'] = 1;
    }

    $element['DEVICE_ID'] = gr('device_id', 'int');
    $element['CLASS_TEMPLATE'] = gr('class_template');
    $element['LINKED_OBJECT'] = gr('linked_object', 'trim');
    $element['LINKED_PROPERTY'] = gr('linked_property', 'trim');
    $element['LINKED_METHOD'] = gr('linked_method', 'trim');

    $element['CSS_STYLE'] = gr('css_style');
    if (!$element['CSS_STYLE']) {
        $element['CSS_STYLE'] = 'default';
    }

    if ($element['TYPE'] != 'container') {
        $element['CONTAINER_ID'] = gr('container_id', 'int');
    } else {
        $element['CONTAINER_ID'] = 0;
    }

    $element['SCENE_ID'] = gr('scene_id', 'int');
    $element['HEIGHT'] = gr('height', 'int');
    $element['WIDTH'] = gr('width', 'int');
    if ($element['TYPE'] == 'menuitem' && !$element['WIDTH'] && !$element['HEIGHT']) {
        $element['HEIGHT'] = 0;
        $element['WIDTH'] = 200;
    }

    $element['BACKGROUND'] = gr('background', 'int');

    if (gr('use_javascript')) {
        $element['JAVASCRIPT'] = gr('javascript');
    } else {
        $element['JAVASCRIPT'] = '';
    }
    if (gr('use_css')) {
        $element['CSS'] = gr('css');
    } else {
        $element['CSS'] = '';
    }
    $element['CROSS_SCENE'] = gr('cross_scene', 'int');
    if ($ok) {
        $out['OK'] = 1;
        if ($element['ID']) {
            SQLUpdate('elements', $element);
        } else {
            $element['ID'] = SQLInsert('elements', $element);
        }
    }

    $state_title_new = gr('state_title_new');
    $html_new = gr('html_new');
    $image_new = gr('image_new');
    $script_id_new = gr('script_id_new', 'int');
    $menu_item_id_new = gr('menu_item_id_new', 'int');
    $action_object_new = gr('$action_object_new');
    $action_method_new = gr('action_method_new');
    $is_dynamic_new = gr('is_dynamic_new');
    $linked_object_new = gr('linked_object_new');
    $linked_property_new = gr('linked_property_new');
    $condition_new = gr('condition_new');
    $condition_value_new = gr('condition_value_new');
    $condition_advanced_new = gr('condition_advanced_new');
    $switch_scene_new = gr('switch_scene_new');
    $state_id = gr('state_id', 'int');
    $state_delete = gr('state_delete');
    $state_clone = gr('state_clone');
    $ext_url_new = gr('ext_url_new');
    $homepage_id_new = gr('homepage_id_new');
    $open_scene_id_new = gr('open_scene_id_new');
    $do_on_click_new = gr('do_on_click_new');
    $priority_new = gr('priority_new','int');
    $code_new = gr('code_new');
    $s3d_object_new = gr('s3d_object_new');
    $s3d_camera_new = gr('s3d_camera_new');

    if ($element['TYPE'] == 'container') {
        $state_title_new = 'Default';
    }

    if ($state_delete && $state_rec['ID']) {
        $state_rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $state_id . "'");
        foreach ($state_rec as $k => $v) {
            $out['STATE_' . $k] = '';
        }
        SQLExec("DELETE FROM elm_states WHERE ID='" . $state_rec['ID'] . "'");
        $this->redirect("?view_mode=".$this->view_mode."&id=".$this->id."&tab=".$this->tab."&view_mode2=edit_elements&element_id=".$element_id);
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

    } elseif ($element['TYPE'] == 'container' || $element['TYPE'] == 'html') {
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

        $linked_object = gr('linked_object');
        $linked_method = gr('linked_method');

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
        $linked_object = gr('linked_object');
        $linked_property = gr('linked_property');
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

        $menuitem_select_id = gr('$menuitem_select_id','int');
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
        $linked_object = gr('linked_object');
        $linked_property = gr('linked_property');
        $state_high = gr('state_high');
        $state_high_value = gr('state_high_value');
        $state_low = gr('state_low');
        $state_low_value = gr('state_low_value');
        $linked_property_unit = gr('linked_property_unit');

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
        $linked_object = gr('linked_object');
        if (!$linked_object) {
            $linked_object = 'myObject';
        }

    } elseif (($element['TYPE'] == 'switch') && (!$state_rec['ID'] || $element['EASY_CONFIG'])) {

        SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . (int)$element['ID']);
        $linked_object = gr('linked_object');

        if (!$linked_object) {
            $linked_object = 'myObject';
        }

        $state_rec = array();
        $state_rec['TITLE'] = 'off';
        $state_rec['HTML'] = $element['TITLE'];
        $state_rec['ELEMENT_ID'] = $element['ID'];
        $state_rec['IS_DYNAMIC'] = 1;
        $state_rec['LINKED_OBJECT'] = $linked_object;
        $state_rec['LINKED_PROPERTY'] = 'status';
        $state_rec['CONDITION'] = 4;
        $state_rec['CONDITION_VALUE'] = 1;
        $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'];
        $state_rec['ACTION_METHOD'] = 'turnOn';
        $state_rec['ID'] = SQLInsert('elm_states', $state_rec);

        $state_rec = array();
        $state_rec['TITLE'] = 'on';
        $state_rec['HTML'] = $element['TITLE'];
        $state_rec['ELEMENT_ID'] = $element['ID'];
        $state_rec['IS_DYNAMIC'] = 1;
        $state_rec['LINKED_OBJECT'] = $linked_object;
        $state_rec['LINKED_PROPERTY'] = 'status';
        $state_rec['CONDITION'] = 1;
        $state_rec['CONDITION_VALUE'] = 1;
        $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'];
        $state_rec['ACTION_METHOD'] = 'turnOff';
        $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
        $state_id = $state_rec['ID'];


    } elseif (($element['TYPE'] == 'mode') && !$state_rec['ID']) {

        $linked_object = gr('linked_object');
        if (!$linked_object) {
            $linked_object = 'myObject';
        }

        $state_rec = array();
        $state_rec['TITLE'] = 'off';
        $state_rec['HTML'] = $element['TITLE'];
        $state_rec['ELEMENT_ID'] = $element['ID'];
        $state_rec['IS_DYNAMIC'] = 1;
        $state_rec['LINKED_OBJECT'] = $linked_object;
        $state_rec['LINKED_PROPERTY'] = 'active';
        $state_rec['CONDITION'] = 4;
        $state_rec['CONDITION_VALUE'] = 1;
        $state_rec['ACTION_OBJECT'] = $state_rec['LINKED_OBJECT'];
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
