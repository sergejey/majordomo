<?php
/**
 * Devices
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 13:07:05 [Jul 19, 2016])
 */
//
//
class devices extends module
{
    /**
     * devices
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "devices";
        $this->title = "<#LANG_SECTION_DEVICES#>";
        $this->module_category = "<#LANG_SECTION_DEVICES#>";
        $this->checkInstalled();

        $this->setDictionary();

    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 0)
    {
        $p = array();
        if (IsSet($this->id)) {
            $p["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
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

    function setDictionary()
    {
        include_once(DIR_MODULES . 'devices/devices_structure.inc.php');
        include_once(DIR_MODULES . 'devices/devices_structure_links.inc.php');
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

        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } elseif ($this->action == 'link') {
            $this->link($out);
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
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    function link(&$out)
    {
        $ok = 1;
        if ($this->type) {
            $out['TYPE'] = $this->type;
        } else {
            $ok = 0;
        }
        if ($this->source_table) {
            $out['SOURCE_TABLE'] = $this->source_table;
        } else {
            $ok = 0;
        }
        if ($this->source_table_id) {
            $out['SOURCE_TABLE_ID'] = $this->source_table_id;
        } else {
            $ok = 0;
        }
        if ($this->prefix) {
            $out['PREFIX'] = $this->prefix;
        }

        if ($this->add_title) {
            $out['ADD_TITLE'] = urlencode($this->add_title);
        }

        if ($this->linked_object) {
            $device_rec = SQLSelectOne("SELECT ID,TITLE FROM devices WHERE LINKED_OBJECT LIKE '" . DBSafe($this->linked_object) . "'");
            if ($device_rec['TITLE']) {
                $out['TITLE'] = $device_rec['TITLE'];
                if ($this->preview) {
                    $data=$this->processDevice($device_rec['ID']);
                    $out['HTML']=$data['HTML'];
                }
                $out['ID']=$device_rec['ID'];
            }
            $out['LINKED_OBJECT'] = $this->linked_object;
        }
        $out['UNIQ'] = uniqid('dev');
        if ($ok) {
            $out['OK'] = 1;
        }
    }

    function getTypeDetails($type)
    {
        return $this->device_types[$type];
    }

    function getTypeLinks($type)
    {
        $type_details = $this->getTypeDetails($type);
        $res_links = array();
        foreach ($this->device_links as $k => $v) {
            $link_types = explode(',', $k);
            $link_types = array_map('trim', $link_types);
            if (in_array($type_details['CLASS'], $link_types) || in_array($type_details['PARENT_CLASS'], $link_types)) {
                foreach ($v as $link) {
                    $res_links[] = $link;
                }
            }
        }
        return $res_links;
    }

    function getLinkDetails($link_name)
    {
        foreach ($this->device_links as $k => $v) {
            foreach ($v as $link) {
                if ($link['LINK_NAME'] == $link_name) {
                    return $link;
                }
            }
        }
    }

    function getAllGroups($type)
    {
        $groups = SQLSelect("SELECT * FROM devices_groups");
        $res = array();
        $total = count($groups);
        for ($i = 0; $i < $total; $i++) {
            $tmp = explode(',', $groups[$i]['APPLY_TYPES']);
            if (in_array($type, $tmp)) {
                $res[] = $groups[$i];
            }
        }
        return $res;
    }

    function getAllProperties($type)
    {
        $properties = $this->device_types[$type]['PROPERTIES'];
        $parent_class = $this->device_types[$type]['PARENT_CLASS'];
        if ($parent_class != '') {
            foreach ($this->device_types as $k => $v) {
                if ($v['CLASS'] == $parent_class) {
                    $parent_properties = $this->getAllProperties($k);
                    foreach ($parent_properties as $pk => $pv) {
                        if (!isset($properties[$pk])) {
                            $properties[$pk] = $pv;
                        }
                    }
                }
            }
        }
        return $properties;
    }

    function getAllMethods($type)
    {
        $methods = $this->device_types[$type]['METHODS'];
        $parent_class = $this->device_types[$type]['PARENT_CLASS'];
        if ($parent_class != '') {
            foreach ($this->device_types as $k => $v) {
                if ($v['CLASS'] == $parent_class) {
                    $parent_methods = $this->getAllMethods($k);
                    foreach ($parent_methods as $pk => $pv) {
                        if (!isset($methods[$pk])) {
                            $methods[$pk] = $pv;
                        }
                    }
                }
            }
        }
        return $methods;
    }

    function getNewObjectIndex($class, $prefix = '')
    {
        $objects = getObjectsByClass($class);
        if ($prefix!='') {
            $other_objects=SQLSelect("SELECT TITLE FROM objects WHERE TITLE LIKE '".$prefix."%'");
            foreach($other_objects as $obj) {
                $objects[]=$obj;
            }
        }
        $index = 0;
        $total = count($objects);
        for ($i = 0; $i < $total; $i++) {
            if (preg_match('/(\d+)/', $objects[$i]['TITLE'], $m)) {
                $current_index = (int)$m[1];
                if ($current_index > $index) {
                    $index = $current_index;
                }
            }
        }
        $index++;
        if ($index < 10) {
            $index = '0' . $index;
        }
        return $index;
    }

    function processDevice($device_id, $view = '')
    {
        startMeasure('processDevice');
        $device_rec = SQLSelectOne("SELECT * FROM devices WHERE ID=" . (int)$device_id);
        $result = array('HTML' => '', 'DEVICE_ID' => $device_rec['ID']);

        $template = getObjectClassTemplate($device_rec['LINKED_OBJECT'],$view);

        $result['HTML'] = processTitle($template, $this);
        if ($device_rec['TYPE'] == 'camera') {
            $result['HEIGHT'] = 5;
        }
        endMeasure('processDevice');
        return $result;
    }

    function getWatchedProperties($device_id = 0)
    {
        $this->setDictionary();
        $properties = array();
        $qry = 1;
        if ($device_id) {
            $qry .= " AND devices.ID IN (" . $device_id . ")";
        }
        $devices = SQLSelect("SELECT * FROM devices WHERE $qry");
        $total = count($devices);
        for ($i = 0; $i < $total; $i++) {
            if (!$devices[$i]['LINKED_OBJECT']) {
                continue;
            }
            $props = $this->getAllProperties($devices[$i]['TYPE']);
            if (is_array($props)) {
                foreach ($props as $k => $v) {
                    if (substr($k, 0, 1) == '_') continue;
                    $properties[] = array('PROPERTY' => mb_strtolower($devices[$i]['LINKED_OBJECT'] . '.' . $k, 'UTF-8'), 'DEVICE_ID' => $devices[$i]['ID']);
                }
            }
            if ($devices[$i]['TYPE']=='html') {
                $content=getGlobal($devices[$i]['LINKED_OBJECT'].'.data');
                $content=preg_replace('/%([\w\d\.]+?)\.([\w\d\.]+?)\|(\d+)%/uis', '%\1.\2%', $content);
                $content=preg_replace('/%([\w\d\.]+?)\.([\w\d\.]+?)\|(\d+)%/uis', '%\1.\2%', $content);
                $content=preg_replace('/%([\w\d\.]+?)\.([\w\d\.]+?)\|".+?"%/uis', '%\1.\2%', $content);
                if (preg_match_all('/%([\w\d\.]+?)%/is', $content, $m)) {
                    $totalm=count($m[1]);
                    for($im=0;$im<$totalm;$im++) {
                        $properties[]=array('PROPERTY'=>mb_strtolower($m[1][$im], 'UTF-8'), 'DEVICE_ID' => $devices[$i]['ID']);
                    }
                }
            }
        }
        return $properties;
    }

    function updateGroupObjects() {
        $groups=SQLSelect("SELECT * FROM devices_groups WHERE 1");
        //SYS_NAME
        $total = count($groups);
        $added_objects=array();
        for($i=0;$i<$total;$i++) {
            $object_id = addClassObject('SGroups','group'.$groups[$i]['SYS_NAME'],'group'.$groups[$i]['SYS_NAME']);
            $object_rec=SQLSelectOne("SELECT * FROM objects WHERE ID=".$object_id);
            if ($object_rec['ID']) {
                $object_rec['DESCRIPTION']=$groups[$i]['TITLE'];
                SQLUpdate('objects',$object_rec);
            }
            sg($object_rec['TITLE'].'.groupName',$groups[$i]['SYS_NAME']);
            $added_objects[]=$object_id;
        }
        $group_objects=getObjectsByClass('SGroups');
        foreach($group_objects as $rec) {
            if (!in_array($rec['ID'],$added_objects)) {
                deleteObject($rec['ID']);
            }
        }
    }

    function renderStructure()
    {

        if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) { 
            unsubscribeFromEvent('devices', 'COMMAND'); 
            unsubscribeFromEvent('devices', 'MINUTELY');
            return;
        }

        foreach ($this->device_types as $k => $v) {
            //$v['CLASS']
            //$v['PARENT_CLASS']
            //$v['PROPERTIES']
            //$v['METHODS']

            //CLASS
            if ($v['PARENT_CLASS']) {
                $class_id = addClass($v['CLASS'], $v['PARENT_CLASS']);
            } else {
                $class_id = addClass($v['CLASS']);
            }
            if ($class_id) {
                $class = SQLSelectOne("SELECT * FROM classes WHERE ID=" . $class_id);
                if ($v['DESCRIPTION']) {
                    $class['DESCRIPTION'] = $v['DESCRIPTION'];
                    SQLUpdate('classes', $class);
                }
            }

            //PROPERTIES
            if (is_array($v['PROPERTIES'])) {
                foreach ($v['PROPERTIES'] as $pk => $pv) {
                    $prop_id = addClassProperty($v['CLASS'], $pk, (int)$pv['KEEP_HISTORY']);
                    if ($prop_id) {
                        $property = SQLSelectOne("SELECT * FROM properties WHERE ID=" . $prop_id);
                        if (is_array($pv)) {
                            foreach ($pv as $ppk => $ppv) {
                                if (substr($ppk, 0, 1) == '_') continue;
                                $property[$ppk] = $ppv;
                            }
                            SQLUpdate('properties', $property);
                        }
                    }
                }
            }

            //METHODS
            if (is_array($v['METHODS'])) {
                foreach ($v['METHODS'] as $mk => $mv) {
                    $method_id = addClassMethod($v['CLASS'], $mk, "require(DIR_MODULES.'devices/" . $v['CLASS'] . "_" . $mk . ".php');", 'SDevices');
                    if (!file_exists(DIR_MODULES . "devices/" . $v['CLASS'] . "_" . $mk . ".php")) {
                        $code = '<?php' . "\n\n";
                        @SaveFile(DIR_MODULES . "devices/" . $v['CLASS'] . "_" . $mk . ".php", $code);
                    }
                    if ($method_id) {
                        $method = SQLSelectOne("SELECT * FROM methods WHERE ID=" . $method_id);
                        if (is_array($mv)) {
                            foreach ($mv as $mmk => $mmv) {
                                if (substr($mmk, 0, 1) == '_') continue;
                                $method[$mmk] = $mmv;
                            }
                            SQLUpdate('methods', $method);
                        }
                    }
                }
            }

            if (is_array($v['INJECTS'])) {
                foreach ($v['INJECTS'] as $class_name => $methods) {
                    addClass($class_name);
                    foreach ($methods as $mk => $mv) {
                        list($object, $method_name) = explode('.', $mk);
                        addClassObject($class_name, $object);
                        if (!file_exists(DIR_MODULES . "devices/" . $mv . ".php")) {
                            $code = '<?php' . "\n\n";
                            @SaveFile(DIR_MODULES . "devices/" . $mv . ".php", $code);
                        }
                        injectObjectMethodCode($mk, 'SDevices', "require(DIR_MODULES.'devices/" . $mv . ".php');");
                    }
                }
            }
        }
        subscribeToEvent('devices', 'COMMAND');
        subscribeToEvent('devices', 'MINUTELY');

        //update cameras
        $objects = getObjectsByClass('SCameras');
        $total = count($objects);
        for ($i = 0; $i < $total; $i++) {
            $ot = $objects[$i]['TITLE'];
            callMethod($ot . '.updatePreview');
        }

        //update objects sdevice
        $devices = SQLSelect("SELECT ID, LINKED_OBJECT FROM devices");
        foreach ($devices as $device) {
            SQLExec("UPDATE objects SET `SYSTEM`='sdevice" . $device['ID'] . "' WHERE TITLE='" . DBSafe($device['LINKED_OBJECT']) . "' AND `SYSTEM`=''");
        }

        $this->updateGroupObjects();

    }

    function processSubscription($event, &$details)
    {
        if ($event == 'COMMAND' && $details['member_id']) {
            //DebMes("Processing event $event",'simple_devices');
            include_once(DIR_MODULES . 'devices/processCommand.inc.php');
            //DebMes("Processing event $event DONE",'simple_devices');
        }
        if ($event == 'MINUTELY') {
            $points = SQLSelect("SELECT devices_scheduler_points.*, devices.LINKED_OBJECT FROM devices_scheduler_points LEFT JOIN devices ON devices_scheduler_points.DEVICE_ID=devices.ID WHERE ACTIVE=1");
            $total = count($points);
            for ($i = 0; $i < $total; $i++) {
                $rec = $points[$i];
                if ($rec['SET_DAYS'] === '') {
                    continue;
                }
                $run_days = explode(',', $rec['SET_DAYS']);
                if (!in_array(date('w'), $run_days)) {
                    continue;
                }
                $tm = strtotime(date('Y-m-d') . ' ' . $rec['SET_TIME']);
                $diff = time() - $tm;
                if ($diff < 0 || $diff >= 10 * 60) {
                    continue;
                }
                //DebMes("Checking point: ".json_encode($rec,JSON_PRETTY_PRINT),'devices_schedule');
                $tmlr = strtotime($rec['LATEST_RUN']);
                $diff_run = time() - $tmlr;
                if ($diff_run <= 20*60) {
                    //DebMes("Skipping point (diff_run: $diff_run): ".json_encode($rec,JSON_PRETTY_PRINT),'devices_schedule');
                    continue;
                }

                $linked_object = $rec['LINKED_OBJECT'];
                unset($rec['LINKED_OBJECT']);
                $rec['LATEST_RUN'] = date('Y-m-d H:i:s');
                SQLUpdate('devices_scheduler_points', $rec);
                DebMes("Running point: ".$linked_object.'.'.$rec['LINKED_METHOD'],'devices_schedule');
                callMethodSafe($linked_object . '.' . $rec['LINKED_METHOD']);
            }
        }
    }

    function computePermutations($array)
    {
        $result = [];
        $recurse = function ($array, $start_i = 0) use (&$result, &$recurse) {
            if ($start_i === count($array) - 1) {
                array_push($result, $array);
            }
            for ($i = $start_i; $i < count($array); $i++) {
                //Swap array value at $i and $start_i
                $t = $array[$i];
                $array[$i] = $array[$start_i];
                $array[$start_i] = $t;
                //Recurse
                $recurse($array, $start_i + 1);
                //Restore old order
                $t = $array[$i];
                $array[$i] = $array[$start_i];
                $array[$start_i] = $t;
            }
        };
        $recurse($array);
        return $result;
    }

    /**
     *
     * Generate all the possible combinations among a set of nested arrays. *
     * @param array $data The entrypoint array container.
     * @param array $all The final container (used internally).
     * @param array $group The sub container (used internally).
     * @param mixed $val The value to append (used internally).
     * @param int $i The key index (used internally).
     */

