<?php
/**
 * Events
 *
 * Events
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <sergejey@gmail.com> https://majordomohome.com/
 * @version 0.2 (wizard, 15:03:07 [Mar 27, 2009])
 */
//
//
class events extends module
{
    /**
     * events
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "events";
        $this->title = "<#LANG_MODULE_EVENTS#>";
        $this->module_category = "<#LANG_SECTION_SYSTEM#>";
        $this->checkInstalled();
    }

    function api($params)
    {
        global $input, $method;

        $request = isset($params['request']) && is_array($params['request']) ? $params['request'] : array();
        $endpoint = isset($request[0]) ? strtolower($request[0]) : '';

        if ($endpoint == 'get') {
            if ($method != 'GET') {
                return array('error' => 'GET method required');
            }
            if (!isset($_GET['event_name']) || !is_string($_GET['event_name']) || trim($_GET['event_name']) == '') {
                return array('error' => 'Event name required');
            }

            $event_name = trim($_GET['event_name']);
            $event = SQLSelectOne("SELECT ID, EVENT_NAME, DETAILS, ADDED FROM events WHERE EVENT_TYPE='system' AND EVENT_NAME='" . DBSafe($event_name) . "' ORDER BY ADDED DESC, ID DESC LIMIT 1");
            if (empty($event['ID'])) {
                return array('error' => 'Event not found');
            }

            $result = array(
                'event_id' => $event['ID'],
                'event_name' => $event['EVENT_NAME'],
                'details' => $event['DETAILS'],
                'created_at' => $event['ADDED']
            );

            if (isset($_GET['property'])) {
                if (!is_string($_GET['property']) || trim($_GET['property']) == '') {
                    return array_merge($result, array('error' => 'Property name must be a non-empty string'));
                }

                $property = trim($_GET['property']);
                $event_param = SQLSelectOne("SELECT TITLE, VALUE, UPDATED FROM events_params WHERE EVENT_ID=" . (int)$event['ID'] . " AND TITLE='" . DBSafe($property) . "' ORDER BY ID DESC LIMIT 1");
                $property_found = false;

                if (isset($event_param['TITLE'])) {
                    $result['property'] = $event_param['TITLE'];
                    $result['value'] = $event_param['VALUE'];
                    $result['property_updated_at'] = $event_param['UPDATED'];
                    $property_found = true;
                } else {
                    $details = is_string($event['DETAILS']) ? json_decode($event['DETAILS'], true) : null;
                    if (is_array($details) && json_last_error() == JSON_ERROR_NONE && array_key_exists($property, $details)) {
                        $result['property'] = $property;
                        $result['value'] = $details[$property];
                        $property_found = true;
                    } elseif (is_string($event['DETAILS']) && $event['DETAILS'] != '') {
                        $object = getObject($event['DETAILS']);
                        if (is_object($object)) {
                            $property_id = $object->getPropertyByName($property, $object->class_id, $object->id);
                            if ($property_id) {
                                $result['property'] = $property;
                                $result['value'] = $object->getProperty($property);
                                $result['property_updated_at'] = $object->getProperty($property . '__updated');
                                $property_found = true;
                            }
                        }
                    }
                }

                if (!$property_found) {
                    return array_merge($result, array('error' => 'Property not found for event'));
                }
            }

            $result['result'] = true;
            return $result;
        }

        if ($endpoint == 'set') {
            if ($method != 'POST') {
                return array('error' => 'POST method required');
            }
            if (!is_array($input) || !isset($input['event_name']) || !is_string($input['event_name']) || trim($input['event_name']) == '') {
                return array('error' => 'Event name required in JSON body');
            }

            $event_name = trim($input['event_name']);
            $details = isset($input['details']) ? $input['details'] : '';
            $event_id = registerEvent($event_name, $details);
            if (!$event_id) {
                return array('error' => 'Failed to set event');
            }

            return array(
                'event_id' => $event_id,
                'event_name' => $event_name,
                'result' => true
            );
        }

        return array('error' => 'Not supported');
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

        $ok_msg = gr('ok_msg');
        if ($ok_msg != '') {
            $out['OK_MSG'] = $ok_msg;
        }

        if ($this->mode == 'update_settings') {

            $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='EVENTS_KEEP_HISTORY'");
            $rec['NAME']='EVENTS_KEEP_HISTORY';
            $rec['VALUE']=gr('keep_history','int');
            SQLInsertUpdate('settings',$rec);


            $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='EVENTS_MQTT_FORWARD'");
            $rec['NAME']='EVENTS_MQTT_FORWARD';
            $rec['VALUE']=gr('mqtt_forward','int');
            SQLInsertUpdate('settings',$rec);

            $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='EVENTS_MQTT_ROOT'");
            $rec['NAME']='EVENTS_MQTT_ROOT';
            $rec['VALUE']=gr('mqtt_root');
            SQLInsertUpdate('settings',$rec);


            $this->redirect("?ok_msg=" . urlencode(LANG_DATA_SAVED));
        }

        if ($this->data_source == 'events' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_events') {
                $this->search_events($out);
            }
            if ($this->view_mode == 'edit_events') {
                $this->edit_events($out, $this->id);
            }
            if ($this->view_mode == 'delete_events') {
                $this->delete_events($this->id);
                $this->redirect("?");
            }
            if ($this->view_mode == 'multiple_events') {
                global $ids;
                if (is_array($ids)) {
                    $total_selected = count($ids);
                    global $delete;
                    for ($i = 0; $i < $total_selected; $i++) {
                        $id = $ids[$i];
                        if ($delete) {
                            // operation: DELETE
                            $this->delete_events($id);
                        }
                    }
                }
                $this->redirect("?");
            }
        }
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
        global $session;

        if ($this->ajax) {
            $events = SQLSelect("SELECT ID, EVENT_NAME, DESCRIPTION, DETAILS, ADDED FROM events ORDER BY ADDED DESC LIMIT 8");
            $total = count($events);
            echo "<table class='table'>";
            for ($i = 0; $i < $total; $i++) {
                echo "<tr>";
                echo "<td><a href='" . ROOTHTML . "panel/event/" . $events[$i]['ID'] . ".html'>" . $events[$i]['EVENT_NAME'] . "</a></td>";
                echo "<td><i>" . $events[$i]['DESCRIPTION'] . '</i><div>' . $events[$i]['DETAILS'] . "</div></td>";
                echo "<td>" . $events[$i]['ADDED'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            exit;
        }

        if ($this->action == 'addevent') {

            global $mode;
            $this->mode = $mode;

            if ($this->mode == 'update') {
                global $type;
                global $window;
                global $details;
                global $terminal_to;
                global $user_to;
                $event = array();
                $event['EVENT_TYPE'] = $type;
                $event['WINDOW'] = $window;
                $event['DETAILS'] = $details;
                $event['TERMINAL_TO'] = $terminal_to;
                $event['TERMINAL_FROM'] = $session->data['TERMINAL'];
                $event['USER_TO'] = $user_to;
                $event['USER_FROM'] = $session->data['USERNAME'];
                $event['ADDED'] = date('Y-m-d H:i:s');
                $event['EXPIRE'] = date('Y-m-d H:i:s', time() + 5 * 60); //5 minutes expire
                SQLInsert('events', $event);
                postToWebSocketQueue('TERMINAL_EVENT', $event, 'PostEvent');
            }

            $terminals = getAllTerminals(-1, 'TITLE');
            $total = count($terminals);
            for ($i = 0; $i < $total; $i++) {
                if ($terminals[$i]['NAME'] == $session->data['TERMINAL']) {
                    $terminals[$i]['SELECTED'] = 1;
                    $out['TERMINAL_TITLE'] = $terminals[$i]['TITLE'];
                }
            }
            $out['TERMINALS'] = $terminals;

            $users = SQLSelect("SELECT * FROM users ORDER BY NAME");
            $total = count($users);
            for ($i = 0; $i < $total; $i++) {
                if ($users[$i]['USERNAME'] == $session->data['USERNAME']) {
                    $users[$i]['SELECTED'] = 1;
                    $out['USER_TITLE'] = $users[$i]['NAME'];
                }
            }
            $out['USERS'] = $users;

        }

        if ($this->action == 'getnextevent') {
            if (!$session->data['TERMINAL']) {
                $session->data['TERMINAL'] = 'temp' . date('YmdHis');
            }
            //echo "next event for ".$session->data['USERNAME']." on ".$session->data['TERMINAL'];//.date('H:i:s')
            SQLExec("DELETE FROM events WHERE EXPIRE<NOW() AND EVENT_TYPE!='system'");
            $qry = "1";
            //$qry.=" AND TERMINAL_FROM!='".DBSafe($session->data['TERMINAL'])."'";
            $qry .= " AND EVENT_TYPE!='system'";
            $qry .= " AND PROCESSED=0";
            $qry .= " AND (TERMINAL_TO='*' OR TERMINAL_TO='" . DBSafe(isset($session->data['TERMINAL'])?$session->data['TERMINAL']:'') . "')";
            $qry .= " AND (USER_TO='*' OR USER_TO='" . DBSafe(isset($session->data['USERNAME'])?$session->data['USERNAME']:'') . "')";
            $event = SQLSelectOne("SELECT * FROM events WHERE $qry ORDER BY ADDED");
            if (isset($event['ID'])) {
                $res = $event['ID'] . '|' . $event['EVENT_TYPE'] . '|' . $event['WINDOW'] . '|' . str_replace("\n", '\n', $event['DETAILS']);
                echo $res;
                $event['PROCESSED'] = 1;
                SQLUpdate('events', $event);
            }
            exit;
        }
    }

    function registerEvent($eventName, $details = '', $expire_in = 0)
    {
        $sqlQuery = "SELECT *
                  FROM events
                 WHERE EVENT_NAME = '" . DBSafe($eventName) . "'
                   AND EVENT_TYPE = 'system'
                 ORDER BY ID DESC
                 LIMIT 1";

        $rec = array();
        $rec = SQLSelectOne($sqlQuery);

        $rec['EVENT_NAME'] = $eventName;
        $rec['EVENT_TYPE'] = 'system';
        if (!is_array($details)) {
            $rec['DETAILS'] = $details;
        } else {
            $rec['DETAILS'] = json_encode($details);
        }
        $rec['ADDED'] = date('Y-m-d H:i:s');
        if ($expire_in) {
            $rec['EXPIRE'] = date('Y-m-d H:i:s', time() + $expire_in * 24 * 60 * 60);
        } else {
            unset($rec['EXPIRE']);
        }
        $rec['PROCESSED'] = 1;

        if ($rec['ID']) {
            SQLUpdate('events', $rec);
        } else {
            $rec['ID'] = SQLInsert('events', $rec);
        }

        if (is_array($details)) {
            $params = $details;
        } else {
            $params = array();
        }
        $params['updated'] = time();

        foreach ($params as $k => $v) {
            $param_rec = SQLSelectOne("SELECT * FROM events_params WHERE EVENT_ID=" . $rec['ID'] . " AND TITLE LIKE '" . DBSafe($k) . "'");
            $param_rec['TITLE'] = $k;
            $param_rec['VALUE'] = $v;
            $param_rec['UPDATED'] = date('Y-m-d H:i:s');
            $param_rec['EVENT_ID'] = $rec['ID'];
            if ($param_rec['ID']) {
                SQLUpdate('events_params', $param_rec);
            } else {
                $param_rec['ID'] = SQLInsert('events_params', $param_rec);
            }
            if ($param_rec['LINKED_OBJECT'] && $param_rec['LINKED_PROPERTY']) {
                setGlobal($param_rec['LINKED_OBJECT'] . '.' . $param_rec['LINKED_PROPERTY'], $v);
            }
            if ($param_rec['LINKED_OBJECT'] && $param_rec['LINKED_METHOD']) {
                $method_params = array();
                $method_params['VALUE'] = $v;
                $method_params['EVENT'] = $rec['TITLE'];
                callMethodSafe($param_rec['LINKED_OBJECT'] . '.' . $param_rec['LINKED_METHOD'], $method_params);
            }
        }


        if (defined('SETTINGS_EVENTS_KEEP_HISTORY') && (int)SETTINGS_EVENTS_KEEP_HISTORY>0) {
            $this->clear_expired_events();
        }
        if (defined('SETTINGS_EVENTS_MQTT_FORWARD') && SETTINGS_EVENTS_MQTT_FORWARD) {
            $mqtt_root = SETTINGS_EVENTS_MQTT_ROOT;
            if (!$mqtt_root) {
                $mqtt_root='/events';
            }
            $path = $mqtt_root.'/'.$eventName;
            $path = str_replace('//','/',$path);
            if (is_array($details)) {
                $msg = json_encode($details);
            } else {
                $msg = $details;
            }
            callAPI('/api/module/mqtt','GET',array('publish'=>$path,'msg'=>$msg));
        }

        return $rec['ID'];

    }

    function clear_expired_events() {
        if (!defined('SETTINGS_EVENTS_KEEP_HISTORY') || (int)SETTINGS_EVENTS_KEEP_HISTORY==0) return false;
        $expired = SQLSelect("SELECT ID, EVENT_ID FROM events_params WHERE LINKED_OBJECT='' AND UPDATED<'".date('Y-m-d H:i:s',time()-SETTINGS_EVENTS_KEEP_HISTORY*24*60*60)."'");
        $total = count($expired);
        if (!$total) return;
        $event_ids=array(0);
        for($i=0;$i<$total;$i++) {
            SQLExec("DELETE FROM events_params WHERE ID=".$expired[$i]['ID']);
            $event_ids[]=$expired[$i]['EVENT_ID'];
        }
        $events=SQLSelect("SELECT events.ID, events_params.ID as PARAM_ID FROM events LEFT JOIN events_params ON events.ID=events_params.EVENT_ID WHERE events.DESCRIPTION='' AND events.ID IN (".implode(',',$event_ids).") AND IsNull(events_params.ID)");
        $total = count($events);
        if ($total>0) {
            for($i=0;$i<$total;$i++) {
                SQLExec("DELETE FROM events WHERE ID=".$events[$i]['ID']);
            }
        }
    }

    /**
     * events search
     *
     * @access public
     */
    function search_events(&$out)
    {
        require(DIR_MODULES . $this->name . '/events_search.inc.php');
    }

