<?php

class plan_component extends stdClass
{
    public $name;
    public $component_id;

    function __construct($id)
    {
        $this->component_id = $id;
    }

    function getDataProperties()
    {
    }

    function getSVG($attributes)
    {
    }

    function getProperties()
    {
        $basic_properties = array();
        return $basic_properties;
    }

    function getData()
    {
        $data = array();
        if ($this->component_id) {
            $data_items = SQLSelect("SELECT * FROM plan_components_data WHERE COMPONENT_ID=" . (int)$this->component_id);
            foreach ($data_items as $data_item) {
                $data[$data_item['PROPERTY_NAME']] = array('VALUE' => $data_item['PROPERTY_VALUE'], 'LINKED_OBJECT' => $data_item['LINKED_OBJECT'], 'LINKED_PROPERTY' => $data_item['LINKED_PROPERTY']);
                if ($data_item['LINKED_OBJECT'] && $data_item['LINKED_PROPERTY']) {
                    $data[$data_item['PROPERTY_NAME']]['VALUE'] = getGlobal($data_item['LINKED_OBJECT'] . "." . $data_item['LINKED_PROPERTY']);
                }
            }
        }
        return $data;
    }

    function processProperties(&$properties)
    {
        $data = $this->getData();
        foreach ($properties as &$property) {
            if ($property['TYPE'] == 'select') {
                $items_data = explode('|', $property['DATA']);
                foreach ($items_data as $item) {
                    if (preg_match('/^(.+?)=(.+)$/', $item, $m)) {
                        $property['OPTIONS'][] = array('VALUE' => $m[1], 'TITLE' => $m[2]);
                    } else {
                        $property['OPTIONS'][] = array('VALUE' => $item, 'TITLE' => $item);
                    }
                }
            }
            if (isset($data[$property['NAME']])) {
                $property['VALUE'] = $data[$property['NAME']]['VALUE'];
                $property['LINKED_OBJECT'] = $data[$property['NAME']]['LINKED_OBJECT'];
                $property['LINKED_PROPERTY'] = $data[$property['NAME']]['LINKED_PROPERTY'];
            } else {
                $property['VALUE'] = $property['DEFAULT'];
            }
            $property['VALUE_HTML']=htmlspecialchars($property['VALUE']);
        }
    }

    function getJavascript($attributes)
    {
    }

}