    function generate_combinations(array $data, array &$all = array(), array $group = array(), $value = null, $i = 0, $key = null)
    {
        $keys = array_keys($data);
        if (isset($value) === true) {
            $group[$key] = $value;
        }
        if ($i >= count($data)) {
            array_push($all, $group);
        } else {
            $currentKey = $keys[$i];
            $currentElement = $data[$currentKey];
            if (count($data[$currentKey]) <= 0) {
                $this->generate_combinations($data, $all, $group, null, $i + 1, $currentKey);
            } elseif (is_array($currentElement)) {
                foreach ($currentElement as $val) {
                    $this->generate_combinations($data, $all, $group, $val, $i + 1, $currentKey);
                }
            }
        }
        return $all;
    }

    function homebridgeSync($device_id = 0, $force_refresh = 0)
    {
        if ($this->isHomeBridgeAvailable()) {
            include_once(DIR_MODULES . 'devices/homebridgeSync.inc.php');
        }
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
        if ($this->data_source == 'devices' || $this->data_source == '') {

            if ($this->mode == 'homebridgesync') {
                $this->homebridgeSync();
                $this->redirect("?");
            }

            if ($this->view_mode == '' || $this->view_mode == 'search_devices') {
                $this->search_devices($out);
                if ($this->isHomeBridgeAvailable()) {
                    $out['ENABLE_HOMEBRIDGE'] = 1;
                }
            }

            if ($this->view_mode == 'manage_groups') {
                $this->manage_groups($out);
            }

            if ($this->view_mode == 'edit_devices') {
                $this->edit_devices($out, $this->id);
            }

            if ($this->view_mode == 'quick_edit') {
                $this->quick_edit($out);
            }

            if ($this->view_mode == 'render_structure') {
                $this->renderStructure();
                $this->redirect("?");
            }

            if ($this->view_mode == 'delete_devices') {
                $this->delete_devices($this->id);
                $this->redirect("?type=" . gr('type') . '&location_id=' . gr('location_id') . '&group_name=' . gr('group_name'));
            }
        }
    }

