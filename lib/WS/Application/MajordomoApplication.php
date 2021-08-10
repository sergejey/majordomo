<?php

declare(strict_types=1);

namespace Bloatless\WebSocket\Application;

use Bloatless\WebSocket\Connection;

class MajordomoApplication extends Application
{
    /**
     * @var array $clients
     */
    private $clients = [];
	private $received_properties = [];
    private $received_values = [];


    /**
     * Handles new connections to the application.
     *
     * @param Connection $client
     * @return void
     */
    public function onConnect(Connection $client): void
    {
        $id = $client->getClientId();
        $this->clients[$id] = $client;
    }

    /**
     * Handles client disconnects.
     *
     * @param Connection $client
     * @return void
     */
    public function onDisconnect(Connection $client): void
    {
        $id = $client->getClientId();
        unset($this->clients[$id]);
    }

    /**
     * Handles incomming data/requests.
     * If valid action is given the according method will be called.
     *
     * @param string $data
     * @param Connection $client
     * @return void
     */
    public function onData(string $data, Connection $client): void
    {
        try {
            $decodedData = $this->decodeData($data);
            $actionName = 'action' . ucfirst($decodedData['action']);
            if (method_exists($this, $actionName)) {
                call_user_func([$this, $actionName], $decodedData['data'], $client->getClientId());
            }
        } catch (\RuntimeException $e) {
            // @todo Handle/Log error
        }
    }
    
    /**
     * Echoes data back to client(s).
     *
     * @param string $text
     * @return void
     */
    private function actionEcho(string $text): void
    {
        $encodedData = $this->encodeData('echo', $text);
        foreach ($this->clients as $sendto) {
            $sendto->send($encodedData);
        }
    }
	
	private function refreshSceneDynamicElements()
    {
        global $scenes;

        if (time() == $this->_scenesUpdated) {
            return;
        }

        $this->_scenesUpdated = time();

        unset($this->_scenesDynamicElements);
        $this->_scenesDynamicElements = array();
        $elements = $scenes->getDynamicElements();
        $total = count($elements);
        for ($i = 0; $i < $total; $i++) {
            if (is_array($elements[$i]['STATES'])) {
                foreach ($elements[$i]['STATES'] as $st) {
                    $states[] = $st;
                    $this->_scenesDynamicElements[$st['ID']] = $st;
                }
            }
        }

    }
	
	    private function actionSubscribe($data, $client_id)
    {
        if ($data['TYPE']) {

            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                DebMes($this->clients[$client_id]->getClientIp() . " Subscription from client to " . $data['TYPE'] . "\n" . json_encode($data), 'websockets');
            }

            if ($data['TYPE'] == 'scenes') {

                $this->refreshSceneDynamicElements();

                if ($data['SCENE_ID'] == '') {
                    $data['SCENE_ID'] = 'all';
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to scene: " . $data['SCENE_ID'], 'websockets');
                }
                $this->clients[$client_id]->subscribedTo['scenes'][$data['SCENE_ID']] = 1;
                global $scenes;
                $properties = $scenes->getWatchedProperties($this->clients[$client_id]->subscribedTo['scenes']);
                if (is_array($properties)) {

                    if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                        DebMes($this->clients[$client_id]->getClientIp() . " Watching: " . json_encode($properties), 'websockets');
                    }
                    foreach ($properties as $v) {
                        $this->clients[$client_id]->watchedProperties[$v['PROPERTY']]['states'][$v['STATE_ID']] = 1;
                    }
                }
            }

