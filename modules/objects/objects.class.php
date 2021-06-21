<?php
/**
 * Objects
 *
 * Objects
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.4 (wizard, 12:05:51 [May 22, 2009])
 */
//
//
class objects extends module
{

    /**
     * objects
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "objects";
        $this->title = "<#LANG_MODULE_OBJECT_INSTANCES#>";
        $this->module_category = "<#LANG_SECTION_OBJECTS#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1)
    {
        $data = array();
        if (IsSet($this->id)) {
            $data["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $data["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $data["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
            $data["tab"] = $this->tab;
        }
        return parent::saveParams($data);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['TAB'] = $this->tab;
        if ($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'objects' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_objects') {
                $this->search_objects($out);
            }

            if ($this->view_mode == 'clone' && $this->id) {
                $this->clone_object($this->id);
            }


            if ($this->view_mode == 'edit_objects') {
                $this->edit_objects($out, $this->id);
            }
            if ($this->view_mode == 'delete_objects') {
                $this->delete_objects($this->id);
                $this->redirect("?");
            }
        }
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function clone_object($id)
    {

        $rec = SQLSelectOne("SELECT * FROM objects WHERE ID='" . $id . "'");
        $rec['TITLE'] = $rec['TITLE'] . ' (copy)';
        unset($rec['ID']);
        $rec['SYSTEM'] = '';
        $rec['ID'] = SQLInsert('objects', $rec);

        $seen_pvalues = array();
        $properties = SQLSelect("SELECT * FROM properties WHERE OBJECT_ID='" . $id . "'");
        $total = count($properties);
        for ($i = 0; $i < $total; $i++) {
            $p_id = $properties[$i]['ID'];
            unset($properties[$i]['ID']);
            $properties[$i]['OBJECT_ID'] = $rec['ID'];
            $properties[$i]['ID'] = SQLInsert('properties', $properties[$i]);
            $p_value = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . $p_id . "'");
            if ($p_value['ID']) {
                $seen_pvalues[$p_value['ID']] = 1;
                unset($p_value['ID']);
                $p_value['PROPERTY_ID'] = $properties[$i]['ID'];
                $p_value['OBJECT_ID'] = $rec['ID'];
                SQLInsert('pvalues', $p_value);
            }
        }

        $pvalues = SQLSelect("SELECT * FROM pvalues WHERE OBJECT_ID='" . $id . "'");
        $total = count($properties);
        for ($i = 0; $i < $total; $i++) {
            $p_id = $pvalues[$i]['ID'];
            if ($seen_pvalues[$p_id]) {
                continue;
            }
            unset($pvalues[$i]['ID']);
            $pvalues[$i]['OBJECT_ID'] = $rec['ID'];
            $pvalues[$i]['ID'] = SQLInsert('pvalues', $pvalues[$i]);
        }

        $methods = SQLSelect("SELECT * FROM methods WHERE OBJECT_ID='" . $id . "'");
        $total = count($methods);
        for ($i = 0; $i < $total; $i++) {
            unset($methods[$i]['ID']);
            $methods[$i]['OBJECT_ID'] = $rec['ID'];
            $methods[$i]['ID'] = SQLInsert('methods', $methods[$i]);
        }

        $this->redirect("?view_mode=edit_objects&id=" . $rec['ID']);

    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {

        if ($this->ajax) {

            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');

            global $op;
            global $id;
            $res = array();
            if ($op == 'get_object') {
                $res = $this->processObject($id);
            }
            echo json_encode($res);

            global $db;
            exit;
        }

        if ($this->class) {
            $objects = getObjectsByClass($this->class);
            if (!$this->code) {
                $template = '#title# <i>#description#</i><br/>';
            } else {
                $template = $this->code;
            }
            $result = '';
            if ($objects[0]['ID']) {
                $total = count($objects);
                for ($i = 0; $i < $total; $i++) {
                    $objects[$i] = SQLSelectOne("SELECT * FROM objects WHERE ID='" . $objects[$i]['ID'] . "'");
                    $line = $template;
                    $line = preg_replace('/\#title\#/is', $objects[$i]['TITLE'], $line);
                    $line = preg_replace('/\#description\#/is', $objects[$i]['DESCRIPTION'], $line);
                    if (preg_match_all('/\#([\w\d_-]+?)\#/is', $line, $m)) {
                        $totalm = count($m[0]);
                        for ($im = 0; $im < $totalm; $im++) {
                            $property = trim($objects[$i]['TITLE'] . '.' . $m[1][$im]);
                            $line = str_replace($m[0][$im], getGlobal($property), $line);
                        }
                    }
                    $result .= $line;
                }
            }
            $out['RESULT'] = $result;
        }

    }

    /**
     * objects search
     *
     * @access public
     */
    function search_objects(&$out)
    {
        require(DIR_MODULES . $this->name . '/objects_search.inc.php');
    }

