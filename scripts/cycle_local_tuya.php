<?php
/**
 * Tuya Cycle
 */

chdir(dirname(__FILE__) . '/../');

include_once('./config.php');
include_once('./lib/loader.php');
include_once('./lib/threads.php');

set_time_limit(0);

include_once('./load_settings.php');
include_once(DIR_MODULES . 'control_modules/control_modules.class.php');

$ctl = new control_modules();

include_once(DIR_MODULES . 'tuya/tuya.class.php');

$tuya_module = new tuya();
$tuya_module->getConfig();

echo date('H:i:s') . ' Running ' . basename(__FILE__) . PHP_EOL;

$latest_check = 0;

$latest_disc = 0;

$cycle_debug = false;

$tuya_local_interval = 5;

if ($tuya_module->config['TUYA_LOCAL_INTERVAL']) {
    $tuya_local_interval = $tuya_module->config['TUYA_LOCAL_INTERVAL'];
}

if ($tuya_module->config['TUYA_CYCLE_DEBUG']) {
    $cycle_debug = $tuya_module->config['TUYA_CYCLE_DEBUG'];
}


echo date('H:i:s') . ' Init Tuya ' . PHP_EOL;
echo date('H:i:s') . " Discover period - '.$tuya_local_interval.' seconds" . PHP_EOL;

     
$save_dsp =array();
$dps_null = array();

