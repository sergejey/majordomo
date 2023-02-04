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
        $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=elements" . "&view_mode2=edit_elements&element_id=&top=" . $_GET['top'] . "&left=" . $_GET['left']);
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
        $this->redirect(ROOTHTML.'panel/scene/'.$rec['ID'].'/elements/'.$element_id.'.html?print='.gr('print'));
    }
}

global $state_id;

if ($this->tab == 'devices') {
    include DIR_MODULES . 'scenes/devices.inc.php';
}

if ($this->tab == 'widgets') {
    include DIR_MODULES . 'scenes/widgets.inc.php';
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
    include_once DIR_MODULES.'scenes/scenes_edit_elements.inc.php';
}


if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);

if (preg_match('/\.mp4$/',$out['WALLPAPER'])) {
    $out['VIDEO_WALLPAPER']=$out['WALLPAPER'];
}

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


