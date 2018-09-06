<?php
/**
 * CONNECT MQTT CLIENT
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.3
 */
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

$started_time = time();
$max_run_time = 2*60*60; // do restart in 2 hours

set_time_limit(0);
const CONNECT_HOST = 'connect.smartliving.ru';
const CONNECT_PORT = '8883';
const CONNECT_CA_FILE = '/etc/ssl/certs';
//const CONNECT_PORT = '1883';



include_once(DIR_MODULES . 'connect/connect.class.php');

$menu_sent_time = 0;

include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");


$saved_devices_data=array();

while (1) {
    $connect = new connect();
    $connect->getConfig();
    
    if (!$connect->config['CONNECT_SYNC']) {
        echo "Connect sync turned off.";
        exit;
    }

    $sqlQuery = "SELECT * FROM commands";
    $commands = SQLSelect($sqlQuery);
    $total = count($commands);

    for ($i = 0; $i < $total; $i++) {
        $cmd_values[$commands[$i]['ID']] = $commands[$i]['CUR_VALUE'];
        $cmd_titles[$commands[$i]['ID']] = $commands[$i]['RENDER_TITLE'];
        $cmd_data[$commands[$i]['ID']] = $commands[$i]['RENDER_DATA'];
    }

    $username = strtolower($connect->config['CONNECT_USERNAME']);
    $password = $connect->config['CONNECT_PASSWORD'];
    $host = CONNECT_HOST;
    $port = CONNECT_PORT;
    if (defined('CONNECT_CA_FILE')) {
        $ca_file=CONNECT_CA_FILE;
    } else {
        $ca_file=NULL;
    }


    $query = $username . '/incoming_urls,' . $username . '/menu_session,'. $username . '/reverse_urls';
    $client_name = "MajorDoMo " . $username . " Connect";
    $mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name,$ca_file);

    if ($mqtt_client->connect(true, NULL, $username, $password)) {

        $query_list = explode(',', $query);
        $total = count($query_list);
        echo date('H:i:s') . " Topics to watch: $query (Total: $total)\n";
        for ($i = 0; $i < $total; $i++) {
            $path = trim($query_list[$i]);
            echo date('H:i:s') . " Path: $path\n";
            $topics[$path] = array("qos" => 0, "function" => "procmsg");
        }
        foreach ($topics as $k => $v) {
            echo date('H:i:s') . " Subscribing to: $k\n";
        }
        $mqtt_client->subscribe($topics, 0);
        while ($mqtt_client->proc()) {
            $currentMillis = round(microtime(true) * 10000);
            if ($currentMillis - $previousMillis > 10000) {
                $previousMillis = $currentMillis;
                setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
                if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
                    $db->Disconnect();
                    exit;
                }
            }
            if (!defined('DISABLE_SIMPLE_DEVICES')) {
                //$saved_devices_data
                $devices_data=checkOperationsQueue('connect_device_data');
                foreach($devices_data as $property_data) {
                    if (!isset($saved_devices_data[$property_data['DATANAME']]) || $saved_devices_data[$property_data['DATANAME']]!=$property_data['DATAVALUE']) {
                        $saved_devices_data[$property_data['DATANAME']]=$property_data['DATAVALUE'];
                        send_device_property($property_data['DATANAME'],$property_data['DATAVALUE']);
                    }
                }
            }
            if (time() - $menu_sent_time > 60 * 60) {
                $menu_sent_time = time();
                send_all_menu();
            }
            if ((time()-$started_time)>$max_run_time) {
                echo "Exit cycle CONNET... (reconnecting)";
                $mqtt_client->close();
                $db->Disconnect();
                exit;
            }
        }
        $mqtt_client->close();

    } else {
        echo date('Y-m-d H:i:s')." Failed to connect ...\n";
        sleep(10);
        continue;
    }
}

