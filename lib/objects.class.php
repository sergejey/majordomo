<?php
/*
 * @version 0.1 (auto-set)
 */

/**
 * Summary of addClass
 * @param mixed $class_name Class name
 * @param mixed $parent_class Parent class (default '')
 * @return mixed
 */
function addClass($class_name, $parent_class = '')
{
    if ($parent_class != '') {
        $parent_class_id = addClass($parent_class);
    } else {
        $parent_class_id = 0;
    }

    $sqlQuery = "SELECT ID
                  FROM classes
                 WHERE TITLE = '" . DBSafe($class_name) . "'";

    $class = SQLSelectOne($sqlQuery);

    if ($class['ID']) {
        return $class['ID'];
    } else {
        $class = array();

        $class['TITLE'] = $class_name;
        $class['PARENT_ID'] = (int)$parent_class_id;
        $class['ID'] = SQLInsert('classes', $class);
        return $class['ID'];
    }
}

/**
 * Summary of getObjectClassTemplate
 * @param mixed $object_name Object name
 * @return mixed
 */
function getClassTemplate($class_id,$view='')
{

    $can_cache = false;
    if (isset($_SERVER['REQUEST_URI'])) {
        $can_cache = true;
        global $class_templates_cached;
    }

    if ($can_cache && isset($class_templates_cached[$class_id.'_'.$view])) {
        return $class_templates_cached[$class_id.'_'.$view];
    }

    $class = SQLSelectOne("SELECT ID, TITLE, PARENT_ID, TEMPLATE FROM classes WHERE ID=" . $class_id);
    if (!$class['ID']) {
        return '';
    }
    if ($view!='' && file_exists(DIR_TEMPLATES . 'classes/views/' . $class['TITLE'] . '_'.$view.'.html')) {
        $class_file_path = DIR_TEMPLATES . 'classes/views/' . $class['TITLE'] . '_'.$view.'.html';
        $alt_class_file_path = ROOT . 'templates_alt/classes/views/' . $class['TITLE'] . '_'.$view.'.html';
    } else {
        $class_file_path = DIR_TEMPLATES . 'classes/views/' . $class['TITLE'] . '.html';
        $alt_class_file_path = ROOT . 'templates_alt/classes/views/' . $class['TITLE'] . '.html';
    }
    if ($class['TEMPLATE'] != '') {
        $data = $class['TEMPLATE'];
    } elseif (file_exists($alt_class_file_path)) {
        $data = LoadFile($alt_class_file_path);
    } elseif (file_exists($class_file_path)) {
        $data = LoadFile($class_file_path);
    } elseif ($class['PARENT_ID']) {
        $data = getClassTemplate($class['PARENT_ID'],$view);
    } else {
        //$data='Template for ['.$class['TITLE'].'] not found';
        $data = '<b>%.object_title%</b>';
        $props = SQLSelect("SELECT ID,TITLE FROM properties WHERE CLASS_ID=" . $class['ID'] . " AND DATA_KEY=1 ORDER BY TITLE");
        if (!IsSet($props[0])) {
            $props = SQLSelect("SELECT ID,TITLE FROM properties WHERE CLASS_ID=" . $class['ID'] . " ORDER BY TITLE");
        }
        if (is_array($props)) {
            foreach ($props as $k => $v) {
                $data .= ' ' . $v['TITLE'] . ': %.' . $v['TITLE'] . '%';
            }
        }
    }

    if ($can_cache) {
        $class_templates_cached[$class_id.'_'.$view] = $data;
    }

    return $data;
}

/**
 * Summary of getObjectClassTemplate
 * @param mixed $object_name Object name
 * @return mixed
 */
function getObjectClassTemplate($object_name,$view='')
{
    startMeasure('getObjectClassTemplate');
    startMeasure('getObject');
    if (isset($_SERVER['REQUEST_URI'])) {
        global $all_objects_cached;
        if (!isset($all_objects_cached)) {
            $objects = SQLSelect("SELECT * FROM objects");
            foreach ($objects as $object) {
                $all_objects_cached[strtolower($object['TITLE'])] = $object;
            }
        }
        $rec = $all_objects_cached[strtolower($object_name)];
    } else {
        $sqlQuery = "SELECT objects.*
                     FROM objects
                    WHERE TITLE = '" . DBSafe($object_name) . "'";
        $rec = SQLSelectOne($sqlQuery);
    }
    //$object=getObject($object_name);
    $object = new stdClass();
    if (!$rec['ID']) {
        return '';
    }
    $object->id = $rec['ID'];
    $object->class_id = $rec['CLASS_ID'];
    $object->description = $rec['DESCRIPTION'];
    endMeasure('getObject');
    startMeasure('getClassTemplate');
    $data = getClassTemplate((int)$object->class_id,$view);
    endMeasure('getClassTemplate');
    $data = preg_replace('/<#ROOTHTML#>/uis', ROOTHTML, $data);
    $data = preg_replace('/%\.object_title%/uis', $object_name, $data);
    $data = preg_replace('/%\.object_id%/uis', $object->id, $data);
    $data = preg_replace('/%\.object_description%/uis', $object->description, $data);
    //$data=preg_replace('/%\.([\w\-]+?)%/uis', '%'.$object_name.'.\1'.'%', $data);
    //$data=preg_replace('/%\.(.+?)%/uis', '%'.$object_name.'.\1'.'%', $data);
    $data = preg_replace('/%\.([\w\_\d\-]+)/uis', '%' . $object_name . '.\1' . '', $data);
    endMeasure('getObjectClassTemplate');
    return $data;
}

