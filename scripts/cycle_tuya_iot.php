<?php

chdir(dirname(__FILE__) . '/../');

include_once('./config.php');
include_once('./lib/loader.php');
include_once('./lib/threads.php');

set_time_limit(0);

include_once('./load_settings.php');
include_once(DIR_MODULES . 'control_modules/control_modules.class.php');

$ctl = new control_modules();

include_once(DIR_MODULES . 'tuya/tuya.class.php');

include_once(DIR_MODULES . 'tuya//libMQTT/Client.php');

echo date('H:i:s') . ' Running ' . basename(__FILE__) . PHP_EOL;

$tuya_module = new tuya();

$tuya_module->getConfig();

if (!$tuya_module->config['TUYA_IOT'] ) {
  exit;   
}    

//$result = $tuya_module->Tuya_IOT_Login();
$result = $tuya_module->Tuya_IOT_GET('/v1.0/token?grant_type=1', True);

if (!$result->success) {
    debmes("Can't login to IOT cloud.".$result->msg);
}

$access_token = $result->result->access_token;
$tuya_module->config['TUYA_ACCESS_TOKEN'] = $access_token;
$tuya_module->config['TUYA_REFRESH_TOKEN'] = $result->result->refresh_token;
$tuya_module->config['TUYA_TOKEN_EXPIRE_TIME'] = $result->result->expire_time + time();
$tuya_module->config['TUYA_IOT_UID'] = $result->result->uid;

$tuya_module->saveConfig();

$cycle_debug = $tuya_module->config['TUYA_CYCLE_DEBUG'];
//$cycle_debug = true;

$mqtt_devices = SQLSelect("SELECT ID, DEV_ID FROM tudevices WHERE STATUS=2;");

if ($mqtt_devices) {
    foreach($mqtt_devices as $dev) {
	$devices[$dev['DEV_ID']] = $dev['ID'];
    }
}    

$mqtt_devices = array_column($mqtt_devices, 'DEV_ID');
$link_id = uniqid() ;

$key = '';
$latest_check = 0;
$latest_db_check = time();

$client = getMQTTConfig($link_id);

while (1==1) {
    if ($client->isConnected() == False) {
	debmes("MQTT Disconnected");
        $client = getMQTTConfig($link_id);    
    }
    $client->eventLoop();
    if ((time() - $latest_check) >= 10) {
        $latest_check = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
    }    
    if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
        echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME' . basename(__FILE__) . PHP_EOL;
        break;
    }
    
    usleep(500000);
    if ($expire_time<(time()-60)) {
        //echo 'Expired'.time(); 
	$client->close();
	$client = getMQTTConfig($link_id);
    } 
    
    if ((time() - $latest_db_check) >= 2*60) {
	$mqtt_devices = SQLSelect("SELECT ID, DEV_ID FROM tudevices WHERE STATUS=2;");

	if ($mqtt_devices) {
	    foreach($mqtt_devices as $dev) {
		$devices[$dev['DEV_ID']] = $dev['ID'];
	    }
	}    

	$mqtt_devices = array_column($mqtt_devices, 'DEV_ID');
	$latest_db_check = time();
    }
}
$client->close();

return;

function getMQTTConfig($link_id) {
    global $tuya_module;
    global $key;
    global $expire_time;
    
    $tuya_module->getConfig();
    //$uid = $tuya_module->config['TUYA_IOT_UID'];
    $uid = $tuya_module->config['TUYA_UID'];

    $data = array(
		    'uid' => $uid,
		    'link_id' => $link_id,
		    'link_type' => 'mqtt',
		    'topics' => 'device'
	    );

    //$url = '/v1.0/iot-03/open-hub/access-config';
    $url = '/v1.0/open-hub/access/config';
    $r_c = $tuya_module->Tuya_IOT_POST($url, $data, false);

    if (!$r_c->success) {
	debmes("Can't get MQTT conf.".$r_c->msg);
	exit;
    } 
    $client_name = $r_c->result->client_id;
    $url = parse_url($r_c->result->url);
    $expire_time = time() + (int)$r_c->result->expire_time;
    $user_name = $r_c->result->username;
    $password = $r_c->result->password;
    $topic = $r_c->result->source_topic->device;

    $key = substr($password,8,24);

    $client = new LibMQTT\Client($url['host'],$url['port'],$client_name);
    $client->setVerbose(0);
    $client->setCryptoProtocol("tls");
    $client->setAuthDetails($user_name, $password);
    $result = $client->connect();
    $result = $client->subscribe([$topic => [ "qos" => 1, "function" => "procMsg" ]]);

    return $client;
}

function procMsg($msg_topic, $msg, $qos) {
    global $key;
    global $mqtt_devices;
    global $tuya_module;
    global $cycle_debug;
    global $devices;

    $msg_json = json_decode($msg, true);
    $data = $msg_json['data'];

    $result = openssl_decrypt(base64_decode($data), 'AES-128-ECB', $key,OPENSSL_RAW_DATA);
    $result = json_decode($result, true);
    
    if (in_array($result['devId'], $mqtt_devices)) {
        foreach ($result['status'] as $status) {
            foreach($status as $k=>$v) {
             if (is_integer($k)) {
                 if (is_bool($v)) {
                     $v = $v?1:0;
                 }
                 if ($cycle_debug) {
                    debmes($result['devId'].'-'.$k.'='.$v);
                 }
		 //echo $result['devId'].'-'.$k.'='.$v.$PHP_EOL;   
                 $tuya_module->processCommand($devices[$result['devId']], $k, $v, 0, false);
              }       
            }    
        } 
     }   
}    