    function isHomeBridgeAvailable()
    {
        //return true; // temporary
        $tmp = SQLSelectOne("SELECT ID FROM objects WHERE TITLE='HomeBridge'");
        if ($tmp['ID']) {
            return true;
        } else {
            return false;
        }
    }


    function manage_groups(&$out)
    {
        require(DIR_MODULES . $this->name . '/devices_manage_groups.inc.php');
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
            $op=gr('op');
            $view = gr('view');
            $res = array();
            if ($op == 'clicked') {
                $object=gr('object');
                if ($object !='') {
                    $device_rec=SQLSelectOne("SELECT ID, TITLE FROM devices WHERE LINKED_OBJECT='".DBSafe($object)."'");
                    if ($device_rec['ID']) {
                        SQLExec("UPDATE devices SET CLICKED=NOW() WHERE ID='".$device_rec['ID']."'");
                        logAction('device_clicked',$device_rec['TITLE']);
                    }

                }
            }
            if ($op == 'get_device') {
                $id=gr('id');
                $res = $this->processDevice($id,$view);
            }
            if ($op == 'loadAllDevicesHTML') {
                /*
                if (gr('favorite')) {
                    $devices=SQLSelect("SELECT ID, LINKED_OBJECT FROM devices WHERE FAVORITE=1");
                } else {
                    $devices=SQLSelect("SELECT ID, LINKED_OBJECT FROM devices WHERE FAVORITE!=1");
                }
                */
                $devices = SQLSelect("SELECT ID, LINKED_OBJECT FROM devices WHERE SYSTEM_DEVICE=0");
                $total = count($devices);
                for ($i = 0; $i < $total; $i++) {
                    if ($devices[$i]['LINKED_OBJECT']) {
                        $processed = $this->processDevice($devices[$i]['ID'],$view);
                        $devices[$i]['HTML'] = $processed['HTML'];
                    }
                }
                $res['DEVICES'] = $devices;
            }
            echo json_encode($res);
            exit;
        }