/**
 * Summary of addClassMethod
 * @param mixed $class_name Class method
 * @param mixed $method_name Method name
 * @param mixed $code Code (default '')
 * @return mixed
 */
function addClassMethod($class_name, $method_name, $code = '', $key = '')
{
    $class_id = addClass($class_name);

    if ($class_id) {
        $sqlQuery = "SELECT * 
                     FROM methods
                    WHERE CLASS_ID = '" . $class_id . "'
                      AND TITLE = '" . DBSafe($method_name) . "'
                      AND OBJECT_ID = 0";

        $method = SQLSelectOne($sqlQuery);

        if ($key != '') {
            $injection_code = '/* begin injection of {' . $key . '} */' . "\n" . $code . "\n" . '/* end injection of {' . $key . '} */';
        } else {
            $injection_code = $code;
        }

        if (!$method['ID']) {
            $method = array();

            $method['CLASS_ID'] = $class_id;
            $method['OBJECT_ID'] = 0;
            $method['CODE'] = $injection_code;
            $method['TITLE'] = $method_name;
            $method['ID'] = SQLInsert('methods', $method);
        } else {
            if ($code != '' && $method['CODE'] != $injection_code && $method['CODE'] != $code && $key != '') {
                @$old_code = $method['CODE'];
                if (preg_match('/\/\* begin injection of {' . $key . '} \*\/(.*?)\/\* end injection of {' . $key . '} \*\//uis', $method['CODE'], $m)) {
                    $current_injection = trim($m[1]);
                    if ($current_injection != $code) {
                        $method['CODE'] = str_replace($m[0], $injection_code, $method['CODE']);
                    }
                } else {
                    $method['CODE'] .= "\n" . $injection_code;
                }

                //$method['CODE'] = $code;
                SQLUpdate('methods', $method);
            }

        }
    }
    return $method['ID'];
}

/**
 * Summary of addClassProperty
 * @param mixed $class_name Class name
 * @param mixed $property_name Property name
 * @param mixed $keep_history Flag keep history (default 0)
 * @return mixed
 */
function addClassProperty($class_name, $property_name, $keep_history = 0)
{
    $class_id = addClass($class_name);

    $sqlQuery = "SELECT ID
                  FROM properties
                 WHERE TITLE = '" . DBSafe($property_name) . "'
                   AND OBJECT_ID = 0
                   AND CLASS_ID  = '" . $class_id . "'";

    $prop = SQLSelectOne($sqlQuery);

    if (!$prop['ID']) {
        $prop = array();

        $prop['CLASS_ID'] = $class_id;
        $prop['TITLE'] = $property_name;
        $prop['KEEP_HISTORY'] = $keep_history;
        $prop['OBJECT_ID'] = 0;
        $prop['ID'] = SQLInsert('properties', $prop);
    }

    return $prop['ID'];
}

/**
 * Summary of addClassObject
 * @param mixed $class_name Class name
 * @param mixed $object_name Object name
 * @return mixed
 */
function addClassObject($class_name, $object_name, $system = '')
{
    $class_id = addClass($class_name);
    $sqlQuery = "SELECT ID
                  FROM objects
                 WHERE TITLE = '" . DBSafe($object_name) . "'";
    $object = SQLSelectOne($sqlQuery);
    if ($object['ID'])
        return $object['ID'];

    if ($system != '') {
        $sqlQuery = "SELECT ID
                  FROM objects
                 WHERE `SYSTEM` = '" . DBSafe($system) . "'";
        $object = SQLSelectOne($sqlQuery);
        if ($object['ID'])
            return $object['ID'];
    }

    $object = array();
    $object['TITLE'] = $object_name;
    $object['CLASS_ID'] = $class_id;
    $object['SYSTEM'] = $system . '';
    $object['ID'] = SQLInsert('objects', $object);
    return $object['ID'];

}

/**
 * Summary of getValueIdByName
 * @param mixed $object_name Object name
 * @param mixed $property Property
 * @return int
 */
function getValueIdByName($object_name, $property)
{
    $sqlQuery = "SELECT ID
                  FROM pvalues
                 WHERE PROPERTY_NAME = '" . DBSafe($object_name . '.' . $property) . "'";

    $value = SQLSelectOne($sqlQuery);

    if (!$value['ID']) {
        $object = getObject($object_name);

        if (is_object($object)) {
            $property_id = $object->getPropertyByName($property, $object->class_id, $object->id);

            $sqlQuery = "SELECT ID
                        FROM pvalues
                       WHERE PROPERTY_ID = " . (int)$property_id . "
                         AND OBJECT_ID   = " . (int)$object->id;

            $value = SQLSelectOne($sqlQuery);

            if (!$value['ID'] && $property_id) {
                $value = array();

                $value['PROPERTY_ID'] = $property_id;
                $value['OBJECT_ID'] = $object->id;
                $value['PROPERTY_NAME'] = $object_name . '.' . $property;
                $value['VALUE'] = '';
                $value['ID'] = SQLInsert('pvalues', $value);
            }
        }
    }

    return (int)$value['ID'];
}

/**
 * Summary of addLinkedProperty
 * @param mixed $object Object
 * @param mixed $property Property
 * @param mixed $module Module
 * @return int
 */
function addLinkedProperty($object, $property, $module)
{
    $sqlQuery = "SELECT *
                  FROM pvalues
                 WHERE ID = '" . getValueIdByName($object, $property) . "'";

    $value = SQLSelectOne($sqlQuery);

    if (IsSet($value['ID'])) {
        if (!$value['LINKED_MODULES']) {
            $tmp = array();
        } else {
            $tmp = explode(',', $value['LINKED_MODULES']);
        }

        if (!in_array($module, $tmp)) {
            $tmp[] = $module;

            $value['LINKED_MODULES'] = implode(',', $tmp);

            SQLUpdate('pvalues', $value);
        }
    } else {
        return 0;
    }
}

