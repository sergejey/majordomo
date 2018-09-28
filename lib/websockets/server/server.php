#!/usr/bin/env php
<?php

if (!defined('WEBSOCKETS_PORT')) define('WEBSOCKETS_PORT',8001);

require_once(__DIR__ . '/websockets.php');

class MajordomoWsServer extends WebSocketServer {

    private $_clients = array();
    private $_cachedProperties = array();
    private $_scenesUpdated = 0;
    private $_filename = '';
    private $_latestAlive = 0;
    private $_scenesDynamicElements = array();

    
  function __construct($addr, $port, $bufferLength) {
    parent::__construct($addr, $port, $bufferLength);
    $this->userClass = 'MyUser';
  }
  
  protected function process ($user, $message) {
    $decodedData = $this->_decodeData($message);               
    if($decodedData === false)
    {
         // @todo: invalid request trigger error...
    }
    print_r($decodedData,true);
    $actionName = '_action' . ucfirst($decodedData['action']);              
    if(method_exists($this, $actionName))
    {                       
         call_user_func(array($this, $actionName), $decodedData['data'], $user->getClientId());
    }
    //$this->send($user,$message);
  }
  
  protected function connected ($user) {
     $id = $user->getClientId();
     $this->_clients[$id] = $user;
     echo "Client connected (".$user->getClientIp()."). Total clients: ".count($this->_clients)."\n";
  }
  
  protected function closed ($user) {
     $id = $user->getClientId();           
     unset($this->_clients[$id]);
     echo "Client dicconnected (".$user->getClientIp()."). Total clients: ".count($this->_clients)."\n";
  }
  
  protected function _decodeData($data)
    {
        $decodedData = json_decode($data, true);
        if($decodedData === null)
        {
            return false;
        }
        
        if(isset($decodedData['action'], $decodedData['data']) === false)
        {
            return false;
        }
        
        return $decodedData;
    }
    
    protected function _encodeData($action, $data)
    {
        if(empty($action))
        {
            return false;
        }
        
        $payload = array(
            'action' => $action,
            'data' => $data
        );
        
        return json_encode($payload);
    }
    
    private function cycleAlive() {
         if ((time()-$this->_latestAlive)<3) {
          return;
         }
         $this->_latestAlive=time();
         global $cycleName;
         if ($cycleName) {
          setGlobal($cycleName, time(), 1);
          $ws_clients_total = count($this->_clients);
          $old_value = gg('WSClientsTotal');
             if ($ws_clients_total!=$old_value) {
                 setGlobal('WSClientsTotal', $ws_clients_total, 1);
             }
         }
         global $websockets_script_started;
         if ($websockets_script_started>0 && (time()-$websockets_script_started)>6*60*60) {
          exit; // restart every 6 hours
         }
    }
        
