<?php
include_once("./config.php");
include_once("./lib/loader.php");
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");

header('Content-Type: text/html; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];
$url = preg_replace('/\?.+/', '', $url);
$request = explode('/', trim($url, '/'));
array_shift($request);

$input = json_decode(file_get_contents('php://input'), true);

$result = array();

if (!isset($request[0])) {
    $result['error'] = 'Incorrect usage';
} elseif (strtolower($request[0]) == 'devices' && $request[1]) {
    $devices=SQLSelect("SELECT * FROM devices WHERE ID=".(int)$request[1]);
    $total = count($devices);
    $cached_properties=array();
    for ($i = 0; $i < $total; $i++) {
        $device=array();
        $device['id']=$devices[$i]['ID'];
        $device['title']=$devices[$i]['TITLE'];
        $device['object']=$devices[$i]['LINKED_OBJECT'];
        $device['type']=$devices[$i]['TYPE'];
        $obj = getObject($device['object']);
        if (!isset($cached_properties[$obj->class_id])) {
            $cached_properties[$obj->class_id]=getClassProperties($obj->class_id);
        }
        $properties = $cached_properties[$obj->class_id];
        foreach($properties as $p) {
            $device[$p['TITLE']]=getGlobal($device['object'].'.'.$p['TITLE']);
        }
        /*
        $device['status']=getGlobal($device['object'].'.status');
        $device['value']=getGlobal($device['object'].'.value');
        */
        $result['device']=$device;
    }
} elseif (strtolower($request[0]) == 'devices') {
    $devices=SQLSelect("SELECT * FROM devices ORDER BY TITLE");
    $result['devices']=array();
    $total = count($devices);
    for ($i = 0; $i < $total; $i++) {
        $device=array();
        $device['id']=$devices[$i]['ID'];
        $device['title']=$devices[$i]['TITLE'];
        $device['object']=$devices[$i]['LINKED_OBJECT'];
        $device['type']=$devices[$i]['TYPE'];
        $obj = getObject($device['object']);
        if (!isset($cached_properties[$obj->class_id])) {
            $cached_properties[$obj->class_id]=getClassProperties($obj->class_id);
        }
        $properties = $cached_properties[$obj->class_id];
        foreach($properties as $p) {
            $device[$p['TITLE']]=getGlobal($device['object'].'.'.$p['TITLE']);
        }
        $result['devices'][]=$device;
    }
} elseif (strtolower($request[0]) == 'room' && (preg_match('/^\d+$/',$request[1])) && ($method=='DELETE')) {
    $id = (int)$request[1];
    $room_rec = SQLSelectOne("SELECT * FROM locations WHERE ID=".$id);
    if ($room_rec['ID']) {
        include_once(DIR_MODULES.'locations/locations.class.php');
        $locations = new locations();
        $locations->delete_locations($room_rec['ID']);
        $result['result']=true;
    } else {
        $result['result']=false;
        $result['error'] = 'Room not found';
    }
} elseif (strtolower($request[0]) == 'room' && (is_array($input)) && ($method=='POST')) {
    $ok=1;
    if ($input['id']) {
        $room_rec = SQLSelectOne("SELECT * FROM locations WHERE ID=".(int)$input['id']);
        if (!$room_rec['ID']) {
            $ok=0;
        }
    }
    $room_rec['TITLE']=$input['title'];
    if (!$room_rec['TITLE']) {
        $ok=0;
    }
    $max_priority = (int)current(SQLSelectOne("SELECT MAX(PRIORITY) FROM locations"));
    if (isset($input['priority'])) {
        $room_rec['PRIORITY']=$input['priority'];
    }
    if ($ok) {
        if (!$room_rec['ID']) {
            $room_rec['PRIORITY']=$max_priority+10;
            $room_rec['ID'] = SQLInsert('locations',$room_rec);
        } else {
            SQLUpdate('locations',$room_rec);
        }
        $object_title=getRoomObjectByLocation($room_rec['ID'],1);
        $result['id']=$room_rec['ID'];
        $result['title']=$room_rec['TITLE'];
        $result['object']=$object_title;
        $result['priority']=$room_rec['PRIORITY'];
        $result['result']=true;
    } else {
        $result['result']=false;
    }



} elseif (strtolower($request[0]) == 'rooms' && ($request[1]=='setOrder') && ($method=='POST')) {
    if (is_array($input)) {
        $total = count($input);
        for($i=0;$i<$total;$i++) {
            $room = SQLSelectOne("SELECT * FROM locations WHERE ID=".(int)$input[$i]['id']);
            if ($room['ID']) {
                $room['PRIORITY']=(int)$input[$i]['priority'];
                SQLUpdate('locations',$room);
            }
        }
        $result['result'] = true;
    } else {
        $result['error'] = 'Incorrect input data';
        $result['result'] = false;
    }
} elseif (strtolower($request[0]) == 'rooms' && ($request[1])) {
    $location=SQLSelectOne("SELECT * FROM locations WHERE ID=".(int)$request[1]);
    $result['room']=array();
    if ($location['ID']) {
        $result['room']['title']=$location['TITLE'];
        $result['room']['id']=$location['ID'];
        $result['room']['object']=getRoomObjectByLocation($location['ID'],1);
        $devices=SQLSelect("SELECT * FROM devices WHERE LOCATION_ID=".$location['ID']);
        $result['room']['devices']=array();
        $total = count($devices);
        $cached_properties=array();
        for ($i = 0; $i < $total; $i++) {
            $device=array();
            $device['id']=$devices[$i]['ID'];
            $device['title']=$devices[$i]['TITLE'];
            $device['object']=$devices[$i]['LINKED_OBJECT'];
            $device['type']=$devices[$i]['TYPE'];
            $obj = getObject($device['object']);
            if (!isset($cached_properties[$obj->class_id])) {
                $cached_properties[$obj->class_id]=getClassProperties($obj->class_id);
            }
            $properties = $cached_properties[$obj->class_id];
            foreach($properties as $p) {
                $device[$p['TITLE']]=getGlobal($device['object'].'.'.$p['TITLE']);
            }
            //$device['status']=getGlobal($device['object'].'.status');
            //$device['value']=getGlobal($device['object'].'.value');
            $result['room']['devices'][]=$device;
        }
    }
} elseif (strtolower($request[0]) == 'rooms') {
    $result['rooms']=array();
    $locations=SQLSelect("SELECT * FROM locations ORDER BY PRIORITY DESC, TITLE");
    foreach($locations as $k=>$v) {
        $location=array();
        $location['id']=$v['ID'];
        $location['title']=$v['TITLE'];
        $location['priority']=$v['PRIORITY'];
        $location['object']=getRoomObjectByLocation($v['ID'],1);
        $result['rooms'][]=$location;
    }
} elseif (strtolower($request[0]) == 'module') {
    $module_name = $request[1];
    $module_file = DIR_MODULES.$module_name.'/'.$module_name.'.class.php';
    if (file_exists($module_file)) {
        include_once($module_file);
        $module = new $module_name;
        if (method_exists($module,'api')) {
            $params = $request;
            array_shift($params);
            array_shift($params);
            $module->api($params);
            $db->Disconnect();
            exit;
        }
    }
} elseif (strtolower($request[0]) == 'events' && isset($request[1])) {
    array_shift($request);
    $event_name=implode('/',$request);
    $result['event_name']=$event_name;
    $result['params']=$_GET;
    $result['event_id']=registerEvent($event_name,$result['params']);
} elseif (strtolower($request[0]) == 'data' && isset($request[1])) {
    $tmp = explode('.', $request[1]);
    if ($method == 'GET') {
        if (isset($tmp[1])) {
            $result['data'] = getGlobal($request[1]);
        } else {
            $object = getObject($tmp[0]);
            include_once(DIR_MODULES . 'classes/classes.class.php');
            $cl = new classes();
            $props = $cl->getParentProperties($object->class_id, '', 1);
            $my_props = SQLSelect("SELECT ID,TITLE FROM properties WHERE OBJECT_ID='" . $object->id . "'");
            if (IsSet($my_props[0])) {
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
            setGlobal($request[1], $input['data']);
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
    if ($time_period>0) {
        if (!isset($request[3])) {
            $result['result']=getHistory($request[1],$time_period * (-1));
        } elseif ($request[3]=='max') {
            $result['result']=getHistoryMax($request[1],$time_period * (-1));
        } elseif ($request[3]=='min') {
            $result['result']=getHistoryMin($request[1],$time_period * (-1));
        } elseif ($request[3]=='count') {
            $result['result']=getHistoryCount($request[1],$time_period * (-1));
        } elseif ($request[3]=='sum') {
            $result['result']=getHistorySum($request[1],$time_period * (-1));
        } elseif ($request[3]=='avg') {
            $result['result']=getHistoryAvg($request[1],$time_period * (-1));
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
} else {
    $result['error'] = 'Incorrect usage';
}

if ($result['error']!='') {
    http_response_code(400);
} else {
    http_response_code(200);
}
header("Content-type:application/json");
echo json_encode($result);

// closing database connection
$db->Disconnect();