/**
 * Summary of removeLinkedProperty
 * @param mixed $object Object
 * @param mixed $property Property
 * @param mixed $module Module
 * @return int
 */
function removeLinkedProperty($object, $property, $module)
{
    $sqlQuery = "SELECT *
                  FROM pvalues
                 WHERE ID = '" . getValueIdByName($object, $property) . "'";

    $value = SQLSelectOne($sqlQuery);

    if ($value['ID']) {
        if (!$value['LINKED_MODULES']) {
            $tmp = array();
        } else {
            $tmp = explode(',', $value['LINKED_MODULES']);
        }

        if (in_array($module, $tmp)) {
            $total = count($tmp);
            $res = array();

            for ($i = 0; $i < $total; $i++) {
                if ($tmp[$i] != $module) {
                    $res[] = $tmp[$i];
                }
            }

            $tmp = $res;

            $value['LINKED_MODULES'] = implode(',', $tmp);

            SQLUpdate('pvalues', $value);
        }
    } else {
        return 0;
    }
}

/**
 * Summary of getObject
 * @param mixed $name Object name
 * @access public
 * @return int|objects
 */
function getObject($name)
{

    if (trim($name)=='') return 0;

    if (preg_match('/^(.+?)\.(.+?)$/', $name, $m)) {
        $class_name = $m[1];
        $object_name = $m[2];

        $sqlQuery = "SELECT objects.*
                     FROM objects
                     LEFT JOIN classes ON objects.CLASS_ID = classes.ID
                    WHERE objects.TITLE = '" . DBSafe($object_name) . "'
                      AND classes.TITLE = '" . DBSafe($class_name) . "'";
        $rec = SQLSelectOne($sqlQuery);
    } else {
        $sqlQuery = "SELECT objects.*
                     FROM objects
                    WHERE TITLE = '" . DBSafe($name) . "'";
        $rec = SQLSelectOne($sqlQuery);
        //$rec = SQLSelectOne("SELECT objects.* FROM objects WHERE TITLE = '".DBSafe($name)."'");
    }

    if (!$rec['ID']) {
        $sqlQuery = "SELECT objects.*
                     FROM objects
                    WHERE TITLE = '" . DBSafe($name) . "'";
        $rec = SQLSelectOne($sqlQuery);
    }

    if ($rec['ID']) {
        include_once(DIR_MODULES . 'objects/objects.class.php');
        $obj = new objects();
        $obj->id = $rec['ID'];
        $obj->loadObject($rec['ID']);
        return $obj;
    }

    return 0;
}

/**
 * Summary of getObjectsByProperty
 * @param mixed $property_name Property name
 * @return array|int
 */
function getObjectsByProperty($property_name, $condition = '', $condition_value = '')
{
    if ($condition_value == '' && $condition != '') {
        $condition_value = $condition;
        $condition = '==';
    }
    $pRecs = SQLSelect("SELECT ID FROM properties WHERE TITLE = '" . DBSafe($property_name) . "'");
    $total = count($pRecs);
    if (!$total) {
        return 0;
    }
    $found = array();
    for ($i = 0; $i < $total; $i++) {
        $pValues = SQLSelect("SELECT objects.TITLE, VALUE FROM pvalues LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID WHERE PROPERTY_ID='" . $pRecs[$i]['ID'] . "'");
        $totalv = count($pValues);
        for ($iv = 0; $iv < $totalv; $iv++) {
            $v = $pValues[$iv]['VALUE'];
            if (!$condition) {
                $found[$pValues[$iv]['TITLE']] = 1;
            } elseif (($condition == '=' || $condition == '==') && ($v == $condition_value)) {
                $found[$pValues[$iv]['TITLE']] = 1;
            } elseif (($condition == '>=') && ($v >= $condition_value)) {
                $found[$pValues[$iv]['TITLE']] = 1;
            } elseif (($condition == '>') && ($v > $condition_value)) {
                $found[$pValues[$iv]['TITLE']] = 1;
            } elseif (($condition == '<=') && ($v <= $condition_value)) {
                $found[$pValues[$iv]['TITLE']] = 1;
            } elseif (($condition == '<') && ($v < $condition_value)) {
                $found[$pValues[$iv]['TITLE']] = 1;
            } elseif (($condition == '<>' || $condition == '!=') && ($v != $condition_value)) {
                $found[$pValues[$iv]['TITLE']] = 1;
            }
        }
    }

    $res = array();
    foreach ($found as $k => $v) {
        $res[] = $k;
    }
    return $res;

}

/**
 * Summary of getObjectsByClass
 * @param mixed $class_name Class name
 * @return array|int
 */
function getObjectsByClass($class_name)
{
    $sqlQuery = "SELECT ID
                  FROM classes
                 WHERE (TITLE = '" . DBSafe(trim($class_name)) . "'
                        OR ID = " . (int)$class_name . "
                       )";

    $class_record = SQLSelectOne($sqlQuery);

    if (!$class_record['ID']) {
        return 0;
    }

    $sqlQuery = "SELECT ID, TITLE
                  FROM objects
                 WHERE CLASS_ID = '" . $class_record['ID'] . "'";

    $objects = SQLSelect($sqlQuery);

    $sqlQuery = "SELECT ID, TITLE
                  FROM classes WHERE PARENT_ID = '" . $class_record['ID'] . "'";

    $sub_classes = SQLSelect($sqlQuery);

    if (IsSet($sub_classes[0]['ID'])) {
        $total = count($sub_classes);

        for ($i = 0; $i < $total; $i++) {
            $sub_objects = getObjectsByClass($sub_classes[$i]['TITLE']);

            if (IsSet($sub_objects[0]['ID'])) {
                foreach ($sub_objects as $obj) {
                    $objects[] = $obj;
                }
            }
        }
    }

    /*
    $total=count($objects);
    for($i=0;$i<$total;$i++) {
    $objects[$i]=getObject($objects[$i]['TITLE'])
    }
     */

    return $objects;
}