     private function _actionSubscribe($data, $client_id) {
         $this->cycleAlive();
         if ($data['TYPE']) {

             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                 DebMes($this->_clients[$client_id]->getClientIp()." Subscription from client to ".$data['TYPE']."\n".json_encode($data),'websockets');
             }             
             
          if ($data['TYPE']=='scenes') {

            $this->refreshSceneDynamicElements();

            if ($data['SCENE_ID']=='') {
             $data['SCENE_ID']='all';
            }
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             DebMes($this->_clients[$client_id]->getClientIp()." Subscribing to scene: ".$data['SCENE_ID'],'websockets');
            }
            $this->_clients[$client_id]->subscribedTo['scenes'][$data['SCENE_ID']]=1;
            global $scenes;
            $properties=$scenes->getWatchedProperties($this->_clients[$client_id]->subscribedTo['scenes']);
            if (is_array($properties)) {

             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              DebMes($this->_clients[$client_id]->getClientIp()." Watching: ".json_encode($properties),'websockets');
             }
             foreach($properties as $v) {
              $this->_clients[$client_id]->watchedProperties[$v['PROPERTY']]['states'][$v['STATE_ID']]=1;
             }
            }
          } elseif ($data['TYPE']=='commands') {
            if ($data['PARENT_ID']=='') {
             $data['PARENT_ID']='0';
            }
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                DebMes($this->_clients[$client_id]->getClientIp()." Subscribing to menu: ".$data['PARENT_ID'],'websockets');
            }
            $this->_clients[$client_id]->subscribedTo['commands']['PARENT_ID']=$data['PARENT_ID'];
            global $commands;
            $properties=$commands->getWatchedProperties($this->_clients[$client_id]->subscribedTo['commands']['PARENT_ID']);
            if (is_array($properties)) {
             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              DebMes($this->_clients[$client_id]->getClientIp()." Watching:\n".json_encode($properties),'websockets');
             }
             foreach($properties as $v) {
              $this->_clients[$client_id]->watchedProperties[$v['PROPERTY']]['commands'][$v['COMMAND_ID']]=1;
             }
            }
          } elseif ($data['TYPE']=='devices') {
              if ($data['DEVICE_ID']=='') {
                  $data['DEVICE_ID']='0';
              }
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                  DebMes($this->_clients[$client_id]->getClientIp()." Subscribing to device: ".$data['DEVICE_ID'],'websockets');
              }
              $this->_clients[$client_id]->subscribedTo['devices']['DEVICE_ID']=$data['DEVICE_ID'];
              global $devices;
              $properties=$devices->getWatchedProperties($this->_clients[$client_id]->subscribedTo['devices']['DEVICE_ID']);
              if (is_array($properties)) {
                  if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                      DebMes($this->_clients[$client_id]->getClientIp()." Watching:\n".json_encode($properties),'websockets');
                  }
                  foreach($properties as $v) {
                      $this->_clients[$client_id]->watchedProperties[$v['PROPERTY']]['devices'][$v['DEVICE_ID']]=1;
                  }
              }
          } elseif ($data['TYPE']=='objects') {
              if ($data['OBJECT_ID']=='') {
                  $data['OBJECT_ID']='0';
              }
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                  DebMes($this->_clients[$client_id]->getClientIp()." Subscribing to object: ".$data['OBJECT_ID'],'websockets');
              }
              $this->_clients[$client_id]->subscribedTo['objects']['OBJECT_ID']=$data['OBJECT_ID'];
              global $objects_module;
              $properties=$objects_module->getWatchedProperties($this->_clients[$client_id]->subscribedTo['objects']['OBJECT_ID']);
              if (is_array($properties)) {
                  if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                      DebMes($this->_clients[$client_id]->getClientIp()." Watching:\n".json_encode($properties),'websockets');
                  }
                  foreach($properties as $v) {
                      $this->_clients[$client_id]->watchedProperties[$v['PROPERTY']]['objects'][$v['OBJECT_ID']]=1;
                  }
              }
          } elseif ($data['TYPE']=='properties') {
            if ($data['PROPERTIES']=='') {
             return;
            }
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             DebMes($this->_clients[$client_id]->getClientIp()." Subscribing to properties: ".$data['PROPERTIES'],'websockets');
            }
            $tmp=explode(',', $data['PROPERTIES']);
             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              DebMes($this->_clients[$client_id]->getClientIp()." Watching:\n".json_encode($tmp),'websockets');
             }
            foreach($tmp as $property) {
             $this->_clients[$client_id]->subscribedTo['properties'][mb_strtolower($property, 'UTF-8')]=1;
             $this->_clients[$client_id]->watchedProperties[mb_strtolower($property, 'UTF-8')]['properties']=1;
            }
          } elseif ($data['TYPE']=='events') {
            if ($data['EVENTS']=='') {
             return;
            }
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              DebMes($this->_clients[$client_id]->getClientIp()." Subscribing to events: ".$data['EVENTS'],'websockets');
            }
            $tmp=explode(',', $data['EVENTS']);
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             DebMes($this->_clients[$client_id]->getClientIp()." Watching:\n".json_encode($tmp),'websockets');
            }
            foreach($tmp as $event) {
             $this->_clients[$client_id]->subscribedTo['events'][mb_strtolower($event, 'UTF-8')]=1;
            }
          }
          $send_data=array();
          $encodedData = $this->_encodeData('subscribed', json_encode($send_data));
          $this->send($this->_clients[$client_id],$encodedData);
         }
        }

        private function _actionPostEvent($data) {
         $this->cycleAlive();
         if (IsSet($data['NAME'])) {
          $event_name=mb_strtolower($data['NAME'], 'UTF-8');
          if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
           DebMes("Received event ".$event_name,'websockets');
          }
          foreach($this->_clients as $client) {
           if (IsSet($client->subscribedTo['events'][$event_name])) {
            $send_data=array();
            $send_data['EVENT_DATA']=$data;
            $encodedData=$this->_encodeData('events', json_encode($send_data));
            $this->send($client,$encodedData);
           }
          }

         }
        }

        private function _actionPostProperty($data) {
         $this->cycleAlive();

         $received_properties=array();
         $received_values=array();

         if (IsSet($data['NAME'])) {
             $received_properties[]=$data['NAME'];
             $received_values[]=$data['VALUE'];
         } elseif (is_array($data[0]) && isset($data[0]['NAME'])) {
             $total = count($data);
             for ($i = 0; $i < $total; $i++) {
                 $received_properties[]=$data[$i]['NAME'];
                 $received_values[$data[$i]['NAME']]=$data[$i]['VALUE'];
             }
         }

            global $scenes;
            global $commands;
            global $devices;
            global $objects_module;


         foreach($received_properties as $property_name) {
             $property_value=$received_values[$property_name];
             
          if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             //DebMes("Update property ".$property_name,'websockets');
          }

          //process property update

          $found_subscribers=0;

          foreach($this->_clients as $client) {
           $tmp=explode('.', $property_name);
           if (IsSet($client->watchedProperties[$property_name]) || IsSet($client->watchedProperties[$tmp[0]])) {
            //scenes
            if (isset($client->watchedProperties[$property_name]['states'])) {
             $send_states=array();
             $seen_state=array();
             foreach($client->watchedProperties[$property_name]['states'] as $k=>$v) {
              if (isset($seen_state[$k])) {
               continue;
              }
              $seen_state[$k]=1;
              $state=$this->_scenesDynamicElements[$k];
              $scenes->processState($state);
              $send_states[]=$state;
             }

             if (isset($send_states[0])) {
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
               DebMes($client->getClientIp()." Sending updated state\n".json_encode($send_states),'websockets');
              }
              $encodedData = $this->_encodeData('states', json_encode($send_states));
              $this->send($client,$encodedData);
             }
            }
            //commands (menu)
            if (isset($client->watchedProperties[$property_name]['commands'])) {
             $send_values=array();
             $send_labels=array();
             $seen_commands=array();
             foreach($client->watchedProperties[$property_name]['commands'] as $k=>$v) {
              if (isset($seen_commands[$k])) {
               continue;
              }
              $seen_commands[$k]=1;
              $item=$commands->processMenuItem($k); //, true, $property_value
              if (isset($item['VALUE'])) {
               $send_values[]=array('ID'=>$item['ID'], 'DATA'=>$item['VALUE']);
              }
              if (isset($item['LABEL'])) {
               $send_labels[]=array('ID'=>$item['ID'], 'DATA'=>$item['LABEL']);
              }
             }

             if (isset($send_labels[0])) {
              $send_data=array('LABELS'=>$send_labels, 'VALUES'=>$send_values);
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                  DebMes($client->getClientIp()." Sending updated menu items\n".json_encode($send_data),'websockets');
              }
              $encodedData = $this->_encodeData('commands', json_encode($send_data));
              $this->send($client,$encodedData);
             }
            }
               //devices
               if (isset($client->watchedProperties[$property_name]['devices'])) {
                   $send_values=array();
                   $seen_devices=array();
                   foreach($client->watchedProperties[$property_name]['devices'] as $k=>$v) {
                       if (isset($seen_devices[$k])) {
                           continue;
                       }
                       $seen_devices[$k]=1;
                       $item=$devices->processDevice($k);
                       if (isset($item['HTML'])) {
                           $send_values[]=array('DEVICE_ID'=>$item['DEVICE_ID'], 'DATA'=>$item['HTML']);
                       }
                   }

                   if (isset($send_values[0])) {
                       $send_data=array('DATA'=>$send_values);
                       if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                           DebMes($client->getClientIp()." Sending updated device items\n".json_encode($send_data),'websockets');
                       }
                       $encodedData = $this->_encodeData('devices', json_encode($send_data));
                       $this->send($client,$encodedData);
                   }
               }

               //objects
               if (isset($client->watchedProperties[$property_name]['objects'])) {
                   $send_values=array();
                   $seen_objects=array();
                   foreach($client->watchedProperties[$property_name]['objects'] as $k=>$v) {
                       if (isset($seen_objects[$k])) {
                           continue;
                       }
                       $seen_objects[$k]=1;
                       $item=$objects_module->processObject($k);
                       if (isset($item['HTML'])) {
                           $send_values[]=array('OBJECT_ID'=>$item['OBJECT_ID'], 'DATA'=>$item['HTML']);
                       }
                   }

                   if (isset($send_values[0])) {
                       $send_data=array('DATA'=>$send_values);
                       if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                           DebMes($client->getClientIp()." Sending updated object items\n".json_encode($send_data),'websockets');
                       }
                       $encodedData = $this->_encodeData('objects', json_encode($send_data));
                       $this->send($client,$encodedData);
                   }
               }

            //properties
            if (isset($client->watchedProperties[$property_name]['properties'])) {
             $send_data=array();
             $send_data[]=array('PROPERTY'=>$property_name, 'VALUE'=>getGlobal($property_name));
             if (isset($send_data[0])) {
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                  DebMes($client->getClientIp()." Sending updated properties\n".json_encode($send_data),'websockets');
                  DebMes($client->getClientIp()." Sending updated properties\n".json_encode($send_data),'websockets');
              }
              $encodedData = $this->_encodeData('properties', json_encode($send_data));
              $this->send($client,$encodedData);
             }

            }
            //object properties
            $tmp=explode('.', $property_name);
            if (isset($client->watchedProperties[$tmp[0]]['properties'])) {
             $send_data=array();
             $send_data[]=array('PROPERTY'=>$property_name, 'VALUE'=>getGlobal($property_name));
             if (isset($send_data[0])) {
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
                  DebMes($client->getClientIp()." Sending updated properties\n".json_encode($send_data),'websockets');
                  DebMes($client->getClientIp()." Sending updated properties\n".json_encode($send_data),'websockets');
              }
              $encodedData = $this->_encodeData('properties', json_encode($send_data));
              $this->send($client,$encodedData);
             }
            }
           }
          }

          if (!$found_subscribers) {
           if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
            //DebMes("No subscribers for ".$property_name,'websockets');
           }
          }

         }

         if (file_exists('./reboot'))
         {
             global $db;
             $db->Disconnect();
             exit;
         }


        }


        private function refreshSceneDynamicElements() {
         global $scenes;

         if (time()==$this->_scenesUpdated) {
          return;
         }

         $this->_scenesUpdated=time();

         unset($this->_scenesDynamicElements);
         $this->_scenesDynamicElements=array();
         $elements=$scenes->getDynamicElements();
         $total=count($elements);
         for($i=0;$i<$total;$i++) {
          if (is_array($elements[$i]['STATES'])) {
           foreach($elements[$i]['STATES'] as $st) {
            $states[]=$st;
            $this->_scenesDynamicElements[$st['ID']]=$st;
           }
          }
         }

        }

        
        private function _actionEcho($text, $client_id)
        {               
                $this->cycleAlive();
                $encodedData = $this->_encodeData('echo', $text);               
                foreach($this->_clients as $sendto)
                {
                    echo date('Y-m-d H:i:s')." Echo command: $text\n";
                    $this->send($sendto,$encodedData);
                }
        }
        
        private function _actionSetFilename($filename)
        {               
                if(strpos($filename, '\\') !== false)
                {
                        $filename = substr($filename, strrpos($filename, '\\')+1);
                }
                elseif(strpos($filename, '/') !== false)
                {
                        $filename = substr($filename, strrpos($filename, '/')+1);
                }               
                if(!empty($filename)) 
                {
                        $this->_filename = $filename;
                        return true;
                }
                return false;
        }


}

$echo = new MajordomoWsServer("0.0.0.0",WEBSOCKETS_PORT,1048576);

try {
  $echo->run();
}
catch (Exception $e) {
  $echo->stdout($e->getMessage());
}
