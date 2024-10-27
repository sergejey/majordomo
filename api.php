<?php

include_once("./config.php");

if (isset($argv[0]) && $argv[0] != '') {
    set_time_limit(60);
    ignore_user_abort(1);
    foreach ($argv as $param) {
        $tmp = json_decode(unserialize($param), true);
        if (is_array($tmp)) {
            foreach ($tmp as $k => $v) {
                if ($k == 'REQUEST_URI') {
                    $_SERVER['REQUEST_URI'] = $v;
                    continue;
                }
                if ($k == 'REQUEST_METHOD') {
                    $_SERVER['REQUEST_METHOD'] = $v;
                    continue;
                }
                $_REQUEST[$k] = $v;
                $_GET[$k] = $v;
            }
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];
$rootHTML = preg_replace('/\//', '\/', ROOTHTML);
if (preg_match('/^' . $rootHTML . '/', $url)) {
    $url = preg_replace('/^' . $rootHTML . '/', '/', $url);
}
if (preg_match('/\/api\.php\?/', $url)) {
    $url = preg_replace('/\/api\.php\?/', '/api.php/', $url);
}
$url = preg_replace('/\?.+/', '', $url);
$request = explode('/', trim($url, '/'));

array_shift($request);

if (is_array($request)) {
    foreach ($request as &$param) {
        $param = urldecode($param);
    }
}

$input = json_decode(file_get_contents('php://input'), true);

$result = array();
$result['request']['url'] = $url;
$result['request']['params'] = $_REQUEST;

if (isset($request[0])) {
    include_once("./config.php");
    include_once("./lib/loader.php");
    include_once("./load_settings.php");

    startMeasure('TOTAL');
    $startedTime = getmicrotime();
    register_shutdown_function('apiShutdown');


    if (gr('prj')) {
        $session = new session("prj");
    }
}

if (!isset($request[0])) {
    $result['error'] = 'Incorrect usage';
} elseif (strtolower($request[0]) == 'devices' && $request[1] && $request[2] == 'links') {
    $device = SQLSelectOne("SELECT * FROM devices WHERE ID='" . (int)$request[1] . "'");
    if (!$device['ID']) {
        $result['result'] = false;
        $result['error'] = 'Device not found';
    } else {
        if ($method == 'DELETE') {
            $link_id = gr('link_id', 'int');
            $link = SQLSelectOne("SELECT devices_linked.* FROM devices_linked WHERE (DEVICE1_ID=" . (int)$device['ID'] . " OR DEVICE2_ID=" . (int)$device['ID'] . ") AND ID=" . $link_id);
            if ($link['ID']) {
                SQLExec("DELETE FROM devices_linked WHERE ID=" . $link_id);
                $result['result'] = true;
            } else {
                $result['result'] = false;
                $result['error'] = 'Link not found';
            }
        }
        if ($method == 'POST') {
            $link_id = gr('link_id', 'int');
            $link = SQLSelectOne("SELECT devices_linked.* FROM devices_linked WHERE (DEVICE1_ID=" . (int)$device['ID'] . " OR DEVICE2_ID=" . (int)$device['ID'] . ") AND ID=" . $link_id);
            $link['DEVICE1_ID'] = gr('device1_id', 'int');
            $link['DEVICE2_ID'] = gr('device2_id', 'int');
            $link['LINK_TYPE'] = gr('link_type');
            $settings = gr('link_settings');
            if ($settings != '') {
                $settings = json_decode($settings, true);
                $link['LINK_SETTINGS'] = serialize($settings);
            }
            $link['IS_ACTIVE'] = gr('active', 'int');
            if ($link['DEVICE1_ID'] && $link['DEVICE2_ID'] && $link['LINK_TYPE']) {
                if ($link['ID']) {
                    SQLUpdate('devices_linked', $link);
                } else {
                    $link['ID'] = SQLInsert('devices_linked', $link);
                }
                $result['result'] = true;
            } else {
                $result['result'] = false;
                $result['error'] = 'Incorrect data for the link';
            }
        }
        if ($method == 'GET') {

            include_once(DIR_MODULES . 'devices/devices.class.php');
            $devices_module = new devices();

            $links = SQLSelect("SELECT devices_linked.*, devices.TITLE FROM devices_linked LEFT JOIN devices ON devices.ID=DEVICE2_ID WHERE (DEVICE1_ID=" . (int)$device['ID'] . " OR DEVICE2_ID=" . (int)$device['ID'] . ") ORDER BY ID");
            if (isset($links[0]['ID'])) {
                $total = count($links);
                for ($i = 0; $i < $total; $i++) {
                    $device1 = SQLSelectOne("SELECT ID, TITLE FROM devices WHERE ID=" . (int)$links[$i]['DEVICE1_ID']);
                    $links[$i]['DEVICE1_TITLE'] = $device1['TITLE'];
                    $device2 = SQLSelectOne("SELECT ID, TITLE FROM devices WHERE ID=" . (int)$links[$i]['DEVICE2_ID']);
                    $links[$i]['DEVICE2_TITLE'] = $device2['TITLE'];
                    if ($links[$i]['LINK_SETTINGS'] != '') {
                        $links[$i]['LINK_SETTINGS'] = unserialize($links[$i]['LINK_SETTINGS']);
                        if (count(array_keys($links[$i]['LINK_SETTINGS'])) == 0) {
                            unset($links[$i]['LINK_SETTINGS']);
                        }
                    } else {
                        unset($links[$i]['LINK_SETTINGS']);
                    }
                }
            }
            $result['links'] = $links;

            $avail_links = $devices_module->getTypeLinks($device['TYPE']);
            $total = count($avail_links);
            for ($i = 0; $i < $total; $i++) {
                $avail_links[$i]['TARGET_DEVICES'] = array();
                if ($avail_links[$i]['TARGET_CLASS'] != '') {
                    $target_classes = explode(',', $avail_links[$i]['TARGET_CLASS']);
                    $target_classes = array_map('trim', $target_classes);
                    $other_devices = SQLSelect("SELECT ID, TITLE, TYPE FROM devices WHERE ID!=" . (int)$device['ID'] . " ORDER BY TITLE");
                    $totald = count($other_devices);
                    for ($id = 0; $id < $totald; $id++) {
                        $type_details = $devices_module->getTypeDetails($other_devices[$id]['TYPE']);
                        if (in_array($type_details['CLASS'], $target_classes) || in_array($type_details['PARENT_CLASS'], $target_classes)) {
                            $avail_links[$i]['TARGET_DEVICES'][] = $other_devices[$id];
                        }
                    }
                }
            }
            $result['available_links'] = $avail_links;
            $result['result'] = true;
        }
    }
} elseif (strtolower($request[0]) == 'devices' && $request[1] && $request[2] == 'schedule') {
    $device = SQLSelectOne("SELECT * FROM devices WHERE ID='" . (int)$request[1] . "'");
    if (!$device['ID']) {
        $result['result'] = false;
        $result['error'] = 'Device not found';
    } else {
        if ($method == 'DELETE') {
            $point_id = gr('point_id', 'int');
            $point = SQLSelectOne("SELECT * FROM devices_scheduler_points WHERE DEVICE_ID=" . (int)$device['ID'] . " AND ID=" . $point_id);
            if ($point['ID']) {
                SQLExec("DELETE FROM devices_scheduler_points WHERE ID=" . $point_id);
                $result['result'] = true;
            } else {
                $result['result'] = false;
                $result['error'] = 'Cannot delete: point (id: ' . $point_id . ') not found';
            }
        }
        if ($method == 'POST') {
            $point_id = gr('point_id', 'int');
            $point = SQLSelectOne("SELECT * FROM devices_scheduler_points WHERE DEVICE_ID=" . (int)$device['ID'] . " AND ID=" . $point_id);
            $point['LINKED_METHOD'] = gr('linked_method');
            $point['VALUE'] = gr('value');
            $point['SET_TIME'] = gr('set_time');
            $point['SET_DAYS'] = gr('set_days');
            $point['DEVICE_ID'] = $device['ID'];
            if ($point['LINKED_METHOD'] != '' && $point['SET_TIME'] != '') {
                if ($point['ID']) {
                    SQLUpdate('devices_scheduler_points', $point);
                } else {
                    $point['ID'] = SQLInsert('devices_scheduler_points', $point);
                }
                $result['result'] = true;
            } else {
                $result['error'] = 'Cannot update/create schedule point.';
                $result['result'] = false;
            }
        }
        if ($method == 'GET') {
            $points = SQLSelect("SELECT ID, LINKED_METHOD, VALUE, SET_TIME, SET_DAYS, LATEST_RUN FROM devices_scheduler_points WHERE DEVICE_ID=" . (int)$device['ID'] . " ORDER BY ID");
            $result['schedule_points'] = $points;
            $show_methods = array();
            include_once(DIR_MODULES . 'devices/devices.class.php');
            $devices_module = new devices();
            $methods = $devices_module->getAllMethods($device['TYPE']);
            if (is_array($methods)) {
                foreach ($methods as $k => $v) {
                    if (isset($v['_CONFIG_SHOW']) && $v['_CONFIG_SHOW']) {
                        $v['NAME'] = $k;
                        $show_methods[] = $v;
                    }
                }
            }
            $result['schedule_methods'] = $show_methods;
            $result['result'] = true;
        }
    }
} elseif (strtolower($request[0]) == 'devices' && $request[1] && $method == 'GET') {

    include_once(DIR_MODULES . 'devices/devices.class.php');
    $devices_module = new devices();
    $devices = $devices_module->getDevicesForAPI("devices.ID=" . (int)$request[1]);

    if (isset($devices[0])) {
        $device = $devices[0];
        $linksTotal = (int)current(SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices_linked WHERE DEVICE1_ID=" . $device['id'] . " OR DEVICE2_ID=" . $device['id']));
        $device['linksTotal'] = $linksTotal;
        $schedulePointsTotal = (int)current(SQLSelectOne("SELECT COUNT(*) as TOTAL FROM devices_scheduler_points WHERE DEVICE_ID=" . $device['id']));
        $device['scheduleTotal'] = $schedulePointsTotal;

        $sub_devices = $devices_module->getDevicesForAPI("devices.PARENT_ID=" . (int)$request[1]);
        if (isset($sub_devices[0])) {
            $device['subDevices'] = $sub_devices;
        }
        $result['device'] = $device;
    }

} elseif (strtolower($request[0]) == 'devices' && $request[1] && $method == 'POST') {
    $device = SQLSelectOne("SELECT * FROM devices WHERE ID='" . (int)$request[1] . "'");
    if (!$device['ID']) {
        $result['result'] = false;
        $result['error'] = 'Device not found';
    } else {
        include_once(DIR_MODULES . 'devices/devices.class.php');
        $devices_module = new devices();
        $type_details = $devices_module->getTypeDetails($device['TYPE']);

        $new_title = gr('title');
        if ($new_title) {
            $device['TITLE'] = $new_title;
            SQLUpdate('devices', $device);
        }

        $new_room = gr('room');
        if ($new_room) {
            $roomObject = getObject($new_room);
            if ($roomObject->location_id) {
                $device['LOCATION_ID'] = $roomObject->location_id;
                SQLUpdate('devices', $device);
                sg($device['LINKED_OBJECT'] . '.linkedRoom', $roomObject->object_title);
            }
        }

        $object_id = addClassObject($type_details['CLASS'], $device['LINKED_OBJECT'], 'sdevice' . $device['ID']);
        $object_rec = SQLSelectOne("SELECT * FROM objects WHERE ID=" . $object_id);
        $object_rec['DESCRIPTION'] = $device['TITLE'];
        $object_rec['LOCATION_ID'] = $device['LOCATION_ID'];
        SQLUpdate('objects', $object_rec);

        $result['result'] = true;
    }
} elseif (strtolower($request[0]) == 'devices') {
    include_once(DIR_MODULES . 'devices/devices.class.php');
    $devices_module = new devices();
    $result['devices'] = $devices_module->getDevicesForAPI("devices.PARENT_ID=0");
} elseif (strtolower($request[0]) == 'room' && (preg_match('/^\d+$/', $request[1])) && ($method == 'DELETE')) {
    $id = (int)$request[1];
    $room_rec = SQLSelectOne("SELECT * FROM locations WHERE ID=" . $id);
    if ($room_rec['ID']) {
        include_once(DIR_MODULES . 'locations/locations.class.php');
        $locations = new locations();
        $locations->delete_locations($room_rec['ID']);
        $result['result'] = true;
    } else {
        $result['result'] = false;
        $result['error'] = 'Room not found';
    }
} elseif (strtolower($request[0]) == 'room' && (is_array($input)) && ($method == 'POST')) {
    $ok = 1;
    if ($input['id']) {
        $room_rec = SQLSelectOne("SELECT * FROM locations WHERE ID=" . (int)$input['id']);
        if (!$room_rec['ID']) {
            $ok = 0;
        }
    }
    $room_rec['TITLE'] = $input['title'];
    if (!$room_rec['TITLE']) {
        $ok = 0;
    }
    $max_priority = (int)current(SQLSelectOne("SELECT MAX(PRIORITY) FROM locations"));
    if (isset($input['priority'])) {
        $room_rec['PRIORITY'] = $input['priority'];
    }
    if ($ok) {
        if (!$room_rec['ID']) {
            $room_rec['PRIORITY'] = $max_priority + 10;
            $room_rec['ID'] = SQLInsert('locations', $room_rec);
        } else {
            SQLUpdate('locations', $room_rec);
        }
        $object_title = getRoomObjectByLocation($room_rec['ID'], 1);
        $result['id'] = $room_rec['ID'];
        $result['title'] = $room_rec['TITLE'];
        $result['object'] = $object_title;
        $result['priority'] = $room_rec['PRIORITY'];
        $result['result'] = true;
    } else {
        $result['result'] = false;
    }


} elseif (strtolower($request[0]) == 'rooms' && ($request[1] == 'setOrder') && ($method == 'POST')) {
    if (is_array($input)) {
        $total = count($input);
        for ($i = 0; $i < $total; $i++) {
            $room = SQLSelectOne("SELECT * FROM locations WHERE ID=" . (int)$input[$i]['id']);
            if ($room['ID']) {
                $room['PRIORITY'] = (int)$input[$i]['priority'];
                SQLUpdate('locations', $room);
            }
        }
        $result['result'] = true;
    } else {
        $result['error'] = 'Incorrect input data';
        $result['result'] = false;
    }
} elseif (strtolower($request[0]) == 'rooms' && ($request[1])) {
    $location = SQLSelectOne("SELECT * FROM locations WHERE ID=" . (int)$request[1]);
    $result['room'] = array();
    if ($location['ID']) {
        $result['room']['title'] = $location['TITLE'];
        $result['room']['id'] = $location['ID'];
        $result['room']['object'] = getRoomObjectByLocation($location['ID'], 1);
        $devices = SQLSelect("SELECT * FROM devices WHERE LOCATION_ID=" . $location['ID']);
        $result['room']['devices'] = array();
        $total = count($devices);
        $cached_properties = array();
        for ($i = 0; $i < $total; $i++) {
            $device = array();
            $device['id'] = $devices[$i]['ID'];
            $device['title'] = $devices[$i]['TITLE'];
            $device['object'] = $devices[$i]['LINKED_OBJECT'];
            $device['type'] = $devices[$i]['TYPE'];
            $device['system_device'] = $devices[$i]['SYSTEM_DEVICE'];
            $obj = getObject($device['object']);
            if (!isset($cached_properties[$obj->class_id])) {
                $cached_properties[$obj->class_id] = getClassProperties($obj->class_id);
            }
            $properties = $cached_properties[$obj->class_id];
            foreach ($properties as $p) {
                $device[$p['TITLE']] = getGlobal($device['object'] . '.' . $p['TITLE']);
            }
            //$device['status']=getGlobal($device['object'].'.status');
            //$device['value']=getGlobal($device['object'].'.value');
            $result['room']['devices'][] = $device;
        }
    }
} elseif (strtolower($request[0]) == 'rooms') {
    $result['rooms'] = array();
    $locations = SQLSelect("SELECT * FROM locations ORDER BY PRIORITY DESC, TITLE");
    foreach ($locations as $k => $v) {
        $location = array();
        $location['id'] = $v['ID'];
        $location['title'] = processTitle($v['TITLE']);
        $location['priority'] = $v['PRIORITY'];
        $location['object'] = getRoomObjectByLocation($v['ID'], 1);
        $result['rooms'][] = $location;
    }
} elseif (strtolower($request[0]) == 'module') {
    $module_name = find_module($request[1]);
    $module_file = DIR_MODULES . $module_name . '/' . $module_name . '.class.php';
    if (file_exists($module_file)) {
        include_once($module_file);
        $module = new $module_name;
        if (method_exists($module, 'api')) {
            $params = $_REQUEST;
            $r = $request;
            array_shift($r);
            array_shift($r);
            $params['request'] = $r;
            $result['apiHandleResult'] = $module->api($params);
        } else
            $result['error'] = "Not supported";
    } else
        $result['error'] = "Not found module";
} elseif (strtolower($request[0]) == 'modulepropertyset' && isset($request[1])) {
    $module_name = find_module($request[1]);
    $module_file = DIR_MODULES . $module_name . '/' . $module_name . '.class.php';
    $object = gr('object');
    $property = gr('property');
    $value = gr('value');
    if (file_exists($module_file) && $object && $property) {
        include_once($module_file);
        $module = new $module_name;
        if (method_exists($module, 'propertySetHandle')) {
            $result['setHandleResult'] = $module->propertySetHandle($object, $property, $value);
            $result['result'] = true;
        }
    }
} elseif (strtolower($request[0]) == 'events' && isset($request[1])) {
    array_shift($request);
    foreach ($request as &$request_item) {
        $request_item = urldecode($request_item);
    }
    $event_name = implode('/', $request);
    $result['event_name'] = $event_name;
    $result['params'] = $_GET;
    $result['event_id'] = registerEvent($event_name, $result['params']);
    $result['result'] = true;
} elseif (strtolower($request[0]) == 'objects') {
    $class_name = '';
    if (isset($request[1])) {
        $class_name = $request[1];
        $objects = getObjectsByClass($class_name);
        $properties = getClassProperties($class_name);
        $total = count($objects);
        for ($i = 0; $i < $total; $i++) {
            $objects[$i]['object'] = $objects[$i]['TITLE'];
            $objects[$i]['id'] = $objects[$i]['ID'];
            unset($objects[$i]['TITLE']);
            unset($objects[$i]['ID']);
            foreach ($properties as $property) {
                $property_title = $property['TITLE'];
                $objects[$i][$property_title] = getGlobal($objects[$i]['object'] . '.' . $property_title);
            }
        }
    } else {
        $objects = SQLSelect("SELECT ID, TITLE, TITLE as OBJECT FROM objects ORDER BY TITLE");
    }
    $result['objects'] = $objects;
} elseif (strtolower($request[0]) == 'data' && !isset($request[1]) && is_array($input['properties']) && $method == 'POST') {
    $properties = $input['properties'];
    foreach ($properties as $property) {
        $tmp = explode('.', $property);
        if (isset($tmp[1])) {
            $result['data'][$property] = getGlobal($property);
        } else {
            $object = getObject($property);
            if (is_object($object)) {
                $result['result'] = true;
                include_once(DIR_MODULES . 'classes/classes.class.php');
                $cl = new classes();
                $props = $cl->getParentProperties($object->class_id, '', 1);
                $my_props = SQLSelect("SELECT ID,TITLE FROM properties WHERE OBJECT_ID='" . $object->id . "'");
                if (isset($my_props[0])) {
                    foreach ($my_props as $p) {
                        $props[] = $p;
                    }
                }
                foreach ($props as $k => $v) {
                    $result['data'][$property . '.' . $v['TITLE']] = $object->getProperty($v['TITLE']);
                }
            }
        }
    }
    //dprint($input);
} elseif (strtolower($request[0]) == 'data' && isset($request[1])) {
    $tmp = explode('.', $request[1]);
    if ($method == 'GET') {
        if (isset($tmp[1])) {
            $result['data'] = getGlobal($request[1]);
        } else {
            $object = getObject($tmp[0]);
            $result['object'] = $object;
            include_once(DIR_MODULES . 'classes/classes.class.php');
            $cl = new classes();
            $props = $cl->getParentProperties($object->class_id, '', 1);
            $my_props = SQLSelect("SELECT ID,TITLE FROM properties WHERE OBJECT_ID='" . $object->id . "'");
            if (isset($my_props[0])) {
                foreach ($my_props as $p) {
                    $props[] = $p;
                }
            }
            foreach ($props as $k => $v) {
                $result['data'][$v['TITLE']] = $object->getProperty($v['TITLE']);
            }
        }
    } elseif ($method == 'POST') {
        if (isset($tmp[1])) {
            if (isset($_POST['data'])) {
                setGlobal($request[1], $_POST['data']);
            } else {
                setGlobal($request[1], $input['data']);
            }
        }
    }
} elseif (strtolower($request[0]) == 'history' && isset($request[1])) {
    $time_period = 0;
    if (!isset($request[2])) {
        $request[2] = 'day';
    }
    if (is_numeric($request[2])) {
        $time_period = $request[2] * 60 * 60;
    } elseif ($request[2] == 'day') {
        $time_period = 24 * 60 * 60;
    } elseif (preg_match('/(\d+)day/', $request[2], $m)) {
        $time_period = $m[1] * 24 * 60 * 60;
    } elseif ($request[2] == 'week') {
        $time_period = 7 * 24 * 60 * 60;
    } elseif (preg_match('/(\d+)week/', $request[2], $m)) {
        $time_period = $m[1] * 7 * 24 * 60 * 60;
    } elseif ($request[2] == 'month') {
        $time_period = 30 * 7 * 24 * 60 * 60;
    } elseif (preg_match('/(\d+)month/', $request[2], $m)) {
        $time_period = $m[1] * 30 * 24 * 60 * 60;
    } elseif ($request[2] == 'year') {
        $time_period = 365 * 7 * 24 * 60 * 60;
    } elseif (preg_match('/(\d+)year/', $request[2], $m)) {
        $time_period = $m[1] * 365 * 24 * 60 * 60;
    }
    if ($time_period > 0) {
        if (!isset($request[3])) {
            $result['result'] = getHistory($request[1], $time_period * (-1));
        } elseif ($request[3] == 'max') {
            $result['result'] = getHistoryMax($request[1], $time_period * (-1));
        } elseif ($request[3] == 'min') {
            $result['result'] = getHistoryMin($request[1], $time_period * (-1));
        } elseif ($request[3] == 'count') {
            $result['result'] = getHistoryCount($request[1], $time_period * (-1));
        } elseif ($request[3] == 'sum') {
            $result['result'] = getHistorySum($request[1], $time_period * (-1));
        } elseif ($request[3] == 'avg') {
            $result['result'] = getHistoryAvg($request[1], $time_period * (-1));
        }
    }
} elseif (strtolower($request[0]) == 'method' && isset($request[1])) {
    $res = callMethod($request[1], $_GET);
    if (!is_null($res)) {
        $result['result'] = $res;
    } else {
        $result['result'] = 'OK';
    }
} elseif (strtolower($request[0]) == 'script' && isset($request[1])) {
    $res = runScript($request[1], $_GET);
    if (!is_null($res)) {
        $result['result'] = $res;
    } else {
        $result['result'] = 'OK';
    }
} elseif (strtolower($request[0]) == 'messages') {
    if ($method == 'GET') {
        $limit = gr('limit', 'int');
        if (!$limit) $limit = 50;
        $qry = '1';
        $sqlQuery = "SELECT shouts.ID, shouts.MEMBER_ID as USER_ID, shouts.ADDED, shouts.MESSAGE, users.NAME
               FROM shouts
               LEFT JOIN users ON shouts.MEMBER_ID = users.ID
              WHERE $qry
              ORDER BY shouts.ADDED DESC, shouts.ID DESC
              LIMIT " . (int)$limit;
        $res = SQLSelect($sqlQuery);
        if (defined('SETTINGS_GENERAL_ALICE_NAME') && SETTINGS_GENERAL_ALICE_NAME != '') {
            $system_name = SETTINGS_GENERAL_ALICE_NAME;
        } else {
            $system_name = 'System';
        }
        $total = count($res);
        if ($total > 0) {
            $res = array_reverse($res);
            for ($i = 0; $i < $total; $i++) {
                if (!$res[$i]['USER_ID']) {
                    $res[$i]['NAME'] = $system_name;
                }
                if (!$res[$i]['NAME']) {
                    $res[$i]['NAME'] = 'User';
                }
            }
        }
        $result['messages'] = $res;

        $user = SQLSelectOne("SELECT ID, `NAME`, USERNAME FROM users ORDER BY ID");
        if ($user['ID']) {
            if (!$user['NAME']) $user['NAME'] = $user['USERNAME'];
            $result['user'] = $user;
        } else {
            $result['user']['ID'] = "0";
            $result['user']['NAME'] = "System";
        }

    }
    if ($method == 'POST') {
        $user = SQLSelectOne("SELECT ID FROM users ORDER BY ID");
        $user_id = $user['ID'];
        $say_source = 'API';
        $message = gr('message');
        if ($message && $user_id) {
            say(htmlspecialchars($message), 0, $user_id, $say_source);
            $result['result'] = 'OK';
        } else {
            $result['result'] = 'Error';
            $result['error'] = 'Incorrect setup';
        }
    }

} else {
    $result['error'] = 'Incorrect usage';
}

if (isset($result['error'])) {
    DebMes("Incorrect request (" . $method . "): " . json_encode($request) . "\nInput: " . json_encode($input) . "\nResult: " . json_encode($result), 'api');
    http_response_code(400);
} else {
    http_response_code(200);
}

if (function_exists('getmicrotime')) {
    $endTime = getmicrotime();
    $result['passed'] = round($endTime - $startedTime, 4);
}

$result['timestamp'] = time();

if (function_exists('endMeasure')) {
    endMeasure('TOTAL');
}

if (gr('performance')) {
    $result['performance'] = PerformanceReport(1);
}

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    //header("Access-Control-Allow-Methods: \"GET, HEAD\"");
    header("Content-type:application/json");
}
echo json_encode($result);

function apiShutdown()
{
    global $result;
    $a = error_get_last();
    if (isset($a['type']) && ($a['type'] === E_ERROR)) {
        DebMes("Result " . $_SERVER['REQUEST_URI'] . ' ' . json_encode($a), 'api_error');
    } elseif (isset($result['passed']) && $result['passed'] > 5) {
        DebMes("Result [" . $result['passed'] . "] of : " . $_SERVER['REQUEST_URI'] . ' ' . json_encode($result), 'api_slow');
    }
}


function find_module($module_name)
{
    if (empty($module_name))
        return '';

    $moduleName = strtolower($module_name);
    $moduleArr = scandir(DIR_MODULES);

    if (count($moduleArr) == 0)
        return '';

    foreach ($moduleArr as $f) {
        if (strtolower($f) == $moduleName)
            return $f;
    }

    return '';
}