function getClassProperties($class_id, $def = '')
{

    global $cached_class_properties;
    if (isset($cached_class_properties[$class_id])) return $cached_class_properties[$class_id];

    $class = SQLSelectOne("SELECT ID, PARENT_ID FROM classes WHERE (ID='" . (int)$class_id . "' OR TITLE = '" . DBSafe($class_id) . "')");
    $properties = SQLSelect("SELECT properties.*, classes.TITLE AS CLASS_TITLE FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID WHERE CLASS_ID='" . $class['ID'] . "' AND OBJECT_ID=0");
    $res = $properties;
    if (!is_array($def)) {
        $def = array();
        foreach ($properties as $p) {
            $def[] = $p['TITLE'];
        }
    }
    foreach ($properties as $p) {
        if (!in_array($p['TITLE'], $def)) {
            $res[] = $p;
            $def[] = $p['TITLE'];
        }
    }
    if ($class['PARENT_ID']) {
        $p_res = getClassProperties($class['PARENT_ID'], $def);
        if ($p_res[0]['ID']) {
            foreach ($p_res as $k => $p) {
                if (!in_array($p['TITLE'], $def)) {
                    $res[] = $p;
                    $def[] = $p['TITLE'];
                }
            }
        }
    }
    $cached_class_properties[$class_id] = $res;
    return $res;
}

function getKeyData($object_id)
{
    startMeasure('getKeyData');
    $object_rec = SQLSelectOne("SELECT ID,TITLE,CLASS_ID FROM objects WHERE ID=" . (int)$object_id);
    $props = getClassProperties($object_rec['CLASS_ID']);
    $add_description = '';
    foreach ($props as $k => $v) {
        if ($v['DATA_KEY']) {
            $data = getGlobal($object_rec['TITLE'] . '.' . $v['TITLE']);
            if ($data != '') {
                $add_description .= $v['TITLE'] . ': ' . $data . '; ';
            }
        }
    }
    endMeasure('getKeyData');
    return $add_description;
}

/**
 * Summary of getGlobal
 * @param mixed $varname Variable name
 * @return mixed
 */
function getGlobal($varname)
{
    $tmp = explode('.', $varname);

    if (isset($tmp[2])) {
        $object_name = $tmp[0] . '.' . $tmp[1];
        $varname = $tmp[2];
    } elseif (isset($tmp[1])) {
        $object_name = $tmp[0];
        $varname = $tmp[1];
    } else {
        $object_name = 'ThisComputer';
    }
    $cached_name = 'MJD:' . $object_name . '.' . $varname;
    $cached_value = checkFromCache($cached_name);

    if ($cached_value !== false) {
        return $cached_value;
    }
    $obj = getObject($object_name);
    if ($obj) {
        $value = $obj->getProperty($varname);
        return $value;
    } else {
        return 0;
    }
}

/**
 * getHistoryValueId
 *
 * Return history value id
 *
 * @access public
 */
function getHistoryValueId($varname)
{
    $tmp = explode('.', $varname);

    if (isset($tmp[2])) {
        $object_name = $tmp[0] . '.' . $tmp[1];
        $varname = $tmp[2];
    } elseif (isset($tmp[1])) {
        $object_name = $tmp[0];
        $varname = $tmp[1];
    } else
        $object_name = 'ThisComputer';

    // Get object
    $obj = getObject($object_name);
    if (!$obj) return false;

    // Get property
    $prop_id = $obj->getPropertyByName($varname, $obj->class_id, $obj->id);
    if ($prop_id == false) return false;

    $rec = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . (int)$prop_id . "' AND OBJECT_ID='" . (int)$obj->id . "'");

    if (!$rec['ID'])
        return false;

    return $rec['ID'];
}

/**
 * getHistory
 *
 * Return history data
 *
 * @access public
 */
function getHistory($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);

    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }

    // Get data
    return SQLSelect("SELECT VALUE, ADDED FROM $table_name WHERE VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "') ORDER BY ADDED");
}

function getHistoryAvgDay($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);

    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }

    // Get data
    return SQLSelect("SELECT round(avg(VALUE),2) VALUE,  date(ADDED) ADDED FROM $table_name WHERE VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "') group by  date(ADDED) ORDER BY ADDED");
}


/**
 * getHistoryMin
 *
 * Return history data
 *
 * @access public
 */
function getHistoryMin($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);

    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }

    // Get data
    $data = SQLSelectOne("SELECT MIN(VALUE+0.0) AS VALUE FROM $table_name " .
        "WHERE VALUE != \"\" AND VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "')");

    if (!$data['VALUE'])
        return false;

    return $data['VALUE'];
}

/**
 * getHistoryMax
 *
 * Return history data
 *
 * @access public
 */
function getHistoryMax($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }
    // Get data
    $data = SQLSelectOne("SELECT MAX(VALUE+0.0) AS VALUE FROM $table_name " .
        "WHERE VALUE != \"\" AND  VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "')");
    if (!$data['VALUE'])
        return false;

    return $data['VALUE'];
}