        if ($this->owner->action == 'apps') {
            //$this->redirect(ROOTHTML."module/devices.html");
        }

        $location_id = gr('location_id');
        $type = gr('type');
        $collection = gr('collection');

        $qry="1";

        $linked_object = gr('linked_object');
        if ($linked_object) {
            $device_rec=SQLSelectOne("SELECT ID FROM devices WHERE LINKED_OBJECT='".DbSafe($linked_object)."'");
            if ($device_rec['ID']) {
                $this->id=$device_rec['ID'];
            }
        }

        $out['UNIQ']=uniqid('dev'.$this->id);
        
        if ($this->id) {
            $qry.=" AND devices.ID=".(int)$this->id;
            $out['SINGLE_DEVICE']=1;
            $out['VIEW']=$this->view;
        }

        if ($location_id || $type || $collection) {
            $qry = "1 AND SYSTEM_DEVICE=0";
            $orderby = 'locations.PRIORITY DESC, LOCATION_ID, TYPE, TITLE';
            if (preg_match('/loc(\d+)/', $type, $m)) {
                $location_id = $m[1];
                $type = '';
            }
            if (preg_match('/col\_(\w+)/', $type, $m)) {
                $collection = $m[1];
                $type = '';
            }
            if ($location_id) {
                if ($location_id != 'all') {
                    $qry .= " AND devices.LOCATION_ID=" . (int)$location_id;
                    $location = SQLSelectOne("SELECT * FROM locations WHERE ID=" . (int)$location_id);
                    foreach ($location as $k => $v) {
                        $out['LOCATION_' . $k] = $v;
                    }
                    $out['TITLE'] = $location['TITLE'];
                } else {
                    $out['LOCATION_ID'] = 'All';
                    $qry .= " AND 1";
                }

            }
            if ($type) {
                if ($type != 'all') {
                    $qry .= " AND devices.TYPE LIKE '" . DBSafe($type) . "'";
                    $out['TITLE'] = $this->device_types[$type]['TITLE'];
                } else {
                    $orderby = 'TYPE, locations.PRIORITY DESC, LOCATION_ID, TITLE';
                }
                $out['TYPE'] = $type;
            }
            if ($collection!='') {
                $ids=$this->getCollectionIds($collection);
                $qry.=" AND devices.ID IN (".implode(',',$ids).")";
                $out['TITLE']=constant('LANG_DEVICES_COLLECTION_'.strtoupper($collection));
                $out['COLLECTION'] = $collection;
            }
            $location_title = '';
            $type_title = '';
            $devices = SQLSelect("SELECT devices.*, locations.TITLE as LOCATION_TITLE FROM devices LEFT JOIN locations ON devices.LOCATION_ID=locations.ID WHERE $qry ORDER BY $orderby");
            $total = count($devices);
            for ($i = 0; $i < $total; $i++) {
                if ($type == 'all') {
                    $devices[$i]['LOCATION_TITLE'] = $this->device_types[$devices[$i]['TYPE']]['TITLE'];
                    if ($devices[$i]['LOCATION_TITLE'] != $location_title) {
                        $devices[$i]['NEW_LOCATION'] = 1;
                        $location_title = $devices[$i]['LOCATION_TITLE'];
                    }
                } else {
                    if ($devices[$i]['LOCATION_TITLE'] != $location_title && !$out['LOCATION_TITLE']) {
                        $devices[$i]['NEW_LOCATION'] = 1;
                        $location_title = $devices[$i]['LOCATION_TITLE'];
                    }
                    if ($this->device_types[$devices[$i]['TYPE']]['TITLE'] != $type_title) {
                        $type_title = $this->device_types[$devices[$i]['TYPE']]['TITLE'];
                        $devices[$i]['NEW_TYPE'] = 1;
                    }
                }
            }
        } else {
            $orderby = 'locations.PRIORITY DESC, LOCATION_ID, TYPE, TITLE';
            //$qry=" devices.FAVORITE=1";
            $qry.= " AND SYSTEM_DEVICE=0";
            $out['ALL_DEVICES']=1;
            $devices = SQLSelect("SELECT devices.*, locations.TITLE as LOCATION_TITLE FROM devices LEFT JOIN locations ON devices.LOCATION_ID=locations.ID WHERE $qry ORDER BY $orderby");
            $recent_devices=SQLSelect("SELECT devices.* FROM devices WHERE !IsNull(CLICKED) ORDER BY CLICKED DESC LIMIT 10");
        }

        if ($devices[0]['ID']) {
            if ($location_id || $type || 1) {
                $total = count($devices);
                for ($i = 0; $i < $total; $i++) {
                    if ($devices[$i]['LINKED_OBJECT']) {
                        $processed = $this->processDevice($devices[$i]['ID'],$this->view);
                        $devices[$i]['HTML'] = $processed['HTML'];
                    }
                }

            }
            $out['DEVICES'] = $devices;
            if ($this->id) {
                return;
            }
        }