    /**
     * objects edit/add
     *
     * @access public
     */
    function edit_objects(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/objects_edit.inc.php');
    }

    /**
     * objects delete record
     *
     * @access public
     */
    function delete_objects($id)
    {
        $rec = SQLSelectOne("SELECT * FROM objects WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM history WHERE OBJECT_ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM methods WHERE OBJECT_ID='" . $rec['ID'] . "'");
        $pvalues = SQLSelect("SELECT * FROM pvalues WHERE OBJECT_ID=" . $rec['ID']);
        foreach ($pvalues as $pvalue) {
            if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
                $history_table = createHistoryTable($pvalue['ID']);
            } else {
                $history_table = 'phistory';
            }
            SQLExec("DELETE FROM $history_table WHERE VALUE_ID=" . $pvalue['ID']);
            SQLExec("DELETE FROM pvalues WHERE ID='" . $pvalue['ID'] . "'");
        }
        SQLExec("DELETE FROM properties WHERE OBJECT_ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM objects WHERE ID='" . $rec['ID'] . "'");
    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function loadObject($id)
    {
        $rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . (int)$id);
        if (IsSet($rec['ID'])) {
            $this->id = $rec['ID'];
            $this->object_title = $rec['TITLE'];
            $this->class_id = $rec['CLASS_ID'];
            if ($this->class_id) {
                $class_rec = SQLSelectOne("SELECT ID,TITLE FROM classes WHERE ID=" . $this->class_id);
                $this->class_title = $class_rec['TITLE'];
            }
            $this->description = $rec['DESCRIPTION'];
            $this->location_id = $rec['LOCATION_ID'];
            if (preg_match('/^sdevice(.+?)/', $rec['SYSTEM'], $m)) {
                $this->device_id = $m[1];
            }
            //$this->keep_history=$rec['KEEP_HISTORY'];
        } else {
            return false;
        }

    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getParentProperties($id, $def = '', $include_self = 0)
    {
        startMeasure('getParentProperties');
        $class = SQLSelectOne("SELECT PARENT_ID FROM classes WHERE ID='" . (int)$id . "'");

        global $class_properties_cached;
        if (isset($class_properties_cached[$id])) {
            $properties = $class_properties_cached[$id];
        } else {
            $properties = SQLSelect("SELECT properties.*, classes.TITLE AS CLASS_TITLE FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID WHERE CLASS_ID='" . $id . "' AND OBJECT_ID=0");
            $class_properties_cached[$id] = $properties;
        }

        if ($include_self) {
            $res = $properties;
        } else {
            $res = array();
        }

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

        endMeasure('getParentProperties');
        if ($class['PARENT_ID']) {
            $p_res = $this->getParentProperties($class['PARENT_ID'], $def);
            if ($p_res[0]['ID']) {
                $res = array_merge($res, $p_res);
            }
        }

        return $res;

    }