/**
 * getHistoryCount
 *
 * Return history data
 *
 * @access public
 */
function getHistoryCount($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }
    // Get data
    $data = SQLSelectOne("SELECT COUNT(VALUE+0.0) AS VALUE FROM $table_name " .
        "WHERE VALUE != \"\" AND VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "')");
    if (!$data['VALUE'])
        return false;

    return $data['VALUE'];
}

/**
 * getHistorySum
 *
 * Return history data
 *
 * @access public
 */
function getHistorySum($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }
    // Get data
    $data = SQLSelectOne("SELECT SUM(VALUE+0.0) AS VALUE FROM $table_name " .
        "WHERE  VALUE != \"\" AND VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "')");
    if (!$data['VALUE'])
        return false;

    return $data['VALUE'];
}

/**
 * getHistoryAvg
 *
 * Return history data
 *
 * @access public
 */
function getHistoryAvg($varname, $start_time, $stop_time = 0)
{
    if ($start_time <= 0) $start_time = (time() + $start_time);
    if ($stop_time <= 0) $stop_time = (time() + $stop_time);

    // Get hist val id
    $id = getHistoryValueId($varname);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }

    // Get data
    $data = SQLSelectOne("SELECT AVG(VALUE+0.0) AS VALUE FROM $table_name " .
        "WHERE  VALUE != \"\" AND VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $stop_time) . "')");

    if (!$data['VALUE']) {
        $data = SQLSelectOne("SELECT VALUE+0.0 FROM $table_name " .
            "WHERE  VALUE != \"\" AND VALUE_ID='" . $id . "' AND ADDED<('" . date('Y-m-d H:i:s', $start_time) . "') ORDER BY ADDED DESC LIMIT 1");
    }


    if (!$data['VALUE'])
        return false;

    return $data['VALUE'];
}

/**
 * getHistoryValue
 *
 * Return history value
 *
 * @access public
 */
function getHistoryValue($varname, $time, $nerest = false)
{
    if ($time <= 0) $time = (time() + $time);

    // Get hist val id
    $id = getHistoryValueId($varname);

    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($id);
    } else {
        $table_name = 'phistory';
    }

    // Get val before
    $val1 = SQLSelectOne("SELECT VALUE, UNIX_TIMESTAMP(ADDED) AS ADDED FROM $table_name WHERE VALUE_ID='" . $id . "' AND ADDED<=('" . date('Y-m-d H:i:s', $time) . "') AND ADDED>=('" . date('Y-m-d H:i:s', $time - 7 * 24 * 60 * 60) . "') ORDER BY ADDED DESC LIMIT 1");
    if (!isset($val1['VALUE']))
        $val1 = SQLSelectOne("SELECT VALUE, UNIX_TIMESTAMP(ADDED) AS ADDED FROM $table_name WHERE VALUE_ID='" . $id . "' AND ADDED<=('" . date('Y-m-d H:i:s', $time) . "') ORDER BY ADDED DESC LIMIT 1");

    // Get val after
    $val2 = SQLSelectOne("SELECT VALUE, UNIX_TIMESTAMP(ADDED) AS ADDED FROM $table_name WHERE VALUE_ID='" . $id . "' AND ADDED>=('" . date('Y-m-d H:i:s', $time) . "') ORDER BY ADDED LIMIT 1");

    // Not found values
    if ((!$val1['VALUE']) && (!$val2['VALUE']))
        return false;

    // Only before
    if (($val1['VALUE']) && (!$val2['VALUE']))
        return $val1['VALUE'];

    // Only after
    if ((!$val1['VALUE']) && ($val2['VALUE']))
        return $val2['VALUE'];

    // Nerest
    if ($nerest) {
        if (($time - $val1['ADDED']) < ($val2['ADDED'] - $time))
            return $val1['VALUE'];
        else
            return $val2['VALUE'];
    } // Interpolation
    else {
        if ($val2['ADDED'] - $val1['ADDED'] == 0)
            return $val1['VALUE'];
        else
            return $val1['VALUE'] + ($val2['VALUE'] - $val1['VALUE']) * ($time - $val1['ADDED']) / ($val2['ADDED'] - $val1['ADDED']);
    }
}

/**
 * Summary of setGlobal
 * @param mixed $varname Variable name
 * @param mixed $value Value
 * @param mixed $no_linked No-Linked (default 0)
 * @return int
 */
function setGlobal($varname, $value, $no_linked = 0, $source = '')
{

    $tmp = explode('.', $varname);

    if (isset($tmp[2])) {
        $object_name = $tmp[0] . '.' . $tmp[1];
        $varname = $tmp[2];
    } elseif (isset($tmp[1])) {
        $object_name = $tmp[0];
        $varname = $tmp[1];
    } else {
        $object_name = 'ThisComputer';
    }

    $obj = getObject($object_name);

    if ($obj) {
        return $obj->setProperty($varname, $value, $no_linked, $source);
    } else {
        return 0;
    }
}

/**
 * Summary of callMethod
 * @param mixed $method_name Method name
 * @param mixed $params Params (default 0)
 * @return mixed
 */
function callMethod($method_name, $params = 0)
{
    $tmp = explode('.', $method_name);
    if (IsSet($tmp[2])) {
        $object_name = $tmp[0] . '.' . $tmp[1];
        $varname = $tmp[2];
    } elseif (IsSet($tmp[1])) {
        $object_name = $tmp[0];
        $method_name = $tmp[1];
    } else {
        $object_name = 'ThisComputer';
    }

    if ($object_name == 'AllScripts') {
        return runScript($method_name,$params);
    }

    $obj = getObject($object_name);

    if ($obj) {
        return $obj->callMethod($method_name, $params);
    } else {
        return 0;
    }
}