        $locations = SQLSelect("SELECT ID, TITLE FROM locations ORDER BY PRIORITY DESC, TITLE+0");
        $total_devices = count($devices);
        if ($total_devices) {
            $favorite_devices = array();
            $warning_devices = array();
            $problem_devices = array();
            for ($idv = 0; $idv < $total_devices; $idv++) {
                if ($devices[$idv]['FAVORITE']) {
                    $favorite_devices[] = $devices[$idv];
                } elseif ($devices[$idv]['LINKED_OBJECT']) {

                    if (gg($devices[$idv]['LINKED_OBJECT'] . '.normalValue') == '0' &&
                        gg($devices[$idv]['LINKED_OBJECT'] . '.notify') == '1'
                    ) {
                        $warning_devices[] = $devices[$idv];
                        $warning_devices[0]['NEW_SECTION'] = 1;
                        $warning_devices[0]['SECTION_TITLE'] = LANG_WARNING;
                    } elseif (
                        ($devices[$idv]['TYPE'] == 'motion' ||
                            $devices[$idv]['TYPE'] == 'openclose' ||
                            $devices[$idv]['TYPE'] == 'leak' ||
                            $devices[$idv]['TYPE'] == 'smoke' ||
                            $devices[$idv]['TYPE'] == 'counter' ||
                            preg_match('/^sensor/',$devices[$idv]['TYPE']) ||
                            $this->device_types[$devices[$idv]['TYPE']]['PARENT_CLASS'] == 'SSensors' ||
                            (int)gg($devices[$idv]['LINKED_OBJECT'] . '.aliveTimeout')>0
                        ) && gg($devices[$idv]['LINKED_OBJECT'] . '.alive') === '0'
                    ) {
                        $problem_devices[] = $devices[$idv];
                        $problem_devices[0]['NEW_SECTION'] = 1;
                        $problem_devices[0]['SECTION_TITLE'] = LANG_OFFLINE;
                    }
                }
            }


            if (count($favorite_devices)>0) {
                usort($favorite_devices,function ($a,$b) {
                    if ($a['FAVORITE'] == $b['FAVORITE']) {
                        return 0;
                    }
                    return ($a['FAVORITE'] > $b['FAVORITE']) ? -1 : 1;
                });
            }

            foreach ($warning_devices as $device) {
                $favorite_devices[] = $device;
            }

            if ($recent_devices[0]['ID']) {
                $recent_devices[0]['NEW_SECTION']=1;
                $recent_devices[0]['SECTION_TITLE']=LANG_RECENTLY_USED;
                foreach($recent_devices as &$device) {
                    if ($device['LINKED_OBJECT']) {
                        $processed = $this->processDevice($device['ID']);
                        $device['HTML'] = $processed['HTML'];
                    }
                    $favorite_devices[] = $device;
                }
            }

            foreach ($problem_devices as $device) {
                $favorite_devices[] = $device;
            }

            $devices_count = count($favorite_devices);

            if ($devices_count > 0) {

                $loc_rec = array();
                $loc_rec['ID'] = 0;
                $loc_rec['TITLE'] = LANG_FAVORITES;
                $loc_rec['DEVICES'] = $favorite_devices;
                $loc_rec['DEVICES_TOTAL'] = $devices_count;
                array_unshift($locations, $loc_rec);
            }
        }

        $total = count($locations);
        for ($i = 0; $i < $total; $i++) {
            if ($locations[$i]['ID']) {
                $devices_count = 0;
                if ($total_devices) {
                    for ($idv = 0; $idv < $total_devices; $idv++) {
                        if ($devices[$idv]['LOCATION_ID'] == $locations[$i]['ID']) {
                            $devices_count++;
                            $locations[$i]['DEVICES'][] = $devices[$idv];
                        }
                    }
                }
                $locations[$i]['DEVICES_TOTAL'] = $devices_count;
            }
            //$devices_count=(int)current(SQLSelectOne("SELECT COUNT(*) FROM devices WHERE LOCATION_ID=".(int)$locations[$i]['ID']));
            $locations[$i]['INDEX'] = $i;
        }
        $out['GROUPS'] = $locations;

        $types = array();


        if (is_array($this->device_types)) {
            foreach ($this->device_types as $k => $v) {
                if ($v['TITLE']) {
                    $type_rec = array('NAME' => $k, 'TITLE' => $v['TITLE']);
                    $tmp = SQLSelectOne("SELECT COUNT(*) AS TOTAL FROM devices WHERE SYSTEM_DEVICE=0 AND TYPE='" . $k . "'");
                    $type_rec['TOTAL'] = (int)$tmp['TOTAL'];
                    if ($type_rec['TOTAL'] > 0) {
                        $types[] = $type_rec;
                    }
                }
            }
            usort($types, function ($a, $b) {
                return strcmp($a["TITLE"], $b["TITLE"]);
            });
        }



        $col_name = 'is_heating';
        $col_ids=$this->getCollectionIds($col_name);
        $col_total = count($col_ids)-1;
        $col=array('NAME'=>'col_'.$col_name,'TITLE'=>LANG_DEVICES_COLLECTION_IS_HEATING,'TOTAL'=>$col_total);
        if ($col_total>0) array_unshift($types,$col);

        $col_name = 'is_on';
        $col_ids=$this->getCollectionIds($col_name);
        $col_total = count($col_ids)-1;
        $col=array('NAME'=>'col_'.$col_name,'TITLE'=>LANG_DEVICES_COLLECTION_IS_ON,'TOTAL'=>$col_total);
        if ($col_total>0) array_unshift($types,$col);

        $col_name = 'is_open';
        $col_ids=$this->getCollectionIds($col_name);
        $col_total = count($col_ids)-1;
        $col=array('NAME'=>'col_'.$col_name,'TITLE'=>LANG_DEVICES_COLLECTION_IS_OPEN,'TOTAL'=>$col_total);
        if ($col_total>0) array_unshift($types,$col);

        $list_locations = $locations;
        if (is_array($list_locations)) {
            usort($list_locations, function ($a, $b) {
                return strcmp($a["TITLE"], $b["TITLE"]);
            });
            $types[] = array('NAME' => '', 'TITLE' => LANG_LOCATION);
            foreach ($list_locations as $location) {
                if ($location['TITLE'] == LANG_FAVORITES) continue;
                $types[] = array('NAME' => 'loc' . $location['ID'], 'TITLE' => $location['TITLE'], 'TOTAL' => $location['DEVICES_TOTAL']);
            }
        }

