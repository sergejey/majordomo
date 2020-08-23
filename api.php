<?php

include_once("./config.php");

if (isset($argv[0]) && $argv[0]!='') {
    set_time_limit(60);
    ignore_user_abort(1);
    foreach($argv as $param) {
        $tmp=json_decode(unserialize($param),true);
        if (is_array($tmp)) {
            foreach($tmp as $k=>$v) {
                if ($k=='REQUEST_URI') {
                    $_SERVER['REQUEST_URI']=$v;
                    continue;
                }
                if ($k=='REQUEST_METHOD') {
                    $_SERVER['REQUEST_METHOD']=$v;
                    continue;
                }
                $_REQUEST[$k]=$v;
                $_GET[$k]=$v;
            }
        }
    }
}

//DebMes("URL: ".$_SERVER['REQUEST_URI'].' '.json_encode($_REQUEST),'api_request');

$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];
$rootHTML=preg_replace('/\//', '\/', ROOTHTML);
if (preg_match('/^' . $rootHTML . '/', $url)) {
    $url=preg_replace('/^' . $rootHTML . '/', '/', $url);
}
if (preg_match('/\/api\.php\?/',$url)) {
    $url=preg_replace('/\/api\.php\?/','/api.php/',$url);
}
$url = preg_replace('/\?.+/', '', $url);
$request = explode('/', trim($url, '/'));

array_shift($request);

if (is_array($request)) {
    foreach($request as &$param) {
        $param=urldecode($param);
    }
}

$input = json_decode(file_get_contents('php://input'), true);

$result = array();
$result['request']['url']=$url;
$result['request']['params']=$_REQUEST;

if (isset($request[0])) {
    include_once("./config.php");
    include_once("./lib/loader.php");
    include_once("./load_settings.php");

    startMeasure('TOTAL');
    $startedTime=getmicrotime();
    register_shutdown_function('apiShutdown');


    if (gr('prj')) {
        $session = new session("prj");
    }
}

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
        $device['favorite']=$devices[$i]['FAVORITE'];
	$device['system_device']=$devices[$i]['SYSTEM_DEVICE'];
        $obj = getObject($device['object']);
        if (!isset($cached_properties[$obj->class_id])) {
            $cached_properties[$obj->class_id]=getClassProperties($obj->class_id);
        }
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
        $device['favorite']=$devices[$i]['FAVORITE'];
	$device['system_device']=$devices[$i]['SYSTEM_DEVICE'];
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
	    $device['system_device']=$devices[$i]['SYSTEM_DEVICE'];
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
        $location['title']=processTitle($v['TITLE']);
        $location['priority']=$v['PRIORITY'];
        $location['object']=getRoomObjectByLocation($v['ID'],1);
        $result['rooms'][]=$location;
    }
} elseif (strtolower($request[0]) == 'module') {
    $module_name = find_module($request[1]);
	$module_file = DIR_MODULES.$module_name.'/'.$module_name.'.class.php';
    if (file_exists($module_file)) {
        include_once($module_file);
        $module = new $module_name;
        if (method_exists($module,'api')) {
            $params = $_REQUEST;
            $r = $request;
			array_shift($r);
            array_shift($r);
            $params['request']=$r;
			$result['apiHandleResult']=$module->api($params);
        }
		else
			$result['error']="Not supported";
    }
	else
		$result['error']="Not found module";
} elseif (strtolower($request[0]) == 'modulepropertyset' && isset($request[1])) {
    $module_name = find_module($request[1]);
    $module_file = DIR_MODULES.$module_name.'/'.$module_name.'.class.php';
    $object = gr('object');
    $property = gr('property');
    $value = gr('value');
    if (file_exists($module_file) && $object && $property) {
        include_once($module_file);
        $module = new $module_name;
        if (method_exists($module,'propertySetHandle')) {
            $result['setHandleResult']=$module->propertySetHandle($object,$property,$value);
            $result['result'] = true;
        }
    }
} elseif (strtolower($request[0]) == 'events' && isset($request[1])) {
    array_shift($request);
    foreach($request as &$request_item) {
        $request_item=urldecode($request_item);
    }
    $event_name=implode('/',$request);
    $result['event_name']=$event_name;
    $result['params']=$_GET;
    $result['event_id']=registerEvent($event_name,$result['params']);
    $result['result'] = true;
} elseif (strtolower($request[0]) == 'objects') {
    if (isset($request[1])) {
        $objects=getObjectsByClass($request[1]);
    } else {
        $objects=SQLSelect("SELECT ID, TITLE FROM objects ORDER BY TITLE");
    }
    $result['objects'] = $objects;
} elseif (strtolower($request[0]) == 'data' && !isset($request[1]) && is_array($input['properties']) && $method=='POST') {
    $properties=$input['properties'];
    foreach($properties as $property) {
        $tmp = explode('.', $property);
        if (isset($tmp[1])) {
            $result['data'][$property]=getGlobal($property);
        } else {
            $object=getObject($property);
            if (is_object($object)) {
                $result['result'] = true;
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
                    $result['data'][$property.'.'.$v['TITLE']] = $object->getProperty($v['TITLE']);
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

if (isset($result['error'])) {
    http_response_code(400);
} else {
    http_response_code(200);
}

if (function_exists('getmicrotime')) {
    $endTime=getmicrotime();
    $result['passed']=round($endTime-$startedTime,4);
}

if (function_exists('endMeasure')) {
    endMeasure('TOTAL');
}

if ($_GET['performance']) {
    $result['performance']=PerformanceReport(1);
}

header("Content-type:application/json");
echo json_encode($result);

function apiShutdown() {
    global $result;
    $a=error_get_last();
    if (isset($a['type']) && ($a['type'] === E_ERROR)) {
        DebMes("Result ".$_SERVER['REQUEST_URI'].' '.json_encode($a),'api_error');
    } elseif (isset($result['passed']) && $result['passed']>5) {
        DebMes("Result [".$result['passed']."] of : ".$_SERVER['REQUEST_URI'].' '.json_encode($result),'api_slow');
    }
}


function find_module($module_name) {
    if(empty($module_name))
        return '';

    $moduleName = strtolower($module_name);
    $moduleArr = scandir(DIR_MODULES);

    if (count($moduleArr) == 0)
      return '';

    foreach ($moduleArr as $f) 
    {
      if (strtolower($f) == $moduleName)
         return $f;
    }

    return '';
}
