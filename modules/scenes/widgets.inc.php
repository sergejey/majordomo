<?php

foreach ($this->widget_types as $type => $data) {
    $types[] = array(
        'TYPE' => $type,
        'TITLE' => $data['TITLE'],
        'DESCRIPTION' => $data['DESCRIPTION'],
    );
}
$out['WIDGET_TYPES'] = $types;

$top = gr('top', 'int');
$out['TOP'] = $top;
$left = gr('left', 'int');
$out['LEFT'] = $left;

$scene_id = $rec['ID'];

if ($this->mode == 'edit_widget') {
    $element_id = gr('element_id', 'int');
    $element = SQLSelectOne("SELECT * FROM elements WHERE ID=" . $element_id);
    $data = json_decode($element['WIZARD_DATA'], true);
    $widget_type = $this->widget_types[$data['WIDGET_TYPE']];
    $out['WIDGET_TYPE'] = $data['WIDGET_TYPE'];

    $properties = array();
    if (is_array($widget_type['PROPERTIES'])) {
        foreach ($widget_type['PROPERTIES'] as $property => $property_data) {
            if (!$property_data['_CONFIG_TYPE']) continue;
            $new_prop = $property_data;
            $new_prop['NAME'] = $property;
            if (isset($data[$property])) {
                $new_prop['VALUE'] = $data[$property];
            } else {
                $new_prop['VALUE'] = $property_data['DEFAULT_VALUE'];
            }
            if ($property_data['_CONFIG_TYPE'] == 'text' || $property_data['_CONFIG_TYPE'] == 'image_url' || $property_data['_CONFIG_TYPE'] == 'textarea') {
                $new_prop['VALUE'] = htmlspecialchars($new_prop['VALUE']);
            }
            if ($property_data['_CONFIG_TYPE'] == 'select') {
                $options = array();
                if (is_array($property_data['_CONFIG_OPTIONS'])) {
                    foreach ($property_data['_CONFIG_OPTIONS'] as $opt) {
                        if (!isset($opt['TITLE'])) $opt['TITLE'] = $opt['VALUE'];
                        $options[] = $opt;
                    }
                } elseif (is_callable($property_data['_CONFIG_OPTIONS'])) {
                    $options = $property_data['_CONFIG_OPTIONS']();
                }
                $total = count($options);
                for ($i = 0; $i < $total; $i++) {
                    if ($options[$i]['VALUE'] == $new_prop['VALUE']) $options[$i]['SELECTED'] = 1;
                }
                $new_prop['OPTIONS'] = $options;
            }
            $properties[] = $new_prop;
        }

        $out['PROPERTIES'] = $properties;
    }

    if (gr('mode2') == 'save') {
        $element['TITLE'] = gr('element_title');
        foreach ($widget_type['PROPERTIES'] as $property => $property_data) {
            if ($property_data['_CONFIG_TYPE']) {
                $value = gr('property_' . $property);
                $data[$property] = $value;
            }
        }
        foreach ($widget_type['PROPERTIES'] as $property => $property_data) {
            if (isset($property_data['FUNCTION'])) {
                $data[$property] = $property_data['FUNCTION']($data);
            }
        }
        if (is_integer(strpos($element['TITLE'], $widget_type['TITLE'])) || $element['TITLE'] == '') {
            if ($data['WIDGET_TYPE'] == 'device_scaled' && isset($data['device_id'])) {
                $device = SQLSelectOne("SELECT ID, TITLE FROM devices WHERE ID=" . (int)$data['device_id']);
                $element['TITLE'] = $widget_type['TITLE'] . ': ' . $device['TITLE'];
            } elseif ($data['WIDGET_TYPE'] == 'text') {
                $element['TITLE'] = $widget_type['TITLE'] . ': ' . strip_tags($data['text_value']);
                if (strlen($element['TITLE']) > 100) {
                    $element['TITLE'] = substr($element['TITLE'], 0, 97) . '...';
                }
            }
        }
        $element['WIZARD_DATA'] = json_encode($data);
        if ($element['TITLE'] != '') {
            SQLUpdate('elements', $element);
        }
        $this->redirect("?id=" . $scene_id . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&top=" . $top . "&left=" . $left);
    }

    $out['ELEMENT_TITLE'] = $element['TITLE'];
    $out['ELEMENT_ID'] = $element['ID'];

    //dprint($element);

}

if ($this->mode == 'add_widget') {
    $type = gr('type');
    $src_element_id = gr('src_element_id', 'int');
    if (isset($this->widget_types[$type])) {
        $widget_type = $this->widget_types[$type];
        if ($src_element_id) {
            $element = SQLSelectOne("SELECT * FROM elements WHERE TYPE='widget' AND ID=" . $src_element_id);
            if (!isset($element['ID'])) {
                return;
            }
            unset($element['ID']);
            $element['TITLE'] .= ' (copy)';
            $element['TOP'] = (int)$element['TOP'] + 20;
            $element['LEFT'] = (int)$element['LEFT'] + 20;
        } else {
            $element = array('SCENE_ID' => $scene_id, 'TYPE' => 'widget');
            $num = 1;
            $other_elements = SQLSelect("SELECT TITLE FROM elements WHERE TYPE='widget' AND SCENE_ID=" . $scene_id . " AND TITLE LIKE '" . DBSafe($widget_type['TITLE']) . "%' ORDER BY TITLE DESC");
            if ($other_elements[0]['TITLE']) {
                if (preg_match('/(\d+)$/', $other_elements[0]['TITLE'], $m)) {
                    $num = (int)$m[1] + 1;
                }
            }
            $element['TITLE'] = $widget_type['TITLE'] . ' ' . $num;
            $element['TOP'] = (int)$out['TOP'];
            $element['LEFT'] = (int)$out['LEFT'];
            $element['BACKGROUND'] = 0;
            $element['WIDTH'] = (int)$widget_type['DEFAULT_WIDTH'];
            $element['HEIGHT'] = (int)$widget_type['DEFAULT_HEIGHT'];

            $default_data = array();
            $default_data ['WIDGET_TYPE'] = $type;
            if (is_array($widget_type['PROPERTIES'])) {
                foreach ($widget_type['PROPERTIES'] as $k => $v) {
                    if (isset($v['DEFAULT_VALUE'])) {
                        $default_data[$k] = $v['DEFAULT_VALUE'];
                    }
                }
            }
            $element['WIZARD_DATA'] = json_encode($default_data);
        }
        $element['ID'] = SQLInsert('elements', $element);
        $this->redirect("?id=" . $scene_id . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&top=" . $top . "&left=" . $left . "&mode=edit_widget&element_id=" . $element['ID']);
    }
}

if ($this->mode == 'delete_widget') {
    $element_id = gr('element_id', 'int');
    $element = SQLSelectOne("SELECT * FROM elements WHERE SCENE_ID=" . $scene_id . " AND TYPE='widget' AND ID=" . $element_id);
    if ($element['ID']) {
        $this->delete_elements($element['ID']);
    }
    $this->redirect("?id=" . $scene_id . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&top=" . $top . "&left=" . $left);
}

$widgets = SQLSelect("SELECT ID, TITLE FROM elements WHERE SCENE_ID=" . $scene_id . " AND TYPE='widget' ORDER BY TITLE");
if ($widgets[0]['ID']) {
    $out['WIDGETS'] = $widgets;
}
