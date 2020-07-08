<?php
/*
* @version 0.3 (auto-set)
*/

global $parent_id;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'commands';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if ($parent_id) {
    $rec['PARENT_ID'] = (int)$parent_id;
}

if ($rec['PARENT_ID']) {
    $out['HISTORY'] = $this->getParents($rec['PARENT_ID']);
}


if ($this->mode == 'update') {
    $ok = 1;
    if ($this->tab == '') {
        $rec['PARENT_ID'] = (int)$parent_id;
    }
    //updating 'TITLE' (varchar, required)
    global $title;
    $rec['TITLE'] = $title;
    if ($rec['TITLE'] == '') {
        $out['ERR_TITLE'] = 1;
        $ok = 0;
    }

    global $priority;
    $rec['PRIORITY'] = $priority;

    global $smart_repeat;
    $rec['SMART_REPEAT'] = (int)$smart_repeat;

    $rec['READ_ONLY']=gr('read_only','int');


    global $type;
    $rec['TYPE'] = $type . '';

    global $ext_id;
    $rec['EXT_ID'] = (int)$ext_id;

    global $inline;
    $rec['INLINE'] = (int)$inline;

    global $visible_delay;
    $rec['VISIBLE_DELAY'] = (int)$visible_delay;

    if ($rec['TYPE'] == 'plusminus' || $rec['TYPE'] == 'sliderbox') {
        global $min_value;
        if ($min_value != '') {
            $rec['MIN_VALUE'] = $min_value;
        } else {
            $rec['MIN_VALUE'] = 0;
        }

        global $max_value;
        if ($max_value != '') {
            $rec['MAX_VALUE'] = $max_value;
        } else {
            $rec['MAX_VALUE'] = 0;
        }

        global $step_value;
        if ($step_value != '') {
            $rec['STEP_VALUE'] = $step_value;
        } else {
            $rec['STEP_VALUE'] = 0;
        }
    }

    global $delete_icon;
    if ($delete_icon) {
        if ($rec['ICON'] != '') {
            @unlink(ROOT . 'cms/icons/' . $rec['ICON']);
        }
        $rec['ICON'] = "";
    }

    global $icon;
    global $icon_name;
    if ($icon != '') {
        if ($rec['ICON'] != '') {
            @unlink(ROOT . 'cms/icons/' . $rec['ICON']);
        }
        $rec['ICON'] = $rec['ID'] . '_' . $icon_name;
        copy($icon, ROOT . 'cms/icons/' . $rec['ICON']);
    }


    if ($rec['TYPE'] == 'selectbox' || $rec['TYPE'] == 'custom' || $rec['TYPE'] == 'switch' || $rec['TYPE'] == 'radiobox') {
        global $data;
        $rec['DATA'] = $data;
    }

    if ($rec['TYPE'] == 'plusminus'
        || $rec['TYPE'] == 'sliderbox'
        || $rec['TYPE'] == 'selectbox'
        || $rec['TYPE'] == 'button'
        || $rec['TYPE'] == 'switch'
        || $rec['TYPE'] == 'custom'
        || $rec['TYPE'] == 'timebox'
        || $rec['TYPE'] == 'datebox'
        || $rec['TYPE'] == 'textbox'
        || $rec['TYPE'] == 'radiobox'
        || $rec['TYPE'] == 'color'
        || $rec['TYPE'] == 'object'
    ) {
        global $cur_value;
        if ($cur_value != '') {
            $rec['CUR_VALUE'] = $cur_value;
        }

        $old_linked_object = $rec['LINKED_OBJECT'];
        $old_linked_property = $rec['LINKED_PROPERTY'];

        global $linked_object;
        $rec['LINKED_OBJECT'] = trim($linked_object);
        global $linked_property;
        $rec['LINKED_PROPERTY'] = trim($linked_property);

        /*
        global $onchange_object;
        $rec['ONCHANGE_OBJECT']=trim($onchange_object);
        */

        global $onchange_method;
        $rec['ONCHANGE_METHOD'] = trim($onchange_method);

        global $script_id;
        $rec['SCRIPT_ID'] = (int)$script_id;

        global $code;
        $rec['CODE'] = $code . '';
        if ($rec['CODE'] != '') {
            //echo $content;
            $errors = php_syntax_error($rec['CODE']);
            if ($errors) {
                $out['ERR_CODE'] = 1;
                $out['ERRORS'] = nl2br($errors);
                $ok = 0;
            }
        }

    }

    //updating 'COMMAND' (varchar)
    global $command;
    $rec['COMMAND'] = $command . '';

    global $window;
    $rec['WINDOW'] = $window . '';

    global $sub_preload;
    $rec['SUB_PRELOAD'] = (int)$sub_preload;


    //updating 'URL' (varchar)
    global $url;
    $rec['URL'] = $url . '';
    //updating 'WIDTH' (int)
    global $width;
    $rec['WIDTH'] = (int)$width;
    //updating 'HEIGHT' (int)
    global $height;
    $rec['HEIGHT'] = (int)$height;

    global $autostart;
    $rec['AUTOSTART'] = (int)$autostart;

    global $auto_update;
    $rec['AUTO_UPDATE'] = (int)$auto_update;

    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $this->updateTree_commands();
        $out['OK'] = 1;

        if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
            addLinkedProperty($rec['LINKED_OBJECT'], $rec['LINKED_PROPERTY'], $this->name);
        }
        if ($old_linked_object && $old_linked_object != $rec['LINKED_OBJECT'] && $old_linked_property && $old_linked_property != $rec['LINKED_PROPERTY']) {
            removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
        }


    } else {
        $out['ERR'] = 1;
    }
}
if ($this->tab == '') {
    //if ($rec['SUB_LIST']!='') {
    // $parents=SQLSelect("SELECT ID, TITLE, PARENT_ID FROM $table_name WHERE ID!='".$rec['ID']."' AND ID NOT IN (".$rec['SUB_LIST'].") ORDER BY PARENT_ID, TITLE");
    //} else {
    $parents = SQLSelect("SELECT ID, TITLE, PARENT_ID FROM $table_name WHERE ID!='" . $rec['ID'] . "' AND EXT_ID=0 ORDER BY PARENT_ID, TITLE");
    //}
    $titles = array();
    foreach ($parents as $k => $v) {
        $titles[$v['ID']] = $v['TITLE'];
    }
    $total = count($parents);
    for ($i = 0; $i < $total; $i++) {
        if ($titles[$parents[$i]['PARENT_ID']]) {
            $parents[$i]['TITLE'] = $titles[$parents[$i]['PARENT_ID']] . ' &gt; ' . $parents[$i]['TITLE'];
        }
    }
    $out['PARENTS'] = $parents;
}
if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}

if ($rec['ONCHANGE_OBJECT'] && !$rec['LINKED_OBJECT']) {
    $rec['LINKED_OBJECT'] = $rec['ONCHANGE_OBJECT'];
}

outHash($rec, $out);
if ($out['TITLE']) {
    $this->owner->data['TITLE'] = $out['TITLE'];
}

$out['SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

if ($out['ID']) {

    $same_level = SQLSelect("SELECT * FROM commands WHERE PARENT_ID='" . $out['PARENT_ID'] . "' ORDER BY PRIORITY DESC, TITLE");
    $out['SAME_LEVEL'] = $same_level;

    $children = SQLSelect("SELECT * FROM commands WHERE PARENT_ID='" . $out['ID'] . "' ORDER BY PRIORITY DESC, TITLE");
    if ($children) {
        $out['CHILDREN'] = $children;
    }

}

?>