    function pathToTree($array)
    {
        $tree = array();
        foreach ($array AS $item) {
            $pathIds = explode("/", ltrim($item["EVENT_NAME"], "/") . '/' . $item["ID"]);
            $current = &$tree;
            foreach ($pathIds AS $id) {
                if (!isset($current["CHILDS"][$id])) $current["CHILDS"][$id] = array();
                $current = &$current["CHILDS"][$id];
                if ($id == $item["ID"]) {
                    $current = $item;
                }
            }
        }
        //print_r($tree);exit;
        return ($this->childsToArray($tree['CHILDS']));
        //return $tree["CHILDS"];
    }

    function childsToArray($items)
    {
        $res = array();
        foreach ($items as $k => $v) {
            if (!$v['EVENT_NAME']) {
                $v['TITLE'] = $k;
            } else {
                $tmp = explode('/', $v['EVENT_NAME']);
                $v['TITLE'] = $tmp[count($tmp) - 1];
                $v['TITLE'] = $v['EVENT_NAME'];
            }
            if (isset($v['CHILDS'])) {
                $items = $this->childsToArray($v['CHILDS']);
                if (count($items) == 1) {
                    $v = $items[0];
                } else {
                    $v['ITEMS'] = $items;
                }
                unset($v['CHILDS']);
            }
            $res[] = $v;
        }
        return $res;
    }