    function getParentMethods($id, $def = '', $include_self = 0)
    {
        $class = SQLSelectOne("SELECT PARENT_ID FROM classes WHERE ID='" . (int)$id . "'");

        $methods = SQLSelect("SELECT methods.*, classes.TITLE AS CLASS_TITLE FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID WHERE CLASS_ID='" . $id . "' AND OBJECT_ID=0");

        if ($include_self) {
            $res = $methods;
        } else {
            $res = array();
        }


        if (!is_array($def)) {
            $def = array();
            foreach ($methods as $p) {
                $def[] = $p['TITLE'];
            }
        }

        foreach ($methods as $p) {
            if (!in_array($p['TITLE'], $def)) {
                $res[] = $p;
                $def[] = $p['TITLE'];
            }
        }

        if ($class['PARENT_ID']) {
            $p_res = $this->getParentMethods($class['PARENT_ID'], $def);
            if ($p_res[0]['ID']) {
                $res = array_merge($res, $p_res);
            }
        }

        return $res;

    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getMethodByName($name, $class_id, $id)
    {

        if ($id) {
            $meth = SQLSelectOne("SELECT ID FROM methods WHERE OBJECT_ID='" . (int)$id . "' AND TITLE = '" . DBSafe($name) . "'");
            if ($meth['ID']) {
                return $meth['ID'];
            }
        }

        //include_once(DIR_MODULES.'classes/classes.class.php');
        //$cl=new classes();
        //$meths=$cl->getParentMethods($class_id, '', 1);
        $meths = $this->getParentMethods($class_id, '', 1);

        $total = count($meths);
        for ($i = 0; $i < $total; $i++) {
            if (strtolower($meths[$i]['TITLE']) == strtolower($name)) {
                return $meths[$i]['ID'];
            }
        }
        return false;

    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function raiseEvent($name, $params = 0, $parent = 0)
    {
        if (!is_array($params)) {
            $params = array();
        }
        $params['raiseEvent'] = '1';
        $this->callMethodSafe($name, $params);
    }

    function callClassMethod($name, $params = 0)
    {
        $this->callMethod($name, $params, 1);
    }

    function callMethodSafe($name, $params = 0)
    {
        startMeasure('callMethodSafe');
        $current_call = $this->object_title . '.' . $name;
        $call_stack = array();
        if (is_array($params)) {
            if (isset($params['m_c_s']) && is_array($params['m_c_s']) && !empty($params['m_c_s'])) {
                $call_stack = $params['m_c_s'];
                unset($params['m_c_s']);
            }
            if (isset($params['r_s_m']) && !empty($params['r_s_m'])) {
                $run_SafeMethod = $params['r_s_m'];
                unset($params['r_s_m']);
            }
            if (isset($params['raiseEvent']) && !empty($params['raiseEvent'])) {
                $raiseEvent = $params['raiseEvent'];
                unset($params['raiseEvent']);
            }
            $current_call .= '.' . md5(json_encode($params));
        }
        if (IsSet($_SERVER['REQUEST_URI']) && ($_SERVER['REQUEST_URI'] != '')) {
            if (isset($_GET['m_c_s']) && is_array($_GET['m_c_s']) && !empty($_GET['m_c_s'])) {
                $call_stack = $_GET['m_c_s'];
                unset($params['m_c_s']);
            }
            if (isset($_GET['raiseEvent']) && !empty($_GET['raiseEvent'])) {
                $raiseEvent = $_GET['raiseEvent'];
                unset($params['raiseEvent']);
            }
            if (isset($_GET['r_s_m']) && !empty($_GET['r_s_m'])) {
                $run_SafeMethod = $_GET['r_s_m'];
                unset($params['r_s_m']);
            }
        }

        if (is_array($call_stack) && in_array($current_call, $call_stack)) {
            $call_stack[] = $current_call;
            DebMes("Warning: cross-linked call of " . $current_call . "\nlog:\n" . implode(" -> \n", $call_stack));
            return 0;
        }

        if (!is_array($params)) {
            $params = array();
        }

        $call_stack[] = $current_call;
        $params['raiseEvent'] = $raiseEvent;
        $params['m_c_s'] = $call_stack;
        $params['r_s_m'] = $run_SafeMethod;
        if (IsSet($_SERVER['REQUEST_URI']) && ($_SERVER['REQUEST_URI'] != '') && ((!$raiseEvent && $run_SafeMethod) || (defined('LOWER_BACKGROUND_PROCESSES') && LOWER_BACKGROUND_PROCESSES == 1))) {
            $result = $this->callMethod($name, $params);
        } else {
            $params['r_s_m'] = 1;
            $result = callAPI('/api/method/' . urlencode($this->object_title . '.' . $name), 'GET', $params);
        }
        endMeasure('callMethodSafe');
        return $result;
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function callMethod($name, $params = 0, $parentClassId = 0)
    {

        if (!$parentClassId) {
            verbose_log("Method [" . $this->object_title . ".$name] (" . (is_array($params) ? json_encode($params) : '') . ")");
            //dprint("Method [" . $this->object_title . ".$name] (" . (is_array($params) ? json_encode($params) : '') . ")",false);
        } else {
            verbose_log("Class method [" . $this->class_title . '/' . $this->object_title . ".$name] (" . (is_array($params) ? json_encode($params) : '') . ")");
            //dprint("Class method [" . $this->class_title . '/' . $this->object_title . ".$name] (" . (is_array($params) ? json_encode($params) : '') . ")",false);
        }
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //echo "<hr>";
        startMeasure('callMethod');

        $original_method_name = $this->object_title . '.' . $name;

        startMeasure('callMethod (' . $original_method_name . ')');

        if (!$parentClassId) {
            $id = $this->getMethodByName($name, $this->class_id, $this->id);
            $parentClassId = $this->class_id;
        } else {
            $id = $this->getMethodByName($name, $parentClassId, 0);
        }

        if ($id) {

            $method = SQLSelectOne("SELECT * FROM methods WHERE ID='" . $id . "'");
            $update_rec = array('ID' => $method['ID']);
            $update_rec['EXECUTED'] = date('Y-m-d H:i:s');
            if (defined('CALL_SOURCE')) {
                $source = CALL_SOURCE;
            } else {
                $source = urldecode($_SERVER['REQUEST_URI']);
            }
            if (strlen($source) > 250) {
                $source = substr($source, 0, 250) . '...';
            }
            $update_rec['EXECUTED_SRC'] = $source;


            if (!$method['OBJECT_ID']) {
                if (!$params) {
                    $params = array();
                }
                $params['ORIGINAL_OBJECT_TITLE'] = $this->object_title;
            }

            if ($params) {
                $saved_params = $params;
                unset($params['r_s_m']);
                unset($saved_params['m_c_s']);
                unset($saved_params['SOURCE']);
                $update_rec['EXECUTED_PARAMS'] = json_encode($saved_params, JSON_UNESCAPED_UNICODE);
                if (strlen($update_rec['EXECUTED_PARAMS']) > 250) {
                    $update_rec['EXECUTED_PARAMS'] = substr($update_rec['EXECUTED_PARAMS'], 0, 250);
                }
            }
            SQLUpdate('methods', $update_rec);

            if ($method['OBJECT_ID'] && $method['CALL_PARENT'] == 1) {
                // call class method
                startMeasure('callParentMethod');
                $parent_success = $this->callMethod($name, $params, $this->class_id);
                endMeasure('callParentMethod');
            } elseif ($method['CALL_PARENT'] == 1) {
                $parentClass = SQLSelectOne("SELECT ID, PARENT_ID FROM classes WHERE ID=" . (int)$parentClassId);
                if ($parentClass['PARENT_ID']) {
                    startMeasure('callParentMethod');
                    $parent_success = $this->callMethod($name, $params, $parentClass['PARENT_ID']);
                    endMeasure('callParentMethod');
                }
            }

            if ($method['SCRIPT_ID']) {
                /*
                 $script=SQLSelectOne("SELECT * FROM scripts WHERE ID='".$method['SCRIPT_ID']."'");
                 $code=$script['CODE'];
                */
                runScriptSafe($method['SCRIPT_ID']);
            } else {
                $code = $method['CODE'];
            }


            if ($code != '') {
               if (defined('PYTHON_PATH') and isItPythonCode($code)) {
					echo ($code);
                    python_run_code($code, $params, $this->object_title);
                } else {
                    try {
                        $success = eval($code);
                        if ($success === false) {
                            //getLogger($this)->error(sprintf('Error in "%s.%s" method.', $this->object_title, $name));
                            registerError('method', sprintf('Exception in "%s.%s" method.', $this->object_title, $name));
                        }
                    } catch (Exception $e) {
                        //getLogger($this)->error(sprintf('Exception in "%s.%s" method', $this->object_title, $name), $e);
                        registerError('method', sprintf('Exception in "%s.%s" method ' . $e->getMessage(), $this->object_title, $name));
                    }
                }
            }
            endMeasure('callMethod', 1);
            endMeasure('callMethod (' . $original_method_name . ')', 1);
            if ($method['OBJECT_ID'] && $method['CALL_PARENT'] == 2) {
                startMeasure('callParentMethod');
                $parent_success = $this->callMethod($name, $params, $this->class_id);
                endMeasure('callParentMethod');
            } elseif ($method['CALL_PARENT'] == 2) {
                $parentClass = SQLSelectOne("SELECT ID, PARENT_ID FROM classes WHERE ID=" . (int)$parentClassId);
                if ($parentClass['PARENT_ID']) {
                    startMeasure('callParentMethod');
                    $parent_success = $this->callMethod($name, $params, $parentClass['PARENT_ID']);
                    endMeasure('callParentMethod');
                }
            } else {
                $parent_success = true;
            }

            if (isset($success)) {
                return $success;
            } else {
                return $parent_success;
            }

        } else {
            endMeasure('callMethod (' . $original_method_name . ')', 1);
            endMeasure('callMethod', 1);
            return false;
        }
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getPropertyByName($name, $class_id, $object_id)
    {

        $cached_name = 'P:' . $class_id . '.' . $object_id . '.' . $name;
        $cached_value = checkFromCache($cached_name);
        if ($cached_value !== false) {
            return $cached_value;
        }

        $rec = SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID='" . (int)$object_id . "' AND TITLE = '" . DBSafe($name) . "'");
        if (isset($rec['ID'])) {
            saveToCache($cached_name,$rec['ID']);
            return $rec['ID'];
        }
        $props = $this->getParentProperties($class_id, '', 1);
        $total = count($props);
        for ($i = 0; $i < $total; $i++) {
            if (strtolower($props[$i]['TITLE']) == strtolower($name)) {
                saveToCache($cached_name,$props[$i]['ID']);
                return $props[$i]['ID'];
            }
        }
        return false;
    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getProperty($property, $cache_checked = false)
    {
        if (!$this->object_title) return false;

        $property = trim($property);
        $cached_name = 'MJD:' . $this->object_title . '.' . $property;

        if ($property == 'object_title') {
            return $this->object_title;
        } elseif ($property == 'object_description') {
            return $this->description;
        } elseif ($property == 'object_id') {
            return $this->id;
        } elseif ($property == 'class_title') {
            return $this->class_title;
        }

        if (!$cache_checked) {
            $cached_value = checkFromCache($cached_name);
            if ($cached_value !== false) {
                return $cached_value;
            }
        }

        $value = SQLSelectOne("SELECT VALUE FROM pvalues WHERE PROPERTY_NAME = '" . DBSafe($this->object_title . '.' . $property) . "'");
        if (isset($value['VALUE'])) {
            startMeasure('getPropertyCached2');
            endMeasure('getPropertyCached2', 1);
            endMeasure('getProperty (' . $property . ')', 1);
            endMeasure('getProperty', 1);
            saveToCache($cached_name, $value['VALUE']);
            return $value['VALUE'];
        }


        if ($property == 'location_title') {
            $value = current(SQLSelectOne("SELECT TITLE FROM locations WHERE ID=" . (int)$this->location_id));
            saveToCache($cached_name, $value);
            return $value;
        }


        $id = $this->getPropertyByName($property, $this->class_id, $this->id);
        startMeasure('getPropertyAll');
        startMeasure('getProperty (' . $property . ')');

        if ($id) {
            $value = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . (int)$id . "' AND OBJECT_ID='" . (int)$this->id . "'");
            if (!$value['PROPERTY_NAME'] && $this->object_title) {
                $value['PROPERTY_NAME'] = $this->object_title . '.' . $property;
                SQLUpdate('pvalues', $value);
            }
        } else {
            $value['VALUE'] = false;
        }
        endMeasure('getProperty (' . $property . ')', 1);
        endMeasure('getPropertyAll', 1);
        if (!isset($value['VALUE'])) {
            $value['VALUE'] = false;
        }
        saveToCache($cached_name, $value['VALUE']);
        return $value['VALUE'];
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function setProperty($property, $value, $no_linked = 0, $source = '')
    {

        if (!preg_match('/cycle/is', $property) && function_exists('verbose_log')) {
            verbose_log('Property [' . $this->object_title . '.' . $property . '] set to \'' . $value . '\'');
        }
        startMeasure('setProperty');
        startMeasure('setProperty (' . $property . ')');

        $property = trim($property);

        if (is_null($value)) {
            $value = '';
        }

        if (!$source && is_string($no_linked)) {
            $source = $no_linked;
            $no_linked = 0;
        }
        if (!$source && defined('CALL_SOURCE')) {
            $source = CALL_SOURCE;
        }
        if (!$source && isset($_SERVER['REQUEST_URI'])) {
            $source = urldecode($_SERVER['REQUEST_URI']);
        }
        if (strlen($source) > 250) {
            $source = substr($source, 0, 250) . '...';
        }

        if (defined('TRACK_DATA_CHANGES') && TRACK_DATA_CHANGES == 1) {
            $save = 1;

            if (!is_numeric(trim($value))) {
                $save = 0;
            }

            if (defined('TRACK_DATA_CHANGES_IGNORE') && TRACK_DATA_CHANGES_IGNORE != '' && $save) {
                $tmp = explode(',', TRACK_DATA_CHANGES_IGNORE);
                $total = count($tmp);
                for ($i = 0; $i < $total; $i++) {
                    $regex = trim($tmp[$i]);
                    if (preg_match('/' . $regex . '/is', $this->object_title . '.' . $property)) {
                        $save = 0;
                        break;
                    }
                }
            }
            if ($save) {
                if ($this->location_id) {
                    $location = current(SQLSelectOne("SELECT TITLE FROM locations WHERE ID=" . (int)$this->location_id));
                } else {
                    $location = '';
                }


                if (defined('SETTINGS_SYSTEM_DEBMES_PATH') && SETTINGS_SYSTEM_DEBMES_PATH != '') {
                    $path = SETTINGS_SYSTEM_DEBMES_PATH;
                } elseif (defined('LOG_DIRECTORY') && LOG_DIRECTORY != '') {
                    $path = LOG_DIRECTORY;
                } else {
                    $path = ROOT . 'cms/debmes';
                }

                $today_file = $path . '/' . date('Y-m-d') . '.data';
                $f = fopen($today_file, "a+");
                if ($f) {
                    fputs($f, date("Y-m-d H:i:s"));
                    fputs($f, "\t" . $this->object_title . '.' . $property . "\t" . trim($value) . "\t" . trim($source) . "\t" . trim($location) . "\n");
                    fclose($f);
                    @chmod($today_file, 0666);
                }
            }
        }

        startMeasure('getPropertyByName');
        $id = $this->getPropertyByName($property, $this->class_id, $this->id);
        endMeasure('getPropertyByName');
        $old_value = '';

        $cached_name = 'MJD:' . $this->object_title . '.' . $property;

        startMeasure('setproperty_update');
        if ($id) {
            $prop = SQLSelectOne("SELECT * FROM properties WHERE ID='" . $id . "'");

            if ($prop['VALIDATION_TYPE'] == 1) {
                if (!is_numeric($value)) return false;
                if ($prop['VALIDATION_NUM_MIN'] != '' && (float)$value < (float)$prop['VALIDATION_NUM_MIN']) {
                    return false;
                }
                if ($prop['VALIDATION_NUM_MAX'] != '' && (float)$value > (float)$prop['VALIDATION_NUM_MAX']) {
                    return false;
                }
            }
            if ($prop['VALIDATION_TYPE'] == 2) {
                if ($value != '1' && $value != '0') {
                    return false;
                }
            }
            if ($prop['VALIDATION_TYPE'] == 3) {
                $items = explode(',', $prop['VALIDATION_LIST']);
                if (!in_array(mb_strtolower($value, 'UTF-8'), $items)) return false;
            }
            if ($prop['VALIDATION_TYPE'] == 100) {
                eval($prop['VALIDATION_CODE']);
                if (is_null($value)) return false;
            }

            $property = $prop['TITLE'];
            startMeasure('setproperty_update_getvalue');
            $v = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID=" . (int)$id . " AND OBJECT_ID=" . (int)$this->id);
            endMeasure('setproperty_update_getvalue');
            $old_value = $v['VALUE'];

            if ($prop['DATA_TYPE'] == 5 && $value != $old_value) { // image
                $path_parts = pathinfo($value);
                $extension = strtolower($path_parts['extension']);
                if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'png' && $extension != 'gif') {
                    $extension = 'jpg';
                }
                $image_file_name = date('Ymd_His') . '.' . $extension;
                if (preg_match('/^http.+/', $value)) {
                    $image_data = getURL($value);
                    @mkdir(ROOT . 'cms/images/' . $prop['ID'], 0777);
                    SaveFile(ROOT . 'cms/images/' . $prop['ID'] . '/' . $image_file_name, $image_data);
                    $value = $prop['ID'] . '/' . $image_file_name;
                } elseif (file_exists($value)) {
                    @mkdir(ROOT . 'cms/images/' . $prop['ID'], 0777);
                    copyFile($value, ROOT . 'cms/images/' . $prop['ID'] . '/' . $image_file_name);
                    $value = $prop['ID'] . '/' . $image_file_name;
                } else {
                    $value = '';
                }
                if ($value != '' && file_exists(ROOT . 'cms/images/' . $value)) {
                    $lst = GetImageSize(ROOT . 'cms/images/' . $value);
                    //$image_width=$lst[0];
                    //$image_height=$lst[1];
                    $image_format = $lst[2];
                    if (!$image_format) {
                        @unlink(ROOT . 'cms/images/' . $value);
                        $value = '';
                    }
                } else {
                    $value = '';
                }
                if ($value != '' && $old_value != '' && !$prop['KEEP_HISTORY'] && file_exists(ROOT . 'cms/images/' . $old_value)) {
                    @unlink(ROOT . 'cms/images/' . $old_value);
                }
                if ($value == '') $value = $old_value;
            }

            $v['VALUE'] = $value . '';
            $v['SOURCE'] = $source . '';
            if (!$v['PROPERTY_NAME']) {
                $v['PROPERTY_NAME'] = $this->object_title . '.' . $property;
            }
            if ($v['ID']) {
                $v['UPDATED'] = date('Y-m-d H:i:s');
                SQLUpdate('pvalues', $v);
            } else {
                $v['PROPERTY_ID'] = $id;
                $v['OBJECT_ID'] = $this->id;
                $v['VALUE'] = $value . '';
                $v['SOURCE'] = $source . '';
                $v['UPDATED'] = date('Y-m-d H:i:s');
                $v['ID'] = SQLInsert('pvalues', $v);
            }
        } else {
            $prop = array();
            $prop['OBJECT_ID'] = $this->id;
            $prop['TITLE'] = $property;
            $prop['ID'] = SQLInsert('properties', $prop);

            $v['PROPERTY_NAME'] = $this->object_title . '.' . $property;
            $v['PROPERTY_ID'] = $prop['ID'];
            $v['OBJECT_ID'] = $this->id;
            $v['VALUE'] = $value . '';
            $v['SOURCE'] = $source . '';
            $v['UPDATED'] = date('Y-m-d H:i:s');
            $v['ID'] = SQLInsert('pvalues', $v);
        }
        endMeasure('setproperty_update');

        saveToCache($cached_name, $value);

        $p_lower = strtolower($property);
        if (!defined('DISABLE_SIMPLE_DEVICES') &&
            isset($this->device_id) &&
            ($p_lower == 'value' ||
                $p_lower == 'valuehumidity' ||
                $p_lower == 'status' ||
                $p_lower == 'disabled' ||
                $p_lower == 'level' ||
                $p_lower == 'volume' ||
                $p_lower == 'channel' ||
                $p_lower == 'mode' ||
                $p_lower == 'thermostatmode' ||
                $p_lower == 'fanspeedmode' ||
                $p_lower == 'currenttargetvalue') //
        ) {
            addToOperationsQueue('connect_device_data', $this->object_title . '.' . $property, $value, true);
        }

        if (IsSet($v['LINKED_MODULES']) && $v['LINKED_MODULES']) { // TO-DO !
            if (!is_array($no_linked) && $no_linked) {
                return;
            } elseif (!is_array($no_linked)) {
                $no_linked = array();
            }


            $tmp = explode(',', $v['LINKED_MODULES']);
            $total = count($tmp);

            startMeasure('linkedModulesProcessing');
            for ($i = 0; $i < $total; $i++) {
                $linked_module = trim($tmp[$i]);
                if (isset($no_linked[$linked_module])) {
                    continue;
                }
                startMeasure('linkedModule' . $linked_module);
                if (file_exists(DIR_MODULES . $linked_module . '/' . $linked_module . '.class.php')) {
                    $params = array();
                    $params['object'] = $this->object_title;
                    $params['property'] = $property;
                    $params['value'] = $value;
                    $url = '/api/modulePropertySet/' . urlencode($linked_module);
                    callAPI($url, 'GET', $params);
                }
                endMeasure('linkedModule' . $linked_module);
            }
            endMeasure('linkedModulesProcessing');
        }

        if (function_exists('postToWebSocketQueue')) {
            startMeasure('setproperty_postwebsocketqueue');
            if ($old_value !== $value) {
                postToWebSocketQueue($this->object_title . '.' . $property, $value);
            }
            endMeasure('setproperty_postwebsocketqueue');
        }

        if (IsSet($prop['KEEP_HISTORY']) && ($prop['KEEP_HISTORY'] > 0)) {
            $q_rec = array();
            $q_rec['VALUE_ID'] = $v['ID'];
            $q_rec['ADDED'] = date('Y-m-d H:i:s');
            $q_rec['VALUE'] = $value . '';
            $q_rec['SOURCE'] = $source . '';
            $q_rec['OLD_VALUE'] = $old_value;
            $q_rec['KEEP_HISTORY'] = $prop['KEEP_HISTORY'];
            SQLInsert('phistory_queue', $q_rec);
        }

        if (isset($prop['ONCHANGE']) && $prop['ONCHANGE']) {
            global $property_linked_history;
            if (!$property_linked_history[$this->object_title . '.' . $property][$prop['ONCHANGE']]) {
                $property_linked_history[$this->object_title . '.' . $property][$prop['ONCHANGE']] = 1;
                $params = array();
                $params['PROPERTY'] = $property;
                $params['NEW_VALUE'] = (string)$value;
                $params['OLD_VALUE'] = (string)$old_value;
                $params['SOURCE'] = (string)$source;
                //$this->callMethod($prop['ONCHANGE'], $params);
                //$this->callMethodSafe($prop['ONCHANGE'], $params);
                $this->raiseEvent($prop['ONCHANGE'], $params);
                unset($property_linked_history[$this->object_title . '.' . $property][$prop['ONCHANGE']]);
            }
        }

        endMeasure('setProperty (' . $property . ')', 1);
        endMeasure('setProperty', 1);

    }

    function getWatchedProperties($objects)
    {
        $properties = array();
        $ids = explode(',', $objects);
        include_once(DIR_MODULES . 'classes/classes.class.php');
        $cl = new classes();

        foreach ($ids as $object_id) {
            $this->loadObject($object_id);
            $props = $cl->getParentProperties($this->class_id, '', 1);
            $my_props = SQLSelect("SELECT * FROM properties WHERE OBJECT_ID='" . (int)$object_id . "'");
            if ($my_props[0]['ID']) {
                foreach ($my_props as $p) {
                    $props[] = $p;
                }
            }
            if (is_array($props)) {
                foreach ($props as $k => $v) {
                    if (substr($v['TITLE'], 0, 1) == '_') continue;
                    $properties[] = array('PROPERTY' => mb_strtolower($this->object_title . '.' . $v['TITLE'], 'UTF-8'), 'OBJECT_ID' => $object_id);
                }
            }
        }
        return $properties;
    }

    function processObject($object_id)
    {
        $object_rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . (int)$object_id);
        $result = array('HTML' => '', 'OBJECT_ID' => $object_rec['ID']);
        $template = getObjectClassTemplate($object_rec['TITLE']);
        $result['HTML'] = processTitle($template, $this);
        return $result;
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($parent_name = "")
    {
        unsubscribeFromEvent($this->name, 'DAILY');
        parent::install($parent_name);
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        unsubscribeFromEvent($this->name, 'DAILY');
        SQLDropTable('objects');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {

        //SQLDropTable('cached_values');
        $sqlQuery = "CREATE TABLE IF NOT EXISTS `cached_values`
               (`KEYWORD`   CHAR(100) NOT NULL,
                `DATAVALUE` CHAR(255) NOT NULL,
                PRIMARY KEY (`KEYWORD`)
               ) ENGINE = MEMORY DEFAULT CHARSET=utf8;";
        SQLExec($sqlQuery);

        $sqlQuery = "CREATE TABLE IF NOT EXISTS `cached_ws`
               (`PROPERTY`   CHAR(100) NOT NULL,
                `DATAVALUE` VARCHAR(20000) NOT NULL,
                `POST_ACTION`   CHAR(100) NOT NULL,
                `ADDED`    DATETIME  NOT NULL,
                PRIMARY KEY (`PROPERTY`)
               ) ENGINE = MEMORY DEFAULT CHARSET=utf8;";
        SQLExec($sqlQuery);

        $sqlQuery = "CREATE TABLE IF NOT EXISTS `operations_queue` 
              (`TOPIC`   CHAR(255) NOT NULL,
               `DATANAME` CHAR(255) NOT NULL,
               `DATAVALUE` CHAR(255) NOT NULL,
               `EXPIRE`    DATETIME  NOT NULL
              ) ENGINE = MEMORY DEFAULT CHARSET=utf8;";
        SQLExec($sqlQuery);

        // Если вы дошли до этой записи, при проявлении ошибки, то данная ошибка проявляется на MariDB
        $sqlQuery = "ALTER TABLE operations_queue DROP COLUMN `ID`;";
        SQLExec($sqlQuery, true);


        /*
        objects - Objects
        */
        $data = <<<EOD
 objects: ID int(10) unsigned NOT NULL auto_increment
 objects: SYSTEM varchar(255) NOT NULL DEFAULT ''
 objects: TITLE varchar(255) NOT NULL DEFAULT ''
 objects: CLASS_ID int(10) NOT NULL DEFAULT '0'
 objects: DESCRIPTION text
 objects: LOCATION_ID int(10) NOT NULL DEFAULT '0'

 properties: ID int(10) unsigned NOT NULL auto_increment
 properties: CLASS_ID int(10) NOT NULL DEFAULT '0'
 properties: OBJECT_ID int(10) NOT NULL DEFAULT '0'
 properties: SYSTEM varchar(255) NOT NULL DEFAULT ''
 properties: TITLE varchar(255) NOT NULL DEFAULT ''
 properties: KEEP_HISTORY int(10) NOT NULL DEFAULT '0'
 properties: DATA_KEY int(3) NOT NULL DEFAULT '0' 
 properties: DATA_TYPE int(3) NOT NULL DEFAULT '0' 
 properties: DESCRIPTION text
 properties: VALIDATION_TYPE int(3) NOT NULL DEFAULT '0'
 properties: VALIDATION_NUM_MIN varchar(20) NOT NULL DEFAULT ''
 properties: VALIDATION_NUM_MAX varchar(20) NOT NULL DEFAULT ''
 properties: VALIDATION_LIST varchar(255) NOT NULL DEFAULT ''
 properties: VALIDATION_CODE text
 properties: ONCHANGE varchar(255) NOT NULL DEFAULT ''
 properties: INDEX (CLASS_ID)
 properties: INDEX (OBJECT_ID)
 
 pvalues: ID int(10) unsigned NOT NULL auto_increment
 pvalues: PROPERTY_NAME varchar(100) NOT NULL DEFAULT ''
 pvalues: PROPERTY_ID int(10) NOT NULL DEFAULT '0'
 pvalues: OBJECT_ID int(10) NOT NULL DEFAULT '0'
 pvalues: VALUE text
 pvalues: UPDATED datetime
 pvalues: SOURCE varchar(255) NOT NULL DEFAULT ''
 pvalues: LINKED_MODULES varchar(255) NOT NULL DEFAULT ''
 pvalues: INDEX (PROPERTY_ID)
 pvalues: INDEX (OBJECT_ID)
 pvalues: INDEX (PROPERTY_NAME) 

 phistory: ID int(10) unsigned NOT NULL auto_increment
 phistory: VALUE_ID int(10) unsigned NOT NULL DEFAULT '0'
 phistory: SOURCE varchar(255) NOT NULL DEFAULT ''
 phistory: ADDED datetime
 phistory: INDEX (VALUE_ID)

 phistory_queue: ID int(10) unsigned NOT NULL auto_increment
 phistory_queue: VALUE_ID int(10) unsigned NOT NULL DEFAULT '0'
 phistory_queue: VALUE text
 phistory_queue: OLD_VALUE text
 phistory_queue: KEEP_HISTORY int(10) unsigned NOT NULL DEFAULT '0'
 phistory_queue: SOURCE varchar(255) NOT NULL DEFAULT ''
 phistory_queue: ADDED datetime

EOD;
        parent::dbInstall($data);

        //SQLExec("ALTER TABLE `pvalues` CHANGE `SOURCE` `SOURCE` VARCHAR(255) NOT NULL DEFAULT ''");
        //SQLExec("ALTER TABLE `phistory` CHANGE `SOURCE` `SOURCE` VARCHAR(255) NOT NULL DEFAULT ''");
        //SQLExec("ALTER TABLE `phistory_queue` CHANGE `SOURCE` `SOURCE` VARCHAR(255) NOT NULL DEFAULT ''");

    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDIyLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