function callMethodSafe($method_name, $params = 0)
{
    $tmp = explode('.', $method_name);
    if (IsSet($tmp[2])) {
        $object_name = $tmp[0] . '.' . $tmp[1];
        $varname = $tmp[2];
    } elseif (IsSet($tmp[1])) {
        $object_name = $tmp[0];
        $method_name = $tmp[1];
    } else {
        $object_name = 'ThisComputer';
    }
    if ($object_name == 'AllScripts') {
        return runScriptSafe($method_name,$params);
    }
    $obj = getObject($object_name);

    if ($obj) {
        return $obj->callMethodSafe($method_name, $params);
    } else {
        return 0;
    }
}

function callAPI($api_url, $method = 'GET', $params = 0)
{
    $is_child = false;
    $fork_disabled = true;

    if (defined('ENABLE_FORK') && ENABLE_FORK && function_exists('pcntl_fork')) {
        $fork_disabled = false;
    }

    if (!$fork_disabled) {
        $child_pid = pcntl_fork();
        if ($child_pid == -1) {
            //error
        } elseif ($child_pid) {
            // parent
            pcntl_wait($status, WNOHANG);
            return true;
        } else {
            // child
            $is_child = true;
            register_shutdown_function(create_function('$pars', 'posix_kill(getmypid(), SIGKILL);'), array());
            set_time_limit(60);
        }
    }


    startMeasure('callAPI');
    if (!is_array($params)) {
        $params = array();
    }
    $params['no_session']=1;


    $url = preg_replace('/^\/api\//', BASE_URL.'/api.php/', $api_url);
    $url = preg_replace('/([^:])\/\//','\1/',$url);

    $method=strtoupper($method);
    global $api_ch;
    if (!isset($api_ch)) {
        $api_ch = curl_init();
        curl_setopt($api_ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0');
        curl_setopt($api_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($api_ch, CURLOPT_CONNECTTIMEOUT, 10); // connection timeout
        curl_setopt($api_ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($api_ch, CURLOPT_TIMEOUT, 45);  // operation timeout 45 seconds
        curl_setopt($api_ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($api_ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($api_ch, CURLOPT_NOSIGNAL, 1);
        if (!$is_child) {
            curl_setopt($api_ch, CURLOPT_TIMEOUT_MS, 50);
        }
    }
    if ($method == 'GET') {
        $url .= '?' . http_build_query($params);
        curl_setopt($api_ch, CURLOPT_POSTFIELDS, 0);
        curl_setopt($api_ch, CURLOPT_POST, 0);
    } elseif ($method == 'POST') {
        curl_setopt($api_ch, CURLOPT_POST, 1);
        curl_setopt($api_ch, CURLOPT_POSTFIELDS, $params);
    }
    curl_setopt($api_ch, CURLOPT_URL, $url);
    curl_exec($api_ch);

    endMeasure('callAPI');

    if ($is_child) {
        exit();
    }
    return true;

}

function injectObjectMethodCode($method_name, $key, $code)
{
    $tmp = explode('.', $method_name);
    if (IsSet($tmp[2])) {
        $object_name = $tmp[0] . '.' . $tmp[1];
        $varname = $tmp[2];
    } elseif (IsSet($tmp[1])) {
        $object_name = $tmp[0];
        $method_name = $tmp[1];
    } else {
        $object_name = 'ThisComputer';
    }
    $obj = getObject($object_name);

    if ($obj) {
        //return $obj->callMethod($method_name, $params);
        $id = $obj->getMethodByName($method_name, $obj->class_id, $obj->id);
        if ($id) {
            $method = SQLSelectOne("SELECT * FROM methods WHERE ID=" . (int)$id);
            if ($method['OBJECT_ID'] != $obj->id) {
                $method = array();
                $method['OBJECT_ID'] = $obj->id;
                $method['TITLE'] = $method_name;
                $method['ID'] = SQLInsert('methods', $method);
            }
            $injection_code = '/* begin injection of {' . $key . '} */' . "\n" . $code . "\n" . '/* end injection of {' . $key . '} */';
            @$old_code = $method['CODE'];
            if (preg_match('/\/\* begin injection of {' . $key . '} \*\/(.*?)\/\* end injection of {' . $key . '} \*\//uis', $method['CODE'], $m)) {
                $current_injection = trim($m[1]);
                if ($current_injection != $code) {
                    $method['CODE'] = str_replace($m[0], $injection_code, $method['CODE']);
                }
            } else {
                $method['CODE'] .= "\n" . $injection_code;
            }
            if ($method['CODE'] != $old_code) {
                SQLUpdate('methods', $method);
            }
            return 1;
        }
    } else {
        return 0;
    }
}

/**
 * Summary of processTitle
 * @param mixed $title Title
 * @param mixed $object Object (default 0)
 * @return mixed
 */
function processTitle($title, $object = 0)
{
    global $title_memory_cache;

    $key = $title;

    if (!$title) {
        return $title;
    }

    startMeasure('processTitle');

    $in_title = substr($title, 0, 100);

    //startMeasure('processTitle ['.$in_title.']');

    if ($in_title != '') {
        if (IsSet($_SERVER['REQUEST_METHOD'])) {
            if ($title_memory_cache[$key]) {
                return $title_memory_cache[$key];
            }
        }

        if (preg_match('/\[#.+?#\]/is', $title)) {
            startMeasure('processTitleJTemplate');
            if ($object) {
                $jTempl = new jTemplate($title, $object->data, $object);
            } else {
                $jTempl = new jTemplate($title, $data);
            }

            $title = $jTempl->result;
            endMeasure('processTitleJTemplate');
            // return $title;
        } else {

            $title = preg_replace('/%rand%/is', rand(), $title);
            $title = preg_replace('/%([\w\d\.]+?)\.([\w\d\.]+?)\|(\d+)%/uis', '%\1.\2%', $title);
            if (preg_match_all('/%([\w\d\.]+?)\.([\w\d\.]+?)%/uis', $title, $m)) {
                startMeasure('processTitleProperties');

                $total = count($m[0]);

                for ($i = 0; $i < $total; $i++) {
                    $title = str_replace($m[0][$i], getGlobal($m[1][$i] . '.' . $m[2][$i]), $title);
                }

                endMeasure('processTitleProperties');
            }
            if (preg_match_all('/%([\w\d\.]+?)\.([\w\d\.]+?)\|"(.+?)"%/uis', $title, $m)) {
                startMeasure('processTitlePropertiesReplace');

                $total = count($m[0]);

                for ($i = 0; $i < $total; $i++) {
                    $property_name = $m[1][$i] . '.' . $m[2][$i];
                    $data = getGlobal($property_name);
                    if ($data == '') $data = 0;
                    $descr = $m[3][$i];
                    $descr = preg_replace('#(?<!\\\)\;#', ";-;;-;", $descr); 
                    $descr = preg_replace('#\\\;#', ";", $descr); 
                    $tmp = explode(';-;;-;', $descr);
                    $totald = count($tmp);
                    $hsh = array();
                    if ($totald == 1) {
                        if ($data != '') {
                            $hsh[$data] = $descr;
                        } else {
                            $hsh[$data] = '';
                        }
                    } else {
                        for ($id = 0; $id < $totald; $id++) {
                            $item = trim($tmp[$id]);
                            if (preg_match('/(.*?)=(.+)/uis', $item, $md)) {
                                $search_value = $md[1];
                                if ($search_value=='') {
                                    $search_value='<empty>';
                                }
                                $search_replace = $md[2];
                            } else {
                                $search_value = $id;
                                $search_replace = $item;
                            }
                            $hsh[$search_value] = $search_replace;
                        }
                        if ($data == '') $data='<empty>';
                    }
                    $title = str_replace($m[0][$i], $hsh[$data], $title);
                }

                endMeasure('processTitlePropertiesReplace');
            }
            if (preg_match_all('/%([\w\d\.]+?)\.([\w\d\.]+?)\|(\w+?)%/uis', $title, $m)) {
                startMeasure('processTitlePropertiesFunction');
                $total = count($m[0]);
                for ($i = 0; $i < $total; $i++) {
                    $data = getGlobal($m[1][$i] . '.' . $m[2][$i]);
                    if (function_exists($m[3][$i])) {
                        $data = call_user_func($m[3][$i], $data);
                    }
                    $title = str_replace($m[0][$i], $data, $title);
                }
                endMeasure('processTitlePropertiesFunction');
            }
            if (preg_match_all('/%([\w\d\.]+?)%/is', $title, $m)) {
                $total = count($m[0]);

                for ($i = 0; $i < $total; $i++) {
                    if (preg_match('/^%\d/is', $m[0][$i])) {
                        continue; // dirty hack, sorry for that
                    }

                    $title = str_replace($m[0][$i], getGlobal($m[1][$i]), $title);
                }
            }

        }


        if (preg_match_all('/<#LANG_(\w+?)#>/is', $title, $m)) {
            $total = count($m[0]);

            for ($i = 0; $i < $total; $i++) {
                $title = str_replace($m[0][$i], constant('LANG_' . $m[1][$i]), $title);
            }
        }
        if (preg_match_all('/\&#060#LANG_(.+?)#\&#062/is', $title, $m)) {
            $total = count($m[0]);

            for ($i = 0; $i < $total; $i++) {
                $title = str_replace($m[0][$i], constant('LANG_' . $m[1][$i]), $title);
            }
        }
    }

    //endMeasure('processTitle ['.$in_title.']', 1);
    if (IsSet($_SERVER['REQUEST_METHOD'])) {
        $title_memory_cache[$key] = $title;
    }

    endMeasure('processTitle', 1);

    return $title;
}


/* SHORT ALIAS */
/**
 * Alias for setGlobal
 * @param mixed $varname Variable name
 * @param mixed $value Value
 * @param mixed $no_linked No-Linked (default 0)
 * @return int
 */
function sg($varname, $value, $no_linked = 0, $source = '')
{
    return setGlobal($varname, $value, $no_linked, $source);
}

/**
 * Alias for getGlobal
 * @param mixed $varname Variable name
 * @return mixed
 */
function gg($varname)
{
    return getGlobal($varname);
}

/**
 * Alias for callMethod
 * @param mixed $method_name Method name
 * @param mixed $params Params (default 0)
 * @return mixed
 */
function cm($method_name, $params = 0)
{
    return callMethod($method_name, $params);
}

/**
 * Alias for callMethod
 * @param mixed $method_name Method name
 * @param mixed $params Params (default 0)
 * @return mixed
 */
function runMethod($method_name, $params = 0)
{
    return callMethod($method_name, $params);
}

/**
 * Alias for runScript
 * @param mixed $script_id Script ID
 * @param mixed $params Parameters
 * @return mixed
 */
function rs($script_id, $params = 0)
{
    return runScript($script_id, $params);
}

function getRoomObjectByLocation($location_id, $auto_add = 0)
{
    $location_rec = SQLSelectOne("SELECT * FROM locations WHERE ID=" . (int)$location_id);
    $location_title = transliterate($location_rec['TITLE']);
    $location_title = preg_replace('/\W/', '', $location_title);
    if (!$location_title) {
        $location_title = 'Room' . $location_id;
    }
    $room_object = SQLSelectOne("SELECT * FROM objects WHERE TITLE = '" . DBSafe($location_title) . "'");
    if ($room_object['ID']) return $room_object['TITLE'];

    $class_id = addClass("Rooms");
    $room_object = SQLSelectOne("SELECT * FROM objects WHERE LOCATION_ID=" . $location_id . " AND CLASS_ID=" . $class_id);
    if ($room_object['ID']) return $room_object['TITLE'];
    if ($auto_add) {
        $object_id = addClassObject("Rooms", $location_title);
        SQLExec("UPDATE objects SET LOCATION_ID=" . (int)$location_rec['ID'] . ", DESCRIPTION='" . DBSafe($location_rec['TITLE']) . "' WHERE ID=" . $object_id);
        return $location_title;
    } else {
        return '';
    }
}

function getUserObjectByTitle($user_id, $auto_add = 0)
{
    $user_rec = SQLSelectOne("SELECT * FROM users WHERE ID=" . (int)$user_id);
    $user_title = transliterate($user_rec['USERNAME']);
    $user_title = preg_replace('/\W/', '', $user_title);
    if (!$user_title) {
        $user_title = 'User' . $user_id;
    }
    $user_object = SQLSelectOne("SELECT * FROM objects WHERE (TITLE = '" . DBSafe($user_title) . "' OR (DESCRIPTION!='' AND DESCRIPTION = '" . $user_rec['NAME'] . "'))");
    if ($user_object['ID']) return $user_object['TITLE'];
    if ($auto_add) {
        $object_id = addClassObject("Users", $user_title);
        SQLExec("UPDATE objects SET DESCRIPTION='" . DBSafe($user_rec['NAME']) . "' WHERE ID=" . $object_id);
        return $user_title;
    } else {
        return '';
    }
}

function deleteObject($object_id)
{
    $object_rec = SQLSelectOne("SELECT ID FROM objects WHERE ID=" . (int)$object_id . " OR TITLE = '" . DBSafe($object_id) . "'");
    if ($object_rec['ID']) {
        include_once(DIR_MODULES . 'objects/objects.class.php');
        $obj = new objects();
        $obj->delete_objects($object_rec['ID']);
    }
}

function objectClassChanged($object_id)
{

    include_once(DIR_MODULES . 'objects/objects.class.php');
    $obj = new objects();
    // class changed from $class_changed_from to $rec['CLASS_ID']
    $rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . (int)$object_id);
    // step 1. take all properties out of class
    $pvalues = SQLSelect("SELECT pvalues.*, properties.TITLE AS PROPERTY_TITLE FROM pvalues LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID WHERE properties.CLASS_ID!=0 AND pvalues.OBJECT_ID='" . $rec['ID'] . "'");
    $total = count($pvalues);
    for ($i = 0; $i < $total; $i++) {
        $new_property = array();
        $new_property['TITLE'] = $pvalues[$i]['PROPERTY_TITLE'];
        $new_property['CLASS_ID'] = 0;
        $new_property['OBJECT_ID'] = $rec['ID'];
        //$new_property['VALUE']='';
        $new_property['ID'] = SQLInsert('properties', $new_property);
        $pvalues[$i]['PROPERTY_ID'] = $new_property['ID'];
        unset($pvalues[$i]['PROPERTY_TITLE']);
        SQLUpdate('pvalues', $pvalues[$i]);
    }
    // step 2. apply matched properties of new class
    $properties = $obj->getParentProperties($rec['CLASS_ID'], '', 1);
    $total = count($properties);
    for ($i = 0; $i < $total; $i++) {
        $pvalue = SQLSelectOne("SELECT pvalues.* FROM pvalues LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID WHERE properties.CLASS_ID=0 AND pvalues.OBJECT_ID='" . $rec['ID'] . "' AND properties.TITLE = '" . DBSafe($properties[$i]['TITLE']) . "'");
        if ($pvalue['ID']) {
            $old_prop = $pvalue['PROPERTY_ID'];
            $pvalue['PROPERTY_ID'] = $properties[$i]['ID'];
            SQLUpdate('pvalues', $pvalue);
            SQLExec("DELETE FROM properties WHERE ID='" . $old_prop . "'");
        }
    }
}

function checkOperationsQueue($topic)
{
    $data = SQLSelect("SELECT * FROM operations_queue WHERE TOPIC='" . DBSafe($topic) . "' ORDER BY EXPIRE");
    if ($data[0]['TOPIC']) {
        SQLExec("DELETE FROM operations_queue WHERE TOPIC='" . DBSafe($topic) . "'");
    }
    return $data;
}

function addToOperationsQueue($topic, $dataname, $datavalue = '', $uniq = false, $ttl = 60)
{
    $rec = array();
    $rec['TOPIC'] = $topic;
    $rec['DATANAME'] = $dataname;
    if (strlen($datavalue) < 255) {
        $rec['DATAVALUE'] = $datavalue;
    }
    $rec['EXPIRE'] = date('Y-m-d H:i:s', time() + $ttl);
    if ($uniq) {
        SQLExec("DELETE FROM operations_queue WHERE TOPIC='" . DBSafe($rec['TOPIC']) . "' AND DATANAME='" . DBSafe($rec['DATANAME']) . "'");
    }
    $rec['ID'] = SQLInsert('operations_queue', $rec);
    SQLExec("DELETE FROM operations_queue WHERE EXPIRE<NOW();");
    return $rec['ID'];
}