            if ($data['TYPE'] == 'plans') {
                //$this->refreshSceneDynamicElements();
                if ($data['PLAN_ID'] == '') {
                    $data['PLAN_ID'] = 'all';
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to plan: " . $data['PLAN_ID'], 'websockets');
                }
                $this->clients[$client_id]->subscribedTo['plans'][$data['PLAN_ID']] = 1;
                global $plans;
                $properties = $plans->getWatchedProperties($this->clients[$client_id]->subscribedTo['plans']);
                if (is_array($properties)) {
                    if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                        DebMes($this->clients[$client_id]->getClientIp() . " Watching: " . json_encode($properties), 'websockets');
                    }
                    foreach ($properties as $v) {
                        $this->clients[$client_id]->watchedProperties[$v['PROPERTY']]['plan_states'][$v['STATE_ID']] = $v;
                    }
                }
            }

            if ($data['TYPE'] == 'commands') {
                if ($data['PARENT_ID'] == '') {
                    $data['PARENT_ID'] = '0';
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to menu: " . $data['PARENT_ID'], 'websockets');
                }
                $this->clients[$client_id]->subscribedTo['commands']['PARENT_ID'] = $data['PARENT_ID'];
                global $commands;
                $properties = $commands->getWatchedProperties($this->clients[$client_id]->subscribedTo['commands']['PARENT_ID']);
                if (is_array($properties)) {
                    if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                        DebMes($this->clients[$client_id]->getClientIp() . " Watching:\n" . json_encode($properties), 'websockets');
                    }
                    foreach ($properties as $v) {
                        $this->clients[$client_id]->watchedProperties[$v['PROPERTY']]['commands'][$v['COMMAND_ID']] = 1;
                    }
                }
            }

            if ($data['TYPE'] == 'devices') {
                if ($data['DEVICE_ID'] == '') {
                    $data['DEVICE_ID'] = '0';
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to device: " . $data['DEVICE_ID'], 'websockets');
                }
                $this->clients[$client_id]->subscribedTo['devices']['DEVICE_ID'] = $data['DEVICE_ID'];
                global $devices;
                $properties = $devices->getWatchedProperties($this->clients[$client_id]->subscribedTo['devices']['DEVICE_ID']);
                if (is_array($properties)) {
                    if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                        DebMes($this->clients[$client_id]->getClientIp() . " Watching:\n" . json_encode($properties), 'websockets');
                    }
                    foreach ($properties as $v) {
                        $this->clients[$client_id]->watchedProperties[$v['PROPERTY']]['devices'][$v['DEVICE_ID']] = 1;
                    }
                }
            }

            if ($data['TYPE'] == 'devices_data') {
                if ($data['DEVICE_ID'] == '') {
                    $data['DEVICE_ID'] = '0';
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to device data: " . $data['DEVICE_ID'], 'websockets');
                }
                $this->clients[$client_id]->subscribedTo['devices_data']['DEVICE_ID'] = $data['DEVICE_ID'];
                global $devices;
                $properties = $devices->getWatchedProperties($this->clients[$client_id]->subscribedTo['devices_data']['DEVICE_ID']);
                if (is_array($properties)) {
                    if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                        DebMes($this->clients[$client_id]->getClientIp() . " Watching:\n" . json_encode($properties), 'websockets');
                    }
                    foreach ($properties as $v) {
                        $this->clients[$client_id]->watchedProperties[$v['PROPERTY']]['devices_data'][$v['DEVICE_ID']] = 1;
                    }
                }
            }

            if ($data['TYPE'] == 'objects') {
                if ($data['OBJECT_ID'] == '') {
                    $data['OBJECT_ID'] = '0';
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to object: " . $data['OBJECT_ID'], 'websockets');
                }
                $this->clients[$client_id]->subscribedTo['objects']['OBJECT_ID'] = $data['OBJECT_ID'];
                global $objects_module;
                $properties = $objects_module->getWatchedProperties($this->clients[$client_id]->subscribedTo['objects']['OBJECT_ID']);
                if (is_array($properties)) {
                    if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                        DebMes($this->clients[$client_id]->getClientIp() . " Watching:\n" . json_encode($properties), 'websockets');
                    }
                    foreach ($properties as $v) {
                        $this->clients[$client_id]->watchedProperties[$v['PROPERTY']]['objects'][$v['OBJECT_ID']] = 1;
                    }
                }
            }

            if ($data['TYPE'] == 'properties') {
                if ($data['PROPERTIES'] == '') {
                    return;
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to properties: " . $data['PROPERTIES'], 'websockets');
                }
                $tmp = explode(',', $data['PROPERTIES']);
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Watching:\n" . json_encode($tmp), 'websockets');
                }
                foreach ($tmp as $property) {
                    $this->clients[$client_id]->subscribedTo['properties'][mb_strtolower($property, 'UTF-8')] = 1;
                    $this->clients[$client_id]->watchedProperties[mb_strtolower($property, 'UTF-8')]['properties'] = 1;
                }
            }

            if ($data['TYPE'] == 'events') {
                if ($data['EVENTS'] == '') {
                    return;
                }
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Subscribing to events: " . $data['EVENTS'], 'websockets');
                }
                $tmp = explode(',', $data['EVENTS']);
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    DebMes($this->clients[$client_id]->getClientIp() . " Watching:\n" . json_encode($tmp), 'websockets');
                }
                foreach ($tmp as $event) {
                    $this->clients[$client_id]->subscribedTo['events'][mb_strtolower($event, 'UTF-8')] = 1;
                }
            }

            $send_data = array();
            $encodedData = $this->encodeData('subscribed', json_encode($send_data));
            $this->clients[$client_id]->send($encodedData);
        }
    }

    private function actionPostEvent($data)
    {
        if (IsSet($data['NAME'])) {
            $event_name = mb_strtolower($data['NAME'], 'UTF-8');
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                DebMes("Received event " . $event_name, 'websockets');
            }
            foreach ($this->clients as $client) {
                if (IsSet($client->subscribedTo['events'][$event_name])) {
                    $send_data = array();
                    $send_data['EVENT_DATA'] = $data;
                    $encodedData = $this->encodeData('events', json_encode($send_data));
                    $client->send($encodedData);
                }
            }

        }
    }
	
    private function actionPostProperty($data)
    {
        if (IsSet($data['NAME'])) {
            $received_properties[] = $data['NAME'];
            $received_values[] = $data['VALUE'];
        } elseif (is_array($data[0]) && isset($data[0]['NAME'])) {
            $total = count($data);
            for ($i = 0; $i < $total; $i++) {
                $received_properties[] = $data[$i]['NAME'];
                $received_values[$data[$i]['NAME']] = $data[$i]['VALUE'];
            }
        }
		global $scenes;
		global $plans;
		global $commands;
		global $devices;
		global $objects_module;
	
        foreach ($received_properties as $property_name) {
            $property_name_lc = mb_strtolower($property_name,'UTF-8');
            $property_value = $received_values[$property_name];
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                DebMes("Update property ".$property_name,'websockets');
            }
            //process property update
            $found_subscribers = 0;

            foreach ($this->clients as $client) {
                $tmp = explode('.', $property_name_lc);
                if (IsSet($client->watchedProperties[$property_name_lc]) || IsSet($client->watchedProperties[$tmp[0]])) {
                    //scenes
                    if (isset($client->watchedProperties[$property_name_lc]['states'])) {
                        $send_states = array();
                        $seen_state = array();
                        foreach ($client->watchedProperties[$property_name_lc]['states'] as $k => $v) {
                            if (isset($seen_state[$k])) {
                                continue;
                            }
                            $seen_state[$k] = 1;
                            $state = $this->_scenesDynamicElements[$k];
                            $scenes->processState($state);
                            $send_states[] = $state;
                        }

                        if (isset($send_states[0])) {
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated state\n" . json_encode($send_states), 'websockets');
                            }
                            $encodedData = $this->encodeData('states', json_encode($send_states));
                            $client->send($encodedData);
                        }
                    }

                    //plans
                    if (isset($client->watchedProperties[$property_name_lc]['plan_states'])) {
                        $send_plan_states = array();
                        $seen_plan_state = array();
                        foreach ($client->watchedProperties[$property_name_lc]['plan_states'] as $k => $v) {
                            if (isset($seen_plan_state[$k])) {
                                continue;
                            }
                            $seen_plan_state[$k] = 1;
                            //$state = $this->_scenesDynamicElements[$k];
                            $state=array('ID'=>$k,'PROPERTY_NAME'=>$property_name,'PROPERTY_VALUE'=>$property_value);
                            if ($v['TEMPLATE']!='') {
                                $state['TEMPLATE']=$v['TEMPLATE'];
                                $state['ITEM']=$k;
                            }
                            if (preg_match('/^component(\d+)$/',$k,$m)) {
                                $state['COMPONENT_ID']=$m[1];
                                $state['ITEM']=$k;
                            }
                            $plans->processState($state);
                            if ($state['TEMPLATE']) {
                                unset($state['TEMPLATE']);
                            }
                            $send_plan_states[] = $state;
                        }

                        if (isset($send_plan_states[0])) {
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated state\n" . json_encode($send_plan_states), 'websockets');
                            }
                            $encodedData = $this->encodeData('plan_states', json_encode($send_plan_states));
                            $client->send($encodedData);
                        }
                    }

                    //commands (menu)
                    if (isset($client->watchedProperties[$property_name_lc]['commands'])) {
                        $send_values = array();
                        $send_labels = array();
                        $seen_commands = array();
                        foreach ($client->watchedProperties[$property_name_lc]['commands'] as $k => $v) {
                            if (isset($seen_commands[$k])) {
                                continue;
                            }
                            $seen_commands[$k] = 1;
                            $item = $commands->processMenuItem($k); //, true, $property_value
                            if (isset($item['VALUE'])) {
                                $send_values[] = array('ID' => $item['ID'], 'DATA' => $item['VALUE']);
                            }
                            if (isset($item['LABEL'])) {
                                $send_labels[] = array('ID' => $item['ID'], 'DATA' => $item['LABEL']);
                            }
                        }

                        if (isset($send_labels[0])) {
                            $send_data = array('LABELS' => $send_labels, 'VALUES' => $send_values);
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated menu items\n" . json_encode($send_data), 'websockets');
                            }
                            $encodedData = $this->encodeData('commands', json_encode($send_data));
                            $client->send($encodedData);
                        }
                    }

                    //devices
                    if (isset($client->watchedProperties[$property_name_lc]['devices'])) {
                        $send_values = array();
                        $seen_devices = array();
                        foreach ($client->watchedProperties[$property_name_lc]['devices'] as $k => $v) {
                            if (isset($seen_devices[$k])) {
                                continue;
                            }
                            $seen_devices[$k] = 1;
                            $item = $devices->processDevice($k);
                            if (isset($item['HTML'])) {
                                $send_values[] = array('DEVICE_ID' => $item['DEVICE_ID'], 'DATA' => $item['HTML']);
                            }
                        }

                        if (isset($send_values[0])) {
                            $send_data = array('DATA' => $send_values);
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated device items\n" . json_encode($send_data), 'websockets');
                            }
                            $encodedData = $this->encodeData('devices', json_encode($send_data));
                            $client->send($encodedData);
                        }
                    }

                    //devices data
                    if (isset($client->watchedProperties[$property_name_lc]['devices_data'])) {
                        $send_values=array();
                        $seen_devices=array();
                        foreach($client->watchedProperties[$property_name_lc]['devices_data'] as $k=>$v) {
                            if (isset($seen_devices[$k])) {
                                continue;
                            }
                            $seen_devices[$k]=1;
                            if ($k>0) {
                                $devices_=SQLSelect("SELECT * FROM devices WHERE ID=".$k);
                                $total = count($devices_);
                                $cached_properties=array();
                                for ($i = 0; $i < $total; $i++) {
                                    $device=array();
                                    $device['id']=$devices_[$i]['ID'];
                                    $device['title']=$devices_[$i]['TITLE'];
                                    $device['object']=$devices_[$i]['LINKED_OBJECT'];
                                    $device['type']=$devices_[$i]['TYPE'];
                                    $device['favorite']=$devices_[$i]['FAVORITE'];
                                    $obj = getObject($device['object']);
                                    if (!isset($cached_properties[$obj->class_id])) {
                                        $cached_properties[$obj->class_id]=getClassProperties($obj->class_id);
                                    }
                                    $properties = $cached_properties[$obj->class_id];
                                    foreach($properties as $p) {
                                        $device[$p['TITLE']]=getGlobal($device['object'].'.'.$p['TITLE']);
                                    }
                                    $send_values[]=$device;
                                }
                            }
                            //  $item=$devices->processDevice($k);
                            if (isset($item['HTML'])) {
                                //  $send_values[]=array('DEVICE_ID'=>$item['DEVICE_ID'], 'DATA'=>$item);
                            }
                        }
                        if (isset($send_values[0])) {
                            $send_data=array('DATA'=>$send_values);
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                                DebMes($client->getClientIp()." Sending updated device data items\n".json_encode($send_data),'websockets');
                            }
                            $encodedData = $this->encodeData('devices_data', json_encode($send_data));
                            $client->send($encodedData);
                        }
                    }

                    //objects
                    if (isset($client->watchedProperties[$property_name_lc]['objects'])) {
                        $send_values = array();
                        $seen_objects = array();
                        foreach ($client->watchedProperties[$property_name_lc]['objects'] as $k => $v) {
                            if (isset($seen_objects[$k])) {
                                continue;
                            }
                            $seen_objects[$k] = 1;
                            $item = $objects_module->processObject($k);
                            if (isset($item['HTML'])) {
                                $send_values[] = array('OBJECT_ID' => $item['OBJECT_ID'], 'DATA' => $item['HTML']);
                            }
                        }

                        if (isset($send_values[0])) {
                            $send_data = array('DATA' => $send_values);
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated object items\n" . json_encode($send_data), 'websockets');
                            }
                            $encodedData = $this->encodeData('objects', json_encode($send_data));
                            $client->send($encodedData);
                        }
                    }

                    //properties
                    if (isset($client->watchedProperties[$property_name_lc]['properties'])) {
                        $send_data = array();
                        $send_data[] = array('PROPERTY' => $property_name, 'VALUE' => getGlobal($property_name));
                        if (isset($send_data[0])) {
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated properties\n" . json_encode($send_data), 'websockets');
                            }
                            $encodedData = $this->encodeData('properties', json_encode($send_data));
                            $client->send($encodedData);
                        }

                    }

                    //object properties
                    $tmp = explode('.', $property_name_lc);
                    if (isset($client->watchedProperties[$tmp[0]]['properties'])) {
                        $send_data = array();
                        $send_data[] = array('PROPERTY' => $property_name, 'VALUE' => getGlobal($property_name));
                        if (isset($send_data[0])) {
                            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                                DebMes($client->getClientIp() . " Sending updated properties\n" . json_encode($send_data), 'websockets');
                                DebMes($client->getClientIp() . " Sending updated properties\n" . json_encode($send_data), 'websockets');
                            }
                            $encodedData = $this->encodeData('properties', json_encode($send_data));
                            $client->send($encodedData);
                        }
                    }
                }
            }

            if (!$found_subscribers) {
                if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS == 1) {
                    //DebMes("No subscribers for ".$property_name,'websockets');
                }
            }

        }
    }
}