        $out['TYPES'] = $types;


    }

    function getCollectionIds($collection) {
        $ids = array(0);
        if ($collection=='is_on') {
            $devices=SQLSelect("SELECT ID, LINKED_OBJECT FROM devices WHERE devices.TYPE IN ('relay','dimmer','rgb')");
            foreach($devices as $device) {
                if (gg($device['LINKED_OBJECT'].'.status')) {
                    $ids[]=$device['ID'];
                }
            }
        } elseif ($collection == 'is_open') {
            $devices=SQLSelect("SELECT ID, LINKED_OBJECT FROM devices WHERE devices.TYPE IN ('openable','openclose')");
            foreach($devices as $device) {
                if (!gg($device['LINKED_OBJECT'].'.status')) {
                    $ids[]=$device['ID'];
                }
            }
        } elseif ($collection == 'is_heating') {
            $devices=SQLSelect("SELECT ID, LINKED_OBJECT FROM devices WHERE devices.TYPE IN ('thermostat')");
            foreach($devices as $device) {
                if (gg($device['LINKED_OBJECT'].'.relay_status')) {
                    $ids[]=$device['ID'];
                }
            }
        }
        return $ids;
    }

    /**
     * devices search
     *
     * @access public
     */
    function search_devices(&$out)
    {
        require(DIR_MODULES . $this->name . '/devices_search.inc.php');
    }

    /**
     * devices edit/add
     *
     * @access public
     */
    function edit_devices(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/devices_edit.inc.php');
    }

    function quick_edit(&$out)
    {
        require(DIR_MODULES . $this->name . '/devices_quick_edit.inc.php');
    }

    /**
     * devices delete record
     *
     * @access public
     */
    function delete_devices($id)
    {
        $rec = SQLSelectOne("SELECT * FROM devices WHERE ID='$id'");

        $payload=array();
        $payload['name']=$rec['LINKED_OBJECT'];
        sg('HomeBridge.to_remove',json_encode($payload));


        // some action for related tables

        $elements = SQLSelect("SELECT * FROM elements WHERE `SYSTEM`='sdevice" . $rec['ID'] . "'");
        $total = count($elements);
        for ($i = 0; $i < $total; $i++) {
            SQLExec("DELETE FROM elm_states WHERE ELEMENT_ID=" . $elements[$i]['ID']);
            SQLExec("DELETE FROM elements WHERE ID=" . $elements[$i]['ID']);
        }

        $objects = SQLSelect("SELECT ID FROM objects WHERE `SYSTEM`='sdevice" . $rec['ID'] . "'");

        $total = count($objects);
        for ($i = 0; $i < $total; $i++) {
            deleteObject($objects[$i]['ID']);
        }

        $tables = array('commands');
        $total = count($tables);
        for ($i = 0; $i < $total; $i++) {
            SQLExec("DELETE FROM " . $tables[$i] . " WHERE `SYSTEM`='sdevice" . $rec['ID'] . "'");
        }
        SQLExec("DELETE FROM devices_linked WHERE DEVICE1_ID='" . $rec['ID'] . "' OR DEVICE2_ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM devices WHERE ID='" . $rec['ID'] . "'");
    }

    function addDevice($device_type, $options = 0)
    {
        $this->setDictionary();
        $type_details = $this->getTypeDetails($device_type);

        if (!is_array($options)) {
            $options = array();
        }
        if (!is_array($this->device_types[$device_type])) {
            return 0;
        }

        if ($options['TABLE'] && $options['TABLE_ID']) {
            $table_rec = SQLSelectOne("SELECT * FROM " . $options['TABLE'] . " WHERE ID=" . $options['TABLE_ID']);
            if (!$table_rec['ID']) {
                return 0;
            }
        }

        if ($options['LINKED_OBJECT'] != '') {
            $old_device = SQLSelectOne("SELECT ID FROM devices WHERE LINKED_OBJECT LIKE '" . DBSafe($options['LINKED_OBJECT']) . "'");
            if ($old_device['ID']) return $old_device['ID'];
            $rec['LINKED_OBJECT'] = $options['LINKED_OBJECT'];
        }

        $rec = array();
        $rec['TYPE'] = $device_type;
        if ($options['TITLE']) {
            $rec['TITLE'] = $options['TITLE'];
        } else {
            $rec['TITLE'] = 'New device ' . date('H:i');
        }
        if ($options['LOCATION_ID']) {
            $rec['LOCATION_ID'] = $options['LOCATION_ID'];
        }
        $rec['ID'] = SQLInsert('devices', $rec);

        if ($rec['LOCATION_ID']) {
            $location_title = getRoomObjectByLocation($rec['LOCATION_ID'], 1);
        }

        if (!$rec['LINKED_OBJECT']) {
            $prefix=ucfirst($rec['TYPE']);
            $new_object_title =  $prefix . $this->getNewObjectIndex($type_details['CLASS']);
            $object_id = addClassObject($type_details['CLASS'], $new_object_title, 'sdevice' . $rec['ID']);
            $rec['LINKED_OBJECT'] = $new_object_title;
            if (preg_match('/New device .+/', $rec['TITLE'])) {
                $rec['TITLE'] = $rec['LINKED_OBJECT'];
            }
            SQLUpdate('devices', $rec);
        }

        if ($table_rec['ID']) {
            $this->addDeviceToSourceTable($options['TABLE'], $table_rec['ID'], $rec['ID']);
        }

        if ($options['ADD_MENU']) {
            $this->addDeviceToMenu($rec['ID']);
        }

        if ($options['ADD_SCENE']) {
            $this->addDeviceToScene($rec['ID']);
        }

        return 1;

    }


    function addDeviceToSourceTable($table_name, $table_id, $device_id)
    {

        $rec = SQLSelectOne("SELECT * FROM devices WHERE ID=" . (int)$device_id);
        $this->setDictionary();
        $type_details = $this->getTypeDetails($rec['TYPE']);

        if (!$rec['LINKED_OBJECT']) {
            return 0;
        }

        $table_rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID=" . DBSafe($table_id));
        if (!$table_rec['ID']) {
            return 0;
        }

        $linked_object = $rec['LINKED_OBJECT'];
        $linked_property = '';
        $linked_method = '';

        if ($type_details['PARENT_CLASS'] == 'SSensors') {
            $linked_property = 'value';
        } elseif ($type_details['PARENT_CLASS'] == 'SControllers') {
            $linked_property = 'status';
        }
        if ($rec['TYPE'] == 'dimmer') {
            $linked_property = 'level';
        }
        if ($rec['TYPE'] == 'rgb') {
            $linked_property = 'color';
        }
        if ($rec['TYPE'] == 'motion') {
            $linked_property = 'status';
            $linked_method = 'motionDetected';
        }
        if ($rec['TYPE'] == 'button') {
            $linked_property = 'status';
            $linked_method = 'pressed';
        }
        if ($rec['TYPE'] == 'switch' || $rec['TYPE'] == 'openclose') {
            $linked_property = 'status';
        }
        if ($table_rec['ID']) {
            $table_rec['LINKED_OBJECT'] = $linked_object;
            $table_rec['LINKED_PROPERTY'] = $linked_property;
            $table_rec['LINKED_METHOD'] = $linked_method;
            SQLUpdate($table_name, $table_rec);
        }
    }

    function addDeviceToMenu($device_id, $add_menu_id = 0)
    {
        $rec = SQLSelectOne("SELECT * FROM devices WHERE ID=" . (int)$device_id);
        if (!$rec['ID']) {
            return 0;
        }
        $menu_rec = SQLSelectOne("SELECT * FROM commands WHERE `SYSTEM`='" . 'sdevice' . $rec['ID'] . "'");
        if (!$menu_rec['ID']) {
            $menu_rec = array();
        }
        if (!$menu_rec['TITLE']) {
            $menu_rec['TITLE'] = $rec['TITLE'];
        }
        $menu_rec['PARENT_ID'] = (int)$add_menu_id;
        $menu_rec['SYSTEM'] = 'sdevice' . $rec['ID'];
        $menu_rec['LINKED_OBJECT'] = $rec['LINKED_OBJECT'];
        if ($rec['TYPE'] == 'relay' || $rec['TYPE'] == 'switch') {
            $menu_rec['TYPE'] = 'switch';
            $menu_rec['LINKED_PROPERTY'] = 'status';
            $menu_rec['CUR_VALUE'] = getGlobal($menu_rec['LINKED_OBJECT'] . '.' . $menu_rec['LINKED_PROPERTY']);
        } elseif ($rec['TYPE'] == 'button') {
            $menu_rec['TYPE'] = 'button';
            $menu_rec['LINKED_PROPERTY'] = '';
            $menu_rec['ONCHANGE_METHOD'] = 'pressed';
        } elseif ($rec['TYPE'] == 'dimmer') {
            $menu_rec['TYPE'] = 'sliderbox';
            $menu_rec['MIN_VALUE'] = '0';
            $menu_rec['MAX_VALUE'] = '100';
            $menu_rec['STEP_VALUE'] = '1';
            $menu_rec['LINKED_PROPERTY'] = 'level';
            $menu_rec['CUR_VALUE'] = getGlobal($menu_rec['LINKED_OBJECT'] . '.' . $menu_rec['LINKED_PROPERTY']);
        } else {
            $menu_rec['TYPE'] = 'object';
        }
        if ($menu_rec['ID']) {
            SQLUpdate('commands', $menu_rec);
        } else {
            $menu_rec['ID'] = SQLInsert('commands', $menu_rec);
        }
        return $menu_rec['ID'];
    }

    function addDeviceToScene($device_id, $add_scene_id = 0)
    {

        $rec = SQLSelectOne("SELECT * FROM devices WHERE ID=" . (int)$device_id);
        if (!$rec['ID']) {
            return 0;
        }

        if (!$add_scene_id) {
            $scene_rec = SQLSelectOne("SELECT ID FROM scenes ORDER BY ID LIMIT 1");
            if ($scene_rec['ID']) {
                $add_scene_id = $scene_rec['ID'];
            } else {
                return 0;
            }
        }

        $element_rec = SQLSelectOne("SELECT * FROM elements WHERE SCENE_ID=" . (int)$add_scene_id . " AND `SYSTEM`='" . 'sdevice' . $rec['ID'] . "'");
        if (!$element_rec['ID']) {
            $element_rec = array();
            $wizard_data = array();
        } else {
            $wizard_data = json_decode($element_rec['WIZARD_DATA'], true);
        }
        $element_rec['SCENE_ID'] = (int)$add_scene_id;
        $element_rec['SYSTEM'] = 'sdevice' . $rec['ID'];
        if (!$element_rec['TOP'] && !$element_rec['LEFT']) {
            $element_rec['TOP'] = 10 + rand(0, 300);
            $element_rec['LEFT'] = 10 + rand(0, 300);
        }
        if (!$element_rec['CSS_STYLE']) {
            $element_rec['CSS_STYLE'] = 'default';
        }
        $element_rec['WIZARD_DATA'] = json_encode($wizard_data) . '';
        $element_rec['BACKGROUND'] = 0;
        $element_rec['LINKED_OBJECT'] = $rec['LINKED_OBJECT'];
        $element_rec['TITLE'] = $rec['TITLE'];
        $element_rec['EASY_CONFIG'] = 1;
        $linked_property_unit = '';

        $element_rec['TYPE'] = 'device';
        $element_rec['DEVICE_ID'] = $rec['ID'];
        /*
        if ($rec['TYPE']=='relay' || $rec['TYPE']=='dimmer' || $rec['TYPE']=='switch') {
            $element_rec['TYPE'] = 'switch';
            $element_rec['LINKED_PROPERTY'] = 'status';
        } elseif ($rec['TYPE']=='button') {
            $element_rec['TYPE'] = 'button';
        } elseif ($rec['TYPE']=='motion') {
            $element_rec['TYPE'] = 'warning';
            $element_rec['LINKED_PROPERTY'] = 'status';
            $element_rec['CSS_STYLE']='motion';
        } elseif ($rec['TYPE']=='sensor_temp') {
            $element_rec['CSS_STYLE']='temp';
            $linked_property_unit='&deg;C';
        } elseif ($rec['TYPE']=='sensor_humidity') {
            $element_rec['CSS_STYLE']='humidity';
            $linked_property_unit='%';
        } else {
            $element_rec['TYPE']='object';
        }
        if ($rec['TYPE']=='sensor_temp' || $rec['TYPE']=='sensor_humidity') {
            $element_rec['TYPE'] = 'informer';
            $element_rec['LINKED_PROPERTY'] = 'value';
            $wizard_data['STATE_HIGH']=1;
            $wizard_data['STATE_HIGH_VALUE']='%'.$element_rec['LINKED_OBJECT'].'.maxValue%';
            $wizard_data['STATE_LOW']=1;
            $wizard_data['STATE_LOW_VALUE']='%'.$element_rec['LINKED_OBJECT'].'.maxValue%';
            $wizard_data['UNIT']=$linked_property_unit;
        }
        */

        $element_rec['WIZARD_DATA'] = json_encode($wizard_data);

        if ($element_rec['ID']) {
            SQLUpdate('elements', $element_rec);
        } else {
            $element_rec['ID'] = SQLInsert('elements', $element_rec);

            $linked_object = $rec['LINKED_OBJECT'];

            if ($element_rec['TYPE'] == 'switch') {

                $state_rec = array();
                $state_rec['TITLE'] = 'off';
                $state_rec['HTML'] = $element_rec['TITLE'];
                $state_rec['ELEMENT_ID'] = $element_rec['ID'];
                $state_rec['IS_DYNAMIC'] = 1;
                $state_rec['LINKED_OBJECT'] = $rec['LINKED_OBJECT'] . '';
                $state_rec['LINKED_PROPERTY'] = 'status';
                $state_rec['CONDITION'] = 4;
                $state_rec['CONDITION_VALUE'] = 1;
                $state_rec['ACTION_OBJECT'] = $rec['LINKED_OBJECT'] . '';
                $state_rec['ACTION_METHOD'] = 'turnOn';
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);

                $state_rec = array();
                $state_rec['TITLE'] = 'on';
                $state_rec['HTML'] = $element_rec['TITLE'];
                $state_rec['ELEMENT_ID'] = $element_rec['ID'];
                $state_rec['IS_DYNAMIC'] = 1;
                $state_rec['LINKED_OBJECT'] = $rec['LINKED_OBJECT'] . '';
                $state_rec['LINKED_PROPERTY'] = 'status';
                $state_rec['CONDITION'] = 1;
                $state_rec['CONDITION_VALUE'] = 1;
                $state_rec['ACTION_OBJECT'] = $rec['LINKED_OBJECT'] . '';
                $state_rec['ACTION_METHOD'] = 'turnOff';
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);

            } elseif ($element_rec['TYPE'] == 'warning') {

                $state_rec = array();
                $state_rec['TITLE'] = 'default';
                $state_rec['ELEMENT_ID'] = $element_rec['ID'];
                $state_rec['HTML'] = $element_rec['TITLE'] . '<br/>%' . $rec['LINKED_OBJECT'] . '.updatedText%';
                $state_rec['LINKED_OBJECT'] = $rec['LINKED_OBJECT'] . '';
                $state_rec['LINKED_PROPERTY'] = 'status';
                $state_rec['IS_DYNAMIC'] = 1;
                $state_rec['CONDITION'] = 1;
                $state_rec['CONDITION_VALUE'] = 1;
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);

            } elseif ($element_rec['TYPE'] == 'informer') {

                $linked_property = 'value';
                $state_high = 1;
                $state_high_value = '%' . $linked_object . '.maxValue%';

                if ($state_high) {
                    $state_rec = array();
                    $state_rec['TITLE'] = 'high';
                    $state_rec['ELEMENT_ID'] = $element_rec['ID'];
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

                $state_low = 1;
                $state_low_value = '%' . $linked_object . '.minValue%';

                if ($state_low) {
                    $state_rec = array();
                    $state_rec['TITLE'] = 'low';
                    $state_rec['ELEMENT_ID'] = $element_rec['ID'];
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
                $state_rec['ELEMENT_ID'] = $element_rec['ID'];
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
                        $state_rec['CONDITION_ADVANCED'] = 'if (gg(\'' . $linked_object . '.' . $linked_property . '\')>=gg(\'' . $linked_object . '.minValue\') && gg(\'' . $linked_object . '.' . $linked_property . '\')<=gg(\'' . $linked_object . '.maxValue\')) {' . "\n " . '$display=1;' . "\n" . '} else {' . "\n " . '$display=0;' . "\n" . '}';
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

            } elseif ($element_rec['TYPE'] == 'button') {
                $linked_method = 'pressed';
                $state_rec = array();
                $state_rec['TITLE'] = 'default';
                $state_rec['ELEMENT_ID'] = $element_rec['ID'];
                $state_rec['HTML'] = $element_rec['TITLE'];
                if ($linked_object && $linked_method) {
                    $state_rec['ACTION_OBJECT'] = $linked_object;
                    $state_rec['ACTION_METHOD'] = $linked_method;
                }
                $state_rec['ID'] = SQLInsert('elm_states', $state_rec);
            }
        }

    }


    function checkLinkedDevicesAction($object_title, $value = 0) {
        startMeasure('checkLinkedDevicesAction');
        $device1 = SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '" . $object_title . "'");
        if (!$device1['ID']) {
            endMeasure('checkLinkedDevicesAction');
            return 0;
        }
        include(DIR_MODULES . 'devices/devices_links_actions.inc.php');
        endMeasure('checkLinkedDevicesAction');
        return 1;
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        parent::install();

        @include_once(ROOT . 'languages/' . $this->name . '_' . SETTINGS_SITE_LANGUAGE . '.php');
        @include_once(ROOT . 'languages/' . $this->name . '_default' . '.php');

        SQLExec("UPDATE project_modules SET TITLE='" . LANG_DEVICES_MODULE_TITLE . "' WHERE NAME='" . $this->name . "'");

        $this->setDictionary();
        $this->renderStructure();
        $this->homebridgeSync();
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
        SQLDropTable('devices');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data = '')
    {
        /*
        devices -
        */
        $data = <<<EOD
 devices: ID int(10) unsigned NOT NULL auto_increment
 devices: TITLE varchar(100) NOT NULL DEFAULT ''
 devices: ALT_TITLES varchar(255) NOT NULL DEFAULT ''
 devices: TYPE varchar(100) NOT NULL DEFAULT ''
 devices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 devices: LOCATION_ID int(10) unsigned NOT NULL DEFAULT 0  
 devices: FAVORITE int(3) unsigned NOT NULL DEFAULT 0 
 devices: SYSTEM_DEVICE int(3) unsigned NOT NULL DEFAULT 0
 devices: CLICKED datetime DEFAULT NULL

 devices: SYSTEM varchar(255) NOT NULL DEFAULT ''
 devices: SUBTYPE varchar(100) NOT NULL DEFAULT ''
 devices: ENDPOINT_MODULE varchar(255) NOT NULL DEFAULT ''
 devices: ENDPOINT_NAME varchar(255) NOT NULL DEFAULT ''
 devices: ENDPOINT_TITLE varchar(255) NOT NULL DEFAULT ''
 devices: ROLES varchar(100) NOT NULL DEFAULT ''

 devices_linked: ID int(10) unsigned NOT NULL auto_increment
 devices_linked: DEVICE1_ID int(10) unsigned NOT NULL DEFAULT 0
 devices_linked: DEVICE2_ID int(10) unsigned NOT NULL DEFAULT 0
 devices_linked: LINK_TYPE varchar(100) NOT NULL DEFAULT ''
 devices_linked: LINK_SETTINGS text
 devices_linked: COMMENT varchar(255) NOT NULL DEFAULT ''
  
 devices_groups: ID int(10) unsigned NOT NULL auto_increment
 devices_groups: SYS_NAME varchar(100) NOT NULL DEFAULT ''
 devices_groups: TITLE varchar(255) NOT NULL DEFAULT ''
 devices_groups: APPLY_TYPES text

 devices_scheduler_points: ID int(10) unsigned NOT NULL auto_increment
 devices_scheduler_points: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''
 devices_scheduler_points: VALUE varchar(255) NOT NULL DEFAULT ''
 devices_scheduler_points: SET_TIME varchar(50) NOT NULL DEFAULT ''
 devices_scheduler_points: SET_DAYS varchar(50) NOT NULL DEFAULT '' 
 devices_scheduler_points: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 devices_scheduler_points: ACTIVE int(3) NOT NULL DEFAULT '1'
 devices_scheduler_points: LATEST_RUN datetime

EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDE5LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
