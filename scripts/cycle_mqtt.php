<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

set_time_limit(0);

include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");
include_once(DIR_MODULES . "mqtt/mqtt.class.php");

$mqtt = new mqtt();

//$mqtt->prepareQueueTable();
$mqtt->getConfig();

if ($mqtt->config['MQTT_CLIENT']) {
    $client_name = $mqtt->config['MQTT_CLIENT'];
} else {
    $client_name = "MajorDoMo MQTT Cycle";
}
$client_name = $client_name . ' (#' . uniqid() . ')';

if ($mqtt->config['MQTT_AUTH']) {
    $username = $mqtt->config['MQTT_USERNAME'];
    $password = $mqtt->config['MQTT_PASSWORD'];
}

$host = 'localhost';

if ($mqtt->config['MQTT_HOST']) {
    $host = $mqtt->config['MQTT_HOST'];
}

if ($mqtt->config['MQTT_PORT']) {
    $port = $mqtt->config['MQTT_PORT'];
} else {
    $port = 1883;
}

if ($mqtt->config['MQTT_QUERY']) {
    $query = $mqtt->config['MQTT_QUERY'];
} else {
    $query = '/var/now/#';
}

if ($mqtt->config['MQTT_STRIPMODE']) {
    $stripmode = $mqtt->config['MQTT_STRIPMODE'];
} else {
    $stripmode = 0;
}

$mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name);

if ($mqtt->config['MQTT_AUTH']) {
        $connect = $mqtt_client->connect(true, NULL, $username, $password);
    if (!$connect) {
        exit(1);
    }
} else {
        $connect = $mqtt_client->connect();
    if (!$connect) {
        exit(1);
    }
}

$query_list = explode(',', $query);
$total = count($query_list);
echo date('H:i:s') . " Topics to watch: $query (Total: $total)\n";
for ($i = 0; $i < $total; $i++) {
    $path = trim($query_list[$i]);
    echo date('H:i:s') . " Path: $path\n";
    $topics[$path] = array("qos" => 0, "function" => "procmsg");
}
foreach ($topics as $k => $v) {
    echo date('H:i:s') . " Subscribing to: $k  \n";
    $rec = array($k => $v);
    $mqtt_client->subscribe($rec, 0);
}
$previousMillis = 0;

while ($mqtt_client->proc()) {


    if ($mqtt->config['MQTT_WRITE_METHOD']==2) {
        $queue = checkOperationsQueue('mqtt_queue');
        foreach ($queue as $mqtt_data) {
            $topic=$mqtt_data['DATANAME'];
            $data_value=json_decode($mqtt_data['DATAVALUE'],true);
            $value=$data_value['v'];
            $qos=0;
            if (isset($data_value['q'])) {
                $qos=$data_value['q'];
            }
            $retain=0;
            if (isset($data_value['r'])) {
                $retain=$data_value['r'];
            }
            if ($topic!='') {
                echo "Publishing to $topic : $value\n";
                $mqtt_client->publish($topic, $value, $qos, $retain);
            }
        }
    }

    $currentMillis = round(microtime(true) * 10000);

    if ($currentMillis - $previousMillis > 10000) {
        $previousMillis = $currentMillis;

        //setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
        $time = time();
        if ($time - $checked_time > 49) {
        $checked_time = $time;
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $time, 1);
         }
         
        if (file_exists('./reboot') || IsSet($_GET['onetime'])) {

                        $mqtt_client->close();
            $db->Disconnect();
            exit;
        }
    }
}

$mqtt_client->close();

/**
 * Process message
 * @param mixed $topic Topic
 * @param mixed $msg Message
 * @return void
 */
function procmsg($topic, $msg) {
    //$url = BASE_URL . '/ajax/mqtt.html?op=process&topic='.urlencode($topic)."&msg=".urlencode($msg);
    //getURLBackground($url);
    if (!isset($topic) || !isset($msg)) return false;
    global $stripmode;

    if ($stripmode) {
        $rec = SQLSelectOne("SELECT ID FROM `mqtt` where `PATH` like '$topic%' and LINKED_OBJECT>''");
        if(!$rec['ID']) {
            echo date("Y-m-d H:i:s") . " Ignore received from {$topic} : $msg\n";
            return false;
        }
    }

    echo date("Y-m-d H:i:s") . " Received from {$topic} : $msg\n";
    if (function_exists('callAPI')) {
        callAPI('/api/module/mqtt','GET',array('topic'=>$topic,'msg'=>$msg));
    } else {
        global $mqtt;
        $mqtt->processMessage($topic, $msg);
    }
}

$db->Disconnect(); // closing database connection
