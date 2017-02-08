<?php
include_once("./config.php");
include_once("./lib/loader.php");
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total    = count($settings);
for ($i = 0; $i < $total; $i ++)
    Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);
// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php'))
    include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');
include_once (ROOT . 'languages/default.php');
if (defined('SETTINGS_SITE_TIMEZONE'))
{
    ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
}
header ('Content-Type: text/html; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$url=$_SERVER['REQUEST_URI'];
$url=preg_replace('/\?.+/','',$url);
$request = explode('/', trim($url,'/'));
array_shift($request);

$input = json_decode(file_get_contents('php://input'),true);

$result=array();
if (strtolower($request[0])=='data' && isset($request[1])) {
    $tmp=explode('.',$request[1]);
    if ($method=='GET') {
        if (isset($tmp[1])) {
            $result['data']=getGlobal($request[1]);
        } else {
            $object=getObject($tmp[0]);
            include_once(DIR_MODULES.'classes/classes.class.php');
            $cl=new classes();
            $props=$cl->getParentProperties($object->class_id, '', 1);
            $my_props=SQLSelect("SELECT ID,TITLE FROM properties WHERE OBJECT_ID='".$object->id."'");
            if (IsSet($my_props[0])) {
                foreach($my_props as $p) {
                    $props[]=$p;
                }
            }
            foreach($props as $k=>$v) {
                $result['data'][$v['TITLE']]=$object->getProperty($v['TITLE']);
            }
        }
    }
} elseif (strtolower($request[0])=='method' && isset($request[1])) {
    $res=callMethod($request[1],$_GET);
    if (!is_null($res)) {
        $result['result']=$res;
    } else {
        $result['result']='OK';
    }
} elseif (strtolower($request[0])=='script' && isset($request[1])) {
    $res=runScript($request[1],$_GET);
    if (!is_null($res)) {
        $result['result']=$res;
    } else {
        $result['result']='OK';
    }
}

echo json_encode($result);

// closing database connection
$db->Disconnect();

?>