while (1) {
    if ((time() - $latest_disc) >= 5 * 60) {
        $latest_disc = time();
        $devices = SQLSelect("SELECT ID, TITLE, LOCAL_KEY, DEV_ID, DEV_IP, UUID, '' as MAC, 0 as 'ZIGBEE', SEND12, FLAGS12, TUYA_VER FROM tudevices WHERE LOCAL_KEY!='' and DEV_IP!='' and STATUS=1 ORDER BY DEV_ID");
        $gw_devices = SQLSelect("SELECT d.ID, d.TITLE, gw.LOCAL_KEY, d.DEV_ID, gw.DEV_IP, d.UUID, d.MAC, 1 as 'ZIGBEE' FROM tudevices d INNER JOIN tudevices gw ON d.MESH_ID = gw.DEV_ID WHERE gw.LOCAL_KEY!='' and gw.DEV_IP!='' and d.STATUS=1");
        $devices = array_merge($devices ,$gw_devices); 
        if ($cycle_debug) {
            debmes(date('H:i:s') . ' Tuya: added ' .count($devices) . ' devices for local monitoring' );
            echo date('H:i:s') . ' Tuya: added ' .count($devices) . ' devices for local monitoring'  . PHP_EOL;

        }     
    }    

    
    if ((time() - $latest_check) >= $tuya_local_interval) {
        $latest_check = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
		if ($cycle_debug) {
			echo 'Запуск проверки статуса ' . date('H:i:s') .  PHP_EOL;
		}	

        foreach ($devices as $device) {
            if ($cycle_debug) {
                debmes(date('H:i:s') . ' Tuya: Get Local Status ' .$device['TITLE'] );
                echo 'Запуск проверки статуса ' . $device['TITLE'].' ' .date('H:i:s') .  PHP_EOL;

            }    

            $command = 'STATUS';

            $local_key = $device['LOCAL_KEY'];
            $dev_id = $device['DEV_ID'];
            $local_ip = $device['DEV_IP'];
			$tuya_ver = $device['TUYA_VER'];

            if (ping($local_ip)) {
                $save_dps[$device['ID']]['attempt'] = 0;

                if (!isset($save_dps[$device['ID']]['online']) or $save_dps[$device['ID']]['online']==0) {
                    if ($cycle_debug) {
                        debmes('Device '.$device['TITLE'].' online');
                    }
                    $save_dps[$device['ID']]['online'] = 1;
                    $tuya_module->processCommand($device['ID'],'online',1);
                }

				$hexByte="0a";
				if ($device['ZIGBEE'] == 0) {
					$json='{"gwId":"'.$dev_id.'","devId":"'.$dev_id.'"}';
				} else {
					//$json = '{"cid":"'.$device['MAC'].'"}';
					$json = '{"cid":"'.$device['UUID'].'"}';
				} 
				
				if ($tuya_ver == '3.4') {
					$dps = '{}';
					$result = $tuya_module->TuyaLocalMsg34('STATUS', $dev_id, $local_key, $local_ip, $dps, $cid);
					$status=json_decode($result);

				} else {
                
					$payload =$tuya_module->TuyaLocalEncrypt($hexByte, $json, $local_key, $tuya_ver);
					
					$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
					socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
					//socket_set_option($socket, SOL_SOCKET, TCP_NODELAY, 1);

					$buf='';
		
					if (socket_connect($socket, $local_ip, 6668)) {
						//echo 'Connect '.  PHP_EOL ;
						if ($device['SEND12']) {
							$payload_12 = $tuya_module->TuyaLocalEncrypt('12', $device['FLAGS12'], $local_key, $tuya_ver);   
							$send = socket_send($socket, $payload_12, strlen($payload_12), 0);

							if ($send != strlen($payload)) {
								debmes('Error sending 12. '.date('y-m-d h:i:s') . ' sended '.$send .' from ' .strlen($payload) . ', ip' . $local_ip );
							}
							
							$reciv = socket_recv ( $socket , $buf , 2048 ,0);
							$result = substr($buf,20,-8);

							if ($tuya_ver != '3.1') { 
								$result = openssl_decrypt($result, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
							}    							
							
							if ($cycle_debug) { 
								debmes('12 answer: '.$result );
							}	
							

						}    
						for ($i=0;$i<1;$i++) {
							$send=socket_send($socket, $payload, strlen($payload), 0);
							if ($send!=strlen($payload)) {
								echo  date('y-m-d h:i:s') . ' sended '.$send .' from ' .strlen($payload) . 'ip' . $local_ip . '<BR>';
							}
							$buf='';
							$reciv=socket_recv ( $socket , $buf , 2048 ,0);
							//echo  date('y-m-d h:i:s') . ' recived '.strlen($buf) .   PHP_EOL;
							if ($buf!='') break;
							sleep(1);
						}

					} else {  
						$err = socket_last_error($socket); 
						debmes(date('y-m-d h:i:s') .' ' .socket_strerror($err) . ' '. $local_ip );
					}
		
					
					$result = substr($buf,20,-8);

					if ($tuya_ver != '3.1') { 
						$result = openssl_decrypt($result, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
					}    
					//echo $result .  PHP_EOL;
		
					$status=json_decode($result);
					if ($cycle_debug) {
						debmes(date('H:i:s') . ' Tuya: Status=' .$result);
					}    
					
					if ($result=='json obj data unvalid') {
						if ($cycle_debug) {
							debmes(date('H:i:s') . ' Tuya: get alt. status');
						}    

						$hexByte="0d";
						
						if (isset($dps_null[$device['DEV_ID']])) {
							$dps = $dps_null[$device['DEV_ID']];   
						} else {    
							$sql = "SELECT TITLE from tucommands WHERE DEVICE_ID='" . $device['ID']. "' AND ceil(TITLE)!=0 ORDER BY CAST(TITLE AS UNSIGNED)";
							$command = SQLSelect($sql);

							$dps='';
							foreach ($command as $d) {
								$dps.= ','.'"'.$d['TITLE'] .'":null';
							}
							$dps = '{'.substr($dps,1).'}';
							$dps_null[$device['DEV_ID']] = $dps;
						}
						
						if ($cycle_debug) {
							debmes('Dps:' . $dps);
						}    


						if ($device['ZIGBEE'] == 0) {
							$json='{"devId":"'.$dev_id.'","uid":"","t":"'.time().'","dps": ' . $dps . '}';
							//$json='{"gwId":"'.$dev_id.'","devId":"'.$dev_id.'", "t": "'.time().'", "dps": ' . $dps . '}';

						} else {
							$json = '{"dps":'.$dps.', "t": "'.time().'","cid":"'.$device['MAC'].'"}';
						}        

						$payload =$tuya_module->TuyaLocalEncrypt($hexByte, $json, $local_key);

						$buf='';
			
						$send=socket_send($socket, $payload, strlen($payload), 0);
						if ($send!=strlen($payload)) {
							echo  date('y-m-d h:i:s') . ' sended '.$send .' from ' .strlen($payload) . 'ip' . $local_ip . '<BR>';
						}
						$buf='';
						$reciv=socket_recv ( $socket , $buf , 2048 ,0);
			
						$result = substr($buf,35,-8);
						$result = openssl_decrypt($result, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
			
						$status=json_decode($result);
						
						if ($cycle_debug) {
							debmes('Result:' . bin2hex($buf));
							debmes('Local key:' . $local_key);
							debmes(date('H:i:s') . ' Tuya: alt. status=' . $result);
						}                 
							
							
					}    
					
					socket_close($socket);
				}	

				if (isset($status->dps)) {
					$dps=$status->dps;
					foreach ($dps as $k=>$d){
						if (is_bool($d)) {
						  $d=($d)?1:0;
						} 
						if (!isset($save_dps[$device['ID']][$k]) or $save_dps[$device['ID']][$k]!=$d) {
							if ($cycle_debug) {
								debmes(date('H:i:s') . ' Tuya: Saved: ' . $k . '=' .$d) ;
							}                 

							$save_dps[$device['ID']][$k] = $d;
							$tuya_module->processCommand($device['ID'],$k,$d);
						 }
					 }
				}
            } else {
                
                if (isset($save_dps[$device['ID']]['attempt'])) {
                    $save_dps[$device['ID']]['attempt'] += 1;
                } else {    
                    $save_dps[$device['ID']]['attempt'] = 1;
                }
                if ((!isset($save_dps[$device['ID']]['online']) or $save_dps[$device['ID']]['online']==1) AND $save_dps[$device['ID']]['attempt']==5) {
                    if ($cycle_debug) {
                        debmes('Device '.$device['TITLE'].' offline');
                    }
                    $save_dps[$device['ID']]['online'] = 0;
                    $tuya_module->processCommand($device['ID'],'online',0);
                }

			}
        } 
      
    }

    if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
        echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME' . basename(__FILE__) . PHP_EOL;
        exit;
    }
  

    sleep(1);
}

echo date('H:i:s') . ' Unexpected close of cycle' . PHP_EOL;

DebMes('Unexpected close of cycle: ' . basename(__FILE__));
