<?php

namespace WebSocket\Application;

/**
 * Websocket-Server demo and test application.
 * 
 * @author Simon Samtleben <web@lemmingzshadow.net>
 */
class MajordomoApplication extends Application
{
    private $_clients = array();
    private $_cachedProperties = array();
    private $_scenesUpdated = 0;
    private $_filename = '';
    private $_scenesDynamicElements = array();

        public function onConnect($client)
    {
                $id = $client->getClientId();
        $this->_clients[$id] = $client;         

    }

    public function onDisconnect($client)
    {
        $id = $client->getClientId();           
        unset($this->_clients[$id]);


    }

    public function onData($data, $client)
    {           
        $decodedData = $this->_decodeData($data);               
                if($decodedData === false)
                {
                        // @todo: invalid request trigger error...
                }
                
                $actionName = '_action' . ucfirst($decodedData['action']);              
                if(method_exists($this, $actionName))
                {                       
                        call_user_func(array($this, $actionName), $decodedData['data'], $client->getClientId());
                }
    }
        
        public function onBinaryData($data, $client)
        {               
                $filePath = substr(__FILE__, 0, strpos(__FILE__, 'server')) . 'tmp/';
                $putfileResult = false;
                if(!empty($this->_filename))
                {
                        $putfileResult = file_put_contents($filePath.$this->_filename, $data);                  
                }               
                if($putfileResult !== false)
                {
                        
                        $msg = 'File received. Saved: ' . $this->_filename;
                }
                else
                {
                        $msg = 'Error receiving file.';
                }
                $client->send($this->_encodeData('echo', $msg));
                $this->_filename = '';
        }

        private function _actionSubscribe($data, $client_id) {
         if ($data['TYPE']) {
          echo date('Y-m-d H:i:s')." Subscription from client to ".$data['TYPE']."\n";
          if ($data['TYPE']=='scenes') {

            $this->refreshSceneDynamicElements();

            if ($data['SCENE_ID']=='') {
             $data['SCENE_ID']='all';
            }
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             echo "Subscribing to scene: ".$data['SCENE_ID']."\n";
            }
            $this->_clients[$client_id]->subscribedTo['scenes'][$data['SCENE_ID']]=1;
            global $scenes;
            $properties=$scenes->getWatchedProperties($this->_clients[$client_id]->subscribedTo['scenes']);
            if (is_array($properties)) {

             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              echo "Watching: ".serialize($properties)."\n";
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
             echo "Subscribing to menu: ".$data['PARENT_ID']."\n";
            }
            $this->_clients[$client_id]->subscribedTo['commands']['PARENT_ID']=$data['PARENT_ID'];
            global $commands;
            $properties=$commands->getWatchedProperties($this->_clients[$client_id]->subscribedTo['commands']['PARENT_ID']);
            if (is_array($properties)) {
             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              echo "Watching: ".serialize($properties)."\n";
             }
             foreach($properties as $v) {
              $this->_clients[$client_id]->watchedProperties[$v['PROPERTY']]['commands'][$v['COMMAND_ID']]=1;
             }
            }
          } elseif ($data['TYPE']=='properties') {
            if ($data['PROPERTIES']=='') {
             return;
            }
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             echo "Subscribing to properties: ".$data['PROPERTIES']."\n";
            }
            $tmp=explode(',', $data['PROPERTIES']);
             if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
              echo "Watching: ".serialize($tmp)."\n";
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
             echo "Subscribing to events: ".$data['EVENTS']."\n";
            }
            $tmp=explode(',', $data['EVENTS']);
            if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
             echo "Watching: ".serialize($tmp)."\n";
            }
            foreach($tmp as $event) {
             $this->_clients[$client_id]->subscribedTo['events'][mb_strtolower($event, 'UTF-8')]=1;
            }
          }
         }
        }

        private function _actionPostEvent($data) {
         if (IsSet($data['NAME'])) {
          $event_name=mb_strtolower($data['NAME'], 'UTF-8');
          if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
           echo "Received event ".$event_name."\n";
          }
          foreach($this->_clients as $client) {
           if (IsSet($client->subscribedTo['events'][$event_name])) {
            $send_data=array();
            $send_data['EVENT_DATA']=$data;
            $encodedData=$this->_encodeData('events', json_encode($send_data));
            $client->send($encodedData);
           }
          }

         }
        }

        private function _actionPostProperty($data) {

         if (IsSet($data['NAME'])) {
          $property_name=mb_strtolower($data['NAME'], 'UTF-8');
          if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
           echo "Update property ".$property_name."\n";
          }
          //$this->_cachedProperties[$property_name]=$data['VALUE'];
          //process property update
          global $scenes;
          global $commands;

          foreach($this->_clients as $client) {
           if (IsSet($client->watchedProperties[$property_name])) {
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
               echo ("Sending updated state ".serialize($send_states)."\n");
              }
              $encodedData = $this->_encodeData('states', json_encode($send_states));
              $client->send($encodedData);
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
              $item=$commands->processMenuItem($k, true, $data['VALUE']);
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
               echo ("Sending updated menu items ".serialize($send_data)."\n");
              }
              $encodedData = $this->_encodeData('commands', json_encode($send_data));
              $client->send($encodedData);
             }
            }
            //properties
            if (isset($client->watchedProperties[$property_name]['properties'])) {
             $send_data=array();
             $send_data[]=array('PROPERTY'=>$property_name, 'VALUE'=>getGlobal($property_name));
             if (isset($send_data[0])) {
              if (defined('DEBUG_WEBSOCKETS') && DEBUG_WEBSOCKETS==1) {
               echo ("Sending updated properties ".serialize($send_data)."\n");
              }
              $encodedData = $this->_encodeData('properties', json_encode($send_data));
              $client->send($encodedData);
             }

            }
           }
          }

         }

         if (file_exists('./reboot'))
         {
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
                $encodedData = $this->_encodeData('echo', $text);               
                foreach($this->_clients as $sendto)
                {
                echo "Echo command: $text\n";
                        $sendto->send($encodedData);
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