function procmsg($topic, $msg)
{
    echo date("Y-m-d H:i:s") . " Topic:{$topic} $msg\n";
    if (preg_match('/menu_session/is', $topic)) {
        global $cmd_values;
        global $cmd_titles;
        global $cmd_data;
        echo date("Y-m-d H:i:s") . " Menu session\n";
        $parent_id = $msg;
        if ($parent_id == 0) {
            global $menu_sent_time;
            if ((time()-$menu_sent_time)>30) {
                $menu_sent_time = time();                
                send_all_menu();                
            }
        } else {
            send_menu_element($parent_id);
        }
    } elseif (preg_match('/incoming_urls/is', $topic)) {
        $url = BASE_URL.$msg;
        echo date("Y-m-d H:i:s") . " Incoming url: $url\n";
        getURLBackground($url, 0);
    } elseif (preg_match('/reverse_urls/is', $topic)) {
        echo date("Y-m-d H:i:s") . " Incoming reverse url: $msg\n";
        send_reverse_result($msg,$result);
    }

}

function send_device_property($property,$value) {
    global $connect;
    $connect->sendDeviceProperty($property,$value);
}

function send_menu_element($parent_id) {
    echo date('Y-m-d H:i:s')." Sending menu element $parent_id\n";
    update_menu_data($parent_id);
}

function send_reverse_result($msg,$result) {
    global $connect;
    $url = BASE_URL.$msg;
    $result = getURL($url, 0);
    $connect->sendReverseURL($msg,$result);
}

function send_all_menu() {
    echo date('Y-m-d H:i:s')." Sending full menu\n";
    global $connect;
    update_menu_data(0);
    $connect->sendMenu(1);
}

function update_menu_data($element_id = 0) {
    global $connect;
    global $cmd_values;
    global $cmd_titles;
    global $cmd_data;

    $qry=1;
    if ($element_id) {
        $qry.=" AND (ID=".(int)$element_id." OR PARENT_ID=".(int)$element_id.")";
    }
    $sqlQuery = "SELECT * FROM commands WHERE $qry";    
    $commands = SQLSelect($sqlQuery);
    $total = count($commands);
    $changed_items=array();
    for ($i = 0; $i < $total; $i++) {
        $old_render_title = $commands[$i]['RENDER_TITLE'];
        $new_render_title = processTitle($commands[$i]['TITLE'], $connect);
        if ($old_render_title!=$new_render_title) {
            $commands[$i]['RENDER_TITLE'] = $new_render_title;
        }
        $old_render_data=$commands[$i]['RENDER_DATA'];
        $new_render_data='';
        if ($commands[$i]['DATA']!='') {
            $new_render_data=processTitle($commands[$i]['DATA'], $connect);
            if (strlen($new_render_data)>50*1024) {
                $new_render_data = substr($new_render_data,0,50*1024);
            }
            $commands[$i]['RENDER_DATA'] = $new_render_data;
        }
        if ($new_render_title!=$old_render_title || $new_render_data!=$old_render_data) {
            $commands[$i]['RENDER_UPDATED'] = date('Y-m-d H:i:s');
            SQLUpdate('commands', $commands[$i]);
        }
        $changed = 0;
        if ($cmd_values[$commands[$i]['ID']] != $commands[$i]['CUR_VALUE']) {
            $cmd_values[$commands[$i]['ID']] = $commands[$i]['CUR_VALUE'];
            $changed = 1;
        }
        if ($cmd_titles[$commands[$i]['ID']] != $commands[$i]['RENDER_TITLE']) {
            $cmd_titles[$commands[$i]['ID']] = $commands[$i]['RENDER_TITLE'];
            $changed = 1;
        }
        if ($cmd_data[$commands[$i]['ID']] != $commands[$i]['RENDER_DATA']) {
            $cmd_data[$commands[$i]['ID']] = $commands[$i]['RENDER_DATA'];
            $changed = 1;
        }
        if ($changed) {
            $changed_items[]=$commands[$i];
        }
        
    }    
    $total = count($changed_items);
    if ($total>0 && $element_id>0) {
        //echo "Sending changed items: ".json_encode($changed_items)."\n";
        $connect->sendMenuItems($changed_items);
    } elseif ($element_id>0) {
        //echo "Items not changed\n";
    }
}