    /**
     * events edit/add
     *
     * @access public
     */
    function edit_events(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/events_edit.inc.php');
    }

    /**
     * events delete record
     *
     * @access public
     */
    function delete_events($id)
    {
        $rec = SQLSelectOne("SELECT * FROM events WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM events_params WHERE EVENT_ID=" . $rec['ID']);
        SQLExec("DELETE FROM events WHERE ID='" . $rec['ID'] . "'");
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
        parent::install($parent_name);
        SQLExec("UPDATE project_modules SET HIDDEN=0 WHERE NAME LIKE '" . $this->name . "'");
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
        SQLDropTable('events');
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
        /*
        events - Events
        */
        $data = <<<EOD

 events: ID int(10) unsigned NOT NULL auto_increment
 events: EVENT_NAME varchar(255) NOT NULL DEFAULT ''
 events: EVENT_TYPE char(10) NOT NULL DEFAULT ''
 events: TERMINAL_FROM varchar(255) NOT NULL DEFAULT ''
 events: TERMINAL_TO varchar(255) NOT NULL DEFAULT ''
 events: USER_FROM varchar(255) NOT NULL DEFAULT ''
 events: USER_TO varchar(255) NOT NULL DEFAULT ''
 events: WINDOW varchar(255) NOT NULL DEFAULT ''
 events: DETAILS text
 events: ADDED datetime
 events: EXPIRE datetime
 events: PROCESSED int(3) NOT NULL DEFAULT '0'
 events: DESCRIPTION varchar(255) NOT NULL DEFAULT ''
 
 events_params: ID int(10) unsigned NOT NULL auto_increment
 events_params: EVENT_ID int(10) unsigned NOT NULL DEFAULT '0'
 events_params: TITLE varchar(255) NOT NULL DEFAULT ''  
 events_params: VALUE varchar(255) NOT NULL DEFAULT ''
 events_params: UPDATED datetime
 events_params: LINKED_OBJECT varchar(255) NOT NULL DEFAULT '' 
 events_params: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT '' 
 events_params: LINKED_METHOD varchar(255) NOT NULL DEFAULT '' 
 
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDI3LCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>