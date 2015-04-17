<?php
/**
* Z-Wave 
*
* Zwave
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 17:05:52 [May 30, 2013])
*/
//
//
class zwave extends module {
/**
* zwave
*
* Module class constructor
*
* @access private
*/
function zwave() {
  $this->name="zwave";
  $this->title="<#LANG_MODULE_ZWAVE#>";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if (IsSet($this->location_id)) {
   $out['IS_SET_LOCATION_ID']=1;
  }
  if (IsSet($this->device_id)) {
   $out['IS_SET_DEVICE_ID']=1;
  }
  if (IsSet($this->uniq_id)) {
   $out['IS_SET_UNIQ_ID']=1;
  }
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }

 $this->getConfig();
 $out['ZWAVE_API_URL']=$this->config['ZWAVE_API_URL'];

 if (!$out['ZWAVE_API_URL']) {
  $out['ZWAVE_API_URL']='http://';
 }

 $out['ZWAVE_API_USERNAME']=$this->config['ZWAVE_API_USERNAME'];
 $out['ZWAVE_API_PASSWORD']=$this->config['ZWAVE_API_PASSWORD'];
 $out['ZWAVE_API_AUTH']=$this->config['ZWAVE_API_AUTH'];
 $out['ZWAVE_API_RESET']=(int)$this->config['ZWAVE_API_RESET'];
 if ($this->view_mode=='update_settings') {
   global $zwave_api_url;
   global $zwave_api_username;
   global $zwave_api_password;
   global $zwave_api_auth;
   global $zwave_api_reset;

   if (!preg_match('/\/$/', $zwave_api_url)) {
    $zwave_api_url=$zwave_api_url.'/';
   }
   $this->config['ZWAVE_API_URL']=$zwave_api_url;
   $this->config['ZWAVE_API_USERNAME']=$zwave_api_username;
   $this->config['ZWAVE_API_PASSWORD']=$zwave_api_password;
   $this->config['ZWAVE_API_AUTH']=(int)$zwave_api_auth;
   $this->config['ZWAVE_API_RESET']=(int)$zwave_api_reset;
   $this->saveConfig();
   $this->redirect("?");
 }

 $out['API_STATUS']=$this->connect();

 if ($this->view_mode=='rescan') {
  $this->scanNetwork();
  $this->redirect("?");
 }

 if ($this->data_source=='zwave_devices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_zwave_devices') {
   $this->search_zwave_devices($out);
  }
  if ($this->view_mode=='edit_zwave_devices') {
   $this->edit_zwave_devices($out, $this->id);
  }
  if ($this->view_mode=='delete_zwave_devices') {
   $this->delete_zwave_devices($this->id);
   $this->redirect("?data_source=zwave_devices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='zwave_properties') {
  if ($this->view_mode=='' || $this->view_mode=='search_zwave_properties') {
   $this->search_zwave_properties($out);
  }
 }
}

/**
* Title
*
* Description
*
* @access public
*/
 function connect() {
  return include_once(DIR_MODULES.$this->name.'/zwave_connect.inc.php');  
 }


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function scanNetwork() {
   $data=$this->apiCall('/ZWaveAPI/Data/0');
   $devices=array();
   for($i=1;$i<500;$i++) {
    if (isset($data->devices->{$i})) {
     $device=$data->devices->{$i};
     $device->id=$i;
     $device->type=$device->data->deviceTypeString->value;

     /*
     if ($device->type=='Static PC Controller') {
      continue;
     }
     */

     //echo $device->data->NodeId->value."<hr>";

     //var_dump($device->instances->{'0'}->commandClasses);
     //echo "<hr>";
     if ($device->instances->{'0'}->commandClasses->{'119'}->data->nodename->value!='') {
      $device->title=$device->type.' '.$i.' ('.$device->instances->{'0'}->commandClasses->{'119'}->data->nodename->value.')';
     } else {
      $device->title=$device->type.' '.$i;
     }


     if (isset($device->instances->{'1'})) {
      $device->multi_instance=1;
     }

     for($k=0;$k<50;$k++) {
      if (isset($device->instances->{"$k"})) {
       $instance = (object) array_merge((array) $device, (array) $device->instances->{"$k"});
       $instance->instance=$k;
       if ($device->multi_instance) {
        $instance->title.=' (inst.: '.$k.')';
       }
       $devices[]=$instance;
      } else {
       break;
      }
     }

     
    } else {
     continue;
    }
   }

   $seen_devices=array();
   $total=count($devices);
   //echo "Total: ".$total."<br>";
   for($i=0;$i<$total;$i++) {
    //echo $i.' '.$devices[$i]->title."<br>";
    $rec=SQLSelectOne("SELECT * FROM zwave_devices WHERE NODE_ID='".$devices[$i]->id."' AND INSTANCE_ID='".$devices[$i]->instance."'");
    $rec['NODE_ID']=$devices[$i]->id;
    $rec['INSTANCE_ID']=$devices[$i]->instance;
    if (!$rec['TITLE']) {
     $rec['TITLE']=$devices[$i]->title;
    }

    $all_classes=array();
    for($k=0;$k<255;$k++) {
     if (isset($devices[$i]->commandClasses->{"$k"}->data)) {
      $all_classes[]=$k;
     }     
    }
    if ($all_classes[0]) {
     $rec['ALL_CLASSES']='|'.implode('|', $all_classes).'|';
    } else {
     $rec['ALL_CLASSES']='';
    }
    

    if (isset($devices[$i]->commandClasses->{'32'}->data)) {
     $rec['CLASS_BASIC']=1;
    } else {
     $rec['CLASS_BASIC']=0;
    }
    if (isset($devices[$i]->commandClasses->{'48'}->data)) {
     $rec['CLASS_SENSOR_BINARY']=1;
    } else {
     $rec['CLASS_SENSOR_BINARY']=0;
    }
    if (isset($devices[$i]->commandClasses->{'49'}->data)) {
     $rec['CLASS_SENSOR_MULTILEVEL']=1;
    } else {
     $rec['CLASS_SENSOR_MULTILEVEL']=0;
    }
    if (isset($devices[$i]->commandClasses->{'64'}->data)) {
     $rec['CLASS_THERMOSTAT']=1;
    } else {
     $rec['CLASS_THERMOSTAT']=0;
    }
    if (isset($devices[$i]->commandClasses->{'37'}->data)) {
     $rec['CLASS_SWITCH_BINARY']=1;
    } else {
     $rec['CLASS_SWITCH_BINARY']=0;
    }
    if (isset($devices[$i]->commandClasses->{'38'}->data)) {
     $rec['CLASS_SWITCH_MULTILEVEL']=1;
    } else {
     $rec['CLASS_SWITCH_MULTILEVEL']=0;
    }
    if (isset($devices[$i]->commandClasses->{'50'}->data)) {
     $rec['CLASS_METER']=1;
    } else {
     $rec['CLASS_METER']=0;
    }
    if (isset($devices[$i]->commandClasses->{'112'}->data)) {
     $rec['CLASS_CONFIG']=1;
    } else {
     $rec['CLASS_CONFIG']=0;
    }
    if (isset($devices[$i]->commandClasses->{'128'}->data)) {
     $rec['CLASS_BATTERY']=1;
    } else {
     $rec['CLASS_BATTERY']=0;
    }
    if (isset($devices[$i]->commandClasses->{'156'}->data)) {
     $rec['CLASS_SENSOR_ALARM']=1;
    } else {
     $rec['CLASS_SENSOR_ALARM']=0;
    }
    if (is_object($devices[$i]->commandClasses->{'45'}) || is_object($devices[$i]->commandClasses->{'43'})) {
     $rec['CLASS_SCENE_CONTROLLER']=1;
    } else {
     $rec['CLASS_SCENE_CONTROLLER']=0;
    }
    if (!$rec['ID']) {
     $rec['ID']=SQLInsert('zwave_devices', $rec);
    } else {
     SQLUpdate('zwave_devices', $rec);
    }
    $seen_devices[]=$rec['ID'];
    $this->pollDevice($rec['ID'], $devices[$i]);

   }

   global $remove_not_found;
   if (count($seen_devices)>0 && $remove_not_found) {
    $devices=SQLSelect("SELECT ID FROM zwave_devices WHERE ID NOT IN (".implode(',', $seen_devices).")");
    $total=count($devices);
    for($i=0;$i<$total;$i++) {
     $this->delete_zwave_devices($devices[$i]['ID']);
    }
   }

   return $data->updateTime;

  }


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function setProperty($property_id, $value) {
   $rec=SQLSelectOne("SELECT * FROM zwave_properties WHERE ID='".$property_id."'");
   $device=SQLSelectOne("SELECT * FROM zwave_devices WHERE ID='".$rec['DEVICE_ID']."'");

   if ($rec['TITLE']=='Basic' && $device['CLASS_BASIC']) {
    $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[32].Set('.$value.')');
   }

   if ($rec['TITLE']=='Level') {
    if ($device['CLASS_SWITCH_BINARY']) {
     $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[37].Set('.$value.')');
    }
    if ($device['CLASS_SWITCH_MULTILEVEL']) {
     $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[38].Set('.$value.')');
    }
   }
   if ($rec['TITLE']=='LevelDuration command (level, duration)') {
    $tmp=explode(',', $value);
    $tmp[0]=(int)trim($tmp[0]);
    $tmp[1]=(int)trim($tmp[1]);
    if ($device['CLASS_SWITCH_MULTILEVEL']) {
     $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[38].Set('.$tmp[0].','.$tmp[1].')');
    }
   }
   if ($device['CLASS_THERMOSTAT'] && $rec['TITLE']=='Thermostat mode') {
   /*
    $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].']');
    $mode='';
    $av_modes=$data->commandClasses->{"64"}->data;
    for($i=0;$i<255;$i++) {
     if (isset($av_modes->{"$i"}->modename)) {
      if ($av_modes->{"$i"}->modename->value==$value) {
       $mode=$i;
       break;
      }
     }
    }
    if ($mode) {
     $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[64].Set('.$mode.')');
    }
    */
    $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[64].Set('.$value.')');
   }
   if ($device['CLASS_THERMOSTAT'] && $rec['TITLE']=='ThermostatFanMode') {
    $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[68].Set('.$value.')');
   }
   if ($device['CLASS_THERMOSTAT'] && preg_match('/ThermostatSetPoint (.+)/is', $rec['TITLE'], $m)) {
    $mode_name=$m[1];
    $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].']');
    $mode='';
    $av_modes=$data->commandClasses->{"67"}->data;
    for($i=0;$i<255;$i++) {
     if (isset($av_modes->{"$i"}->modename)) {
      if ($av_modes->{"$i"}->modename->value==$mode_name) {
       $mode=$i;
       break;
      }
     }
    }
    if ($mode) {
     $data=$this->apiCall('/ZWaveAPI/Run/devices['.$device['NODE_ID'].'].instances['.$device['INSTANCE_ID'].'].commandClasses[67].Set('.$mode.', '.$value.')');
    }
   }

   $this->pollDevice($rec['DEVICE_ID']);
  }


/**
* Title
*
* Description
*
* @access public
*/
 function resetChip() {
  //http://IP-OF-RAZBERRY:8083/ZWaveAPI/Run/SerialAPISoftReset(1)
  $cmd="/ZWaveAPI/Run/SerialAPISoftReset(1)";
  $data=$this->apiCall($cmd);
 }

/**
* Title
*
* Description
*
* @access public
*/
 function pollUpdates() {

  if (!$this->latestPoll) {
   $latest=$this->scanNetwork();
   if (!$latest) {
    echo "Error getting data!\n";
   } else {
    $this->latestPoll=$latest;
   }
  } else {

   $unknown_device_found=0;
   //sending update request for some properties
   $properties=SQLSelect("SELECT zwave_properties.*,zwave_devices.NODE_ID, zwave_devices.INSTANCE_ID FROM zwave_properties LEFT JOIN zwave_devices ON zwave_properties.DEVICE_ID=zwave_devices.ID WHERE UPDATE_PERIOD>0 AND (IsNull(zwave_properties.NEXT_UPDATE) OR zwave_properties.NEXT_UPDATE<='".date('Y-m-d H:i:s')."')");
   $total=count($properties);
   $get_codes_added=array();
   for($i=0;$i<$total;$i++) {
    $properties[$i]['NEXT_UPDATE']=date('Y-m-d H:i:s', time()+$properties[$i]['UPDATE_PERIOD']);
    if ($properties[$i]['COMMAND_CLASS'] && !$get_codes_added[$properties[$i]['NODE_ID'].'/'.$properties[$i]['INSTANCE_ID']]) {
     $get_codes_added[$properties[$i]['NODE_ID'].'/'.$properties[$i]['INSTANCE_ID']]=1;
     $cmd='/ZWaveAPI/Run/devices['.$properties[$i]['NODE_ID'].'].instances['.$properties[$i]['INSTANCE_ID'].'].commandClasses['.$properties[$i]['COMMAND_CLASS'].'].Get()';
     echo "Command: ".$cmd."\n";
     $data=$this->apiCall($cmd);
    }
    unset($properties[$i]['NODE_ID']);
    unset($properties[$i]['INSTANCE_ID']);
    SQLUpdate('zwave_properties', $properties[$i]);
   }


   //polling network for updates
   $data=$this->apiCall('/ZWaveAPI/Data/'.$this->latestPoll);
   $latest=$data->updateTime;
   if (!$latest) {
    echo "Error getting updates!\n";
   } else {
    $this->latestPoll=$latest;
    $this->latestDataReceived=time();
    $data=(array)$data;
    foreach($data as $k=>$v) {
     if (preg_match('/devices\.(\d+)\.instances\.(\d+)\.commandClasses/', $k, $m)) {
      $device_id=$m[1];
      $instance_id=$m[2];
      if (!$seen['d'.$device_id.'i'.$instance_id]) {
       $seen['d'.$device_id.'i'.$instance_id]=1;
       $rec=SQLSelectOne("SELECT ID from zwave_devices WHERE NODE_ID='".$device_id."' AND INSTANCE_ID='".$instance_id."'");
       if (!$rec['ID']) {
        echo "Unknown device (".$m[0].")\n";
        $unknown_device_found=1;
       } else {
        echo "Polling device (".$m[0].") ...";
        $this->pollDevice($rec['ID']);
        echo "OK\n";
       }
      }
     }
    }
    //print_r($data);

    if ($unknown_device_found) {
     $this->scanNetwork();
    }

   }
  }

  if ($this->config['ZWAVE_API_RESET'] && ((time()-$this->latestDataReceived)>(int)$this->config['ZWAVE_API_RESET']) && ((time()-$this->latestReset))>15*60) {
   // reset chip if no data was received during timeout set and latest reset was 15 minutes ago or latesr
   $this->latestReset=time();
   $this->resetChip();
  }

 }

 function propertySetHandle($object, $property, $value) {
   $zwave_properties=SQLSelect("SELECT ID FROM zwave_properties WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($zwave_properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     $this->setProperty($zwave_properties[$i]['ID'], $value);
    }
   }
 }

 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function pollDevice($device_id, $data=0) {

   $rec=SQLSelectOne("SELECT * FROM zwave_devices WHERE ID='".$device_id."'");

   $rec_updated=0;

   $comments=array();
   $updatedList=array();
   $properties=array();
   $command_classes=array();

   if (!$data) {
    $data=$this->apiCall('/ZWaveAPI/Run/devices['.$rec['NODE_ID'].'].instances['.$rec['INSTANCE_ID'].']');
   }

   if (!$data) {
    return 0;
   }

   if ($_GET['debug']) {
    //echo $data->updateTime;exit;
    var_dump($data);
   }

   $updateTime=0;

   if (!$rec['RAW_DATA']) {
    $rec_updated=1;
   }

   $rec['RAW_DATA']=json_encode($data);
   $rec['SENSOR_VALUE']='';

   if ($data->data->updateTime) {
    $updateTime=$data->data->updateTime;
   }

   if ($rec['CLASS_BASIC']) {
    $value=$data->commandClasses->{"32"}->data->value;
    if ($value!==$rec['BASIC']) {
     $rec['BASIC']=$value;
     $rec_updated=1;
    }
    $command_classes['Basic']=32;
    $properties['Basic']=$rec['BASIC'];
    $updatedList['Basic']=$data->commandClasses->{"32"}->data->{"updateTime"};
    if ($data->commandClasses->{"32"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"32"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_SENSOR_BINARY']) {

    $sensor_data=$data->commandClasses->{"48"}->data;
    if (isset($data->commandClasses->{"48"}->data->{"1"})) {
     $sensor_data=$data->commandClasses->{"48"}->data->{"1"};
    }
    $value=(int)$sensor_data->level->value;

    if ($value!==$rec['LEVEL']) {
     $rec['LEVEL']=$value;
     $rec_updated=1;
    }
    $properties['Level']=$rec['LEVEL'];
    $command_classes['Level']=48;
    $updatedList['Level']=$sensor_data->{"updateTime"};
    if ($sensor_data->{"updateTime"}>$updateTime) {
     $updateTime=$sensor_data->{"updateTime"};
    }
   }

   if ($rec['CLASS_SENSOR_MULTILEVEL']) {
    // multiple sensor support required!
    //SENSOR_VALUE
    $values=array();
    for($i=0;$i<255;$i++) {
     if (isset($data->commandClasses->{"49"}->data->{"$i"})) {
      $sensor=$data->commandClasses->{"49"}->data->{"$i"};
      $values[]=trim($sensor->sensorTypeString->value).': '.$sensor->val->value.$sensor->scaleString->value;
      if (trim($sensor->sensorTypeString->value)) {
       $prop_name=trim($sensor->sensorTypeString->value).', '.$sensor->scaleString->value;
      } else {
       $prop_name="Sensor $i";
      }
      if ($properties[$prop_name]) {
       $prop_name.=' (1)';
      }
      $properties[$prop_name]=$sensor->val->value;
      $command_classes[$prop_name]=49;
      $updatedList[$prop_name]=$data->commandClasses->{"49"}->data->{"$i"}->{"updateTime"};
      if ($data->commandClasses->{"49"}->data->{"$i"}->{"updateTime"}>$updateTime) {
       $updateTime=$data->commandClasses->{"49"}->data->{"$i"}->{"updateTime"};
      }
     }
    }
    $value=implode('; ', $values);
    if ($value!=$rec['SENSOR_VALUE']) {
     $rec['SENSOR_VALUE'].=$value.';';
     $rec_updated=1;
    }
   }

   if ($rec['CLASS_SWITCH_BINARY']) {
    $value=(int)$data->commandClasses->{"37"}->data->level->value;
    if ($value!==$rec['LEVEL']) {
     $rec['LEVEL']=$value;
     $rec_updated=1;
    }
    $properties['Level']=$rec['LEVEL'];
    $command_classes['Level']=37;
    $updatedList['Level']=$data->commandClasses->{"37"}->data->{"updateTime"};
    if ($data->commandClasses->{"37"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"37"}->data->{"updateTime"};
    }
   }
   if ($rec['CLASS_SWITCH_MULTILEVEL']) {
    $value=(int)$data->commandClasses->{"38"}->data->level->value;
    if ($value!==$rec['LEVEL']) {
     $rec['LEVEL']=$value;
     $rec_updated=1;
    }
    $properties['Level']=$rec['LEVEL'];
    $command_classes['Level']=38;
    $updatedList['Level']=$data->commandClasses->{"38"}->data->{"updateTime"};
    if ($data->commandClasses->{"38"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"38"}->data->{"updateTime"};
    }
    $properties['LevelDuration command (level, duration)']='';
   }
   if ($rec['CLASS_BATTERY']) {
    $value=(int)$data->commandClasses->{"128"}->data->last->value;
    if ($value!=$rec['BATTERY_LEVEL']) {
     $rec['BATTERY_LEVEL']=$value;
     $rec_updated=1;
    }
    $command_classes['Battery']=128;
    $properties['Battery']=$rec['BATTERY_LEVEL'];
    $updatedList['Battery']=$data->commandClasses->{"128"}->data->{"updateTime"};
    if ($data->commandClasses->{"128"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"128"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_METER']) {
    // ... 50
    $values=array();
    for($i=0;$i<255;$i++) {
     if (isset($data->commandClasses->{"50"}->data->{"$i"})) {
      $sensor=$data->commandClasses->{"50"}->data->{"$i"};
      $values[]=trim($sensor->sensorTypeString->value).': '.$sensor->val->value.' '.$sensor->scaleString->value;
      if (trim($sensor->sensorTypeString->value)) {
       $prop_name=trim($sensor->sensorTypeString->value).', '.$sensor->scaleString->value;
      } else {
       $prop_name="Meter $i";
      }
      if ($properties[$prop_name]) {
       $prop_name.=' (1)';
      }
      $command_classes[$prop_name]=50;
      $properties[$prop_name]=$sensor->val->value;
      $updatedList[$prop_name]=$data->commandClasses->{"50"}->data->{"$i"}->{"updateTime"};
      if ($data->commandClasses->{"50"}->data->{"$i"}->{"updateTime"}>$updateTime) {
       $updateTime=$data->commandClasses->{"50"}->data->{"$i"}->{"updateTime"};
      }
     }
    }
    $value=implode('; ', $values);
    if ($value!='') {
     $rec['SENSOR_VALUE'].=$value.'; ';
     $rec_updated=1;
    }
   }

   if ($rec['CLASS_SENSOR_ALARM']) {
    // ... $data->commandClasses->{"156"}->data
    if (is_object($data->commandClasses->{"156"}->data->{"0"}->sensorState)) {
     $command_classes['AlarmGeneral']=156;
     $properties['AlarmGeneral']=$data->commandClasses->{"156"}->data->{"0"}->sensorState->value;
     $updatedList['AlarmGeneral']=$data->commandClasses->{"156"}->data->{"0"}->sensorState->updateTime;
     $value=$properties['AlarmGeneral'];
     if ($value!=$rec['LEVEL']) {
      $rec['LEVEL']=$value;
      $rec_updated=1;
     }
    }
    if (is_object($data->commandClasses->{"156"}->data->{"1"}->sensorState)) {
     $command_classes['AlarmSmoke']=156;
     $properties['AlarmSmoke']=$data->commandClasses->{"156"}->data->{"1"}->sensorState->value;
     $updatedList['AlarmSmoke']=$data->commandClasses->{"156"}->data->{"1"}->sensorState->updateTime;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"2"}->sensorState)) {
     $command_classes['AlarmCarbonMonoxide']=156;
     $properties['AlarmCarbonMonoxide']=$data->commandClasses->{"156"}->data->{"2"}->sensorState->value;
     $updatedList['AlarmCarbonMonoxide']=$data->commandClasses->{"156"}->data->{"2"}->sensorState->updateTime;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"3"}->sensorState)) {
     $command_classes['AlarmCarbonDioxide']=156;
     $properties['AlarmCarbonDioxide']=$data->commandClasses->{"156"}->data->{"3"}->sensorState->value;
     $updatedList['AlarmCarbonDioxide']=$data->commandClasses->{"156"}->data->{"3"}->sensorState->updateTime;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"4"}->sensorState)) {
     $command_classes['AlarmHeat']=156;
     $properties['AlarmHeat']=$data->commandClasses->{"156"}->data->{"4"}->sensorState->value;
     $updatedList['AlarmHeat']=$data->commandClasses->{"156"}->data->{"4"}->sensorState->updateTime;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"5"}->sensorState)) {
     $command_classes['AlarmFlood']=156;
     $properties['AlarmFlood']=$data->commandClasses->{"156"}->data->{"5"}->sensorState->value;
     $updatedList['AlarmFlood']=$data->commandClasses->{"156"}->data->{"5"}->sensorState->updateTime;
    }
    if ($data->commandClasses->{"156"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"156"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_SCENE_CONTROLLER'] && is_object($data->commandClasses->{"43"}->data->{"currentScene"})) {
    // ... 43
    $properties['CurrentScene']=$data->commandClasses->{"43"}->data->{"currentScene"}->value;
    if ($data->commandClasses->{"43"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"43"}->data->{"updateTime"};
    }
   } elseif ($rec['CLASS_SCENE_CONTROLLER'] && is_object($data->commandClasses->{"45"}->data->{"currentScene"})) {
    // ... 45
    $properties['CurrentScene']=$data->commandClasses->{"45"}->data->{"currentScene"}->value;
    if ($data->commandClasses->{"45"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"43"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_THERMOSTAT'] && isset($data->commandClasses->{"64"}->data->mode->value)) {
    //$value=$data->commandClasses->{"64"}->data->{$data->commandClasses->{"64"}->data->mode->value}->modeName->value;
    $rec['SENSOR_VALUE'].=" Mode: ".$data->commandClasses->{"64"}->data->{$data->commandClasses->{"64"}->data->mode->value}->modeName->value.';';
    $value=$data->commandClasses->{"64"}->data->mode->value;
    if ($value!=$rec['MODE_VALUE']) {
     $rec['MODE_VALUE']=$value;
     $rec_updated=1;
    }
    $command_classes['Thermostat mode']=64;
    $properties['Thermostat mode']=$rec['MODE_VALUE'];
    $updatedList['Thermostat mode']=$data->commandClasses->{"64"}->data->{"updateTime"};
    if ($data->commandClasses->{"64"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"64"}->data->{"updateTime"};
    }

    $comments_str='';
    for($i=0;$i<255;$i++) {
     if ($data->commandClasses->{"64"}->data->{$i}) {
      $comments_str.="$i = ".$data->commandClasses->{"64"}->data->{$i}->modeName->value."; ";
     }
    }
    $comments['Thermostat mode']=$comments_str;

    if (isset($data->commandClasses->{"67"}->data)) {
     //ThermostatSetPoint
     for($i=0;$i<255;$i++) {
      if ($data->commandClasses->{"67"}->data->{$i}->val) {
       $key='ThermostatSetPoint '.$data->commandClasses->{"67"}->data->{$i}->modeName->value;
       $properties[$key]=$data->commandClasses->{"67"}->data->{$i}->val->value;
       $command_classes[$key]=67;
       if ($data->commandClasses->{"67"}->data->{$i}->scaleString->value) {
        $comments[$key]=$data->commandClasses->{"67"}->data->{$i}->scaleString->value;
       }
      }
     }
    }

    if (isset($data->commandClasses->{"68"}->data->mode->value)) {
     //ThermostatFanMode
     $properties['ThermostatFanOn']=(int)$data->commandClasses->{"68"}->data->on->value;
     $command_classes['ThermostatFanMode']=68;
     $properties['ThermostatFanMode']=$data->commandClasses->{"68"}->data->mode->value;
     if ($data->commandClasses->{"68"}->data->{"updateTime"}>$updateTime) {
      $updateTime=$data->commandClasses->{"68"}->data->{"updateTime"};
     }
     $rec['SENSOR_VALUE'].=" Fan Mode: ".$data->commandClasses->{"68"}->data->{$data->commandClasses->{"68"}->data->mode->value}->modeName->value.';';
     $comments_str='';
     for($i=0;$i<255;$i++) {
      if ($data->commandClasses->{"68"}->data->{$i}) {
       $comments_str.="$i = ".$data->commandClasses->{"68"}->data->{$i}->modeName->value."; ";
      }
     }
     $comments['ThermostatFanMode']=$comments_str;
    }


   }


   if ($updateTime) {
    $properties['updateTime']=$updateTime;
    $rec['LATEST_UPDATE']=date('Y-m-d H:i:s', $properties['updateTime']);
    $rec_updated=1;
   }


   if ($rec_updated) {
    SQLUpdate('zwave_devices', $rec);
   }



   foreach($properties as $k=>$v) {
    $prop=SQLSelectOne("SELECT * FROM zwave_properties WHERE DEVICE_ID='".$rec['ID']."' AND UNIQ_ID LIKE '".DBSafe($k)."'");
    $prop['DEVICE_ID']=$rec['ID'];
    $prop['UNIQ_ID']=$k;
    $prop['TITLE']=$k;
    $prop['COMMAND_CLASS']=$command_classes[$k];
    if ($prop['VALUE']!=$v) {
     $prop['UPDATED']=date('Y-m-d H:i:s');
    }
    if ($updatedList[$k]) {
     $prop['UPDATED']=date('Y-m-d H:i:s', $updatedList[$k]);
    }
    $prop['VALUE']=$v;
    if ($comments[$k]) {
     $prop['COMMENTS']=$comments[$k];
    }

    if (is_numeric($prop['VALUE']) && $prop['VALUE']!=='') {
     $prop['VALUE']=round($prop['VALUE'], 3);
    }


    if ($prop['ID']) {
     SQLUpdate('zwave_properties', $prop);

     if ($prop['VALUE']!=='') {
      $validated=1;
     } else {
      $validated=0;
      continue;
     }

     if ($prop['LINKED_OBJECT']) {
      if ($prop['CORRECT_VALUE']) {
       $prop['VALUE']+=(float)$prop['CORRECT_VALUE'];
      }
     }
     if ($prop['VALIDATE']) {
      if (((float)$prop['VALUE']<(float)$prop['VALID_FROM']) || ((float)$prop['VALUE']>(float)$prop['VALID_TO'])) {
       $validated=0;
      }
     }

 
     if ($prop['LINKED_OBJECT'] && $prop['LINKED_PROPERTY'] && $validated) {
      $old_value=getGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY']);
      if ($prop['VALUE']!=$old_value) {
       setGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'], $prop['VALUE'], array($this->name=>'0'));
      }
     }

     if ($prop['LINKED_OBJECT'] && $prop['LINKED_METHOD'] && $validated && ($prop['VALUE']!=$old_value || (!$prop['LINKED_PROPERTY']))) {
      $params=array();
      $params['VALUE']=$prop['VALUE'];
      callMethod($prop['LINKED_OBJECT'].'.'.$prop['LINKED_METHOD'], $params);
     }

    } else {
     $prop['ID']=SQLInsert('zwave_properties', $prop);
    }

   }

   //print_r($data);exit;

  }

/**
* Title
*
* Description
*
* @access public
*/
 function apiCall($command) {
   $this->getConfig();
   $command=preg_replace('/^\//', '', $command);
   $url=$this->config['ZWAVE_API_URL'].$command;
   $cookie_file=ROOT.'cached/zwave_cookie.txt';
   $ch = curl_init();  
   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json, text/javascript', 'Content-Type: application/json'));
   curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
   curl_setopt($ch, CURLOPT_TIMEOUT, 30);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
   $result = curl_exec($ch);
   curl_close($ch);

   if (preg_match('/307 Temporary Redirect/is', $result)) {
    if ($this->connect()) {

      $ch = curl_init();  
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json, text/javascript', 'Content-Type: application/json'));
      curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
      $result = curl_exec($ch);
      curl_close($ch);

    } else {
     return false;
    }
   }

   SaveFile(ROOT.'cached/zwave_api.txt',$url."\n\n".$result);
   return json_decode($result);

 }

/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* zwave_devices search
*
* @access public
*/
 function search_zwave_devices(&$out) {
  require(DIR_MODULES.$this->name.'/zwave_devices_search.inc.php');
 }
/**
* zwave_devices edit/add
*
* @access public
*/
 function edit_zwave_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/zwave_devices_edit.inc.php');
 }
/**
* zwave_devices delete record
*
* @access public
*/
 function delete_zwave_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM zwave_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM zwave_properties WHERE DEVICE_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM zwave_devices WHERE ID='".$rec['ID']."'");
 }
/**
* zwave_properties search
*
* @access public
*/
 function search_zwave_properties(&$out) {
  require(DIR_MODULES.$this->name.'/zwave_properties_search.inc.php');
 }

function prettyPrint($json)
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS zwave_devices');
  SQLExec('DROP TABLE IF EXISTS zwave_properties');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
zwave_devices - Z-Wave
zwave_properties - Properties
*/
  $data = <<<EOD
 zwave_devices: ID int(10) unsigned NOT NULL auto_increment
 zwave_devices: TITLE varchar(255) NOT NULL DEFAULT ''
 zwave_devices: NODE_ID varchar(50) NOT NULL DEFAULT ''
 zwave_devices: INSTANCE_ID varchar(50) NOT NULL DEFAULT ''
 zwave_devices: BASIC varchar(50) NOT NULL DEFAULT ''
 zwave_devices: LEVEL varchar(50) NOT NULL DEFAULT ''
 zwave_devices: BATTERY_LEVEL varchar(50) NOT NULL DEFAULT ''
 zwave_devices: SENSOR_VALUE varchar(255) NOT NULL DEFAULT ''
 zwave_devices: MODE_VALUE varchar(255) NOT NULL DEFAULT ''
 zwave_devices: DEVICE_TYPE varchar(50) NOT NULL DEFAULT ''
 zwave_devices: STATUS int(3) NOT NULL DEFAULT '0'
 zwave_devices: AUTO_POLL int(3) NOT NULL DEFAULT '0'
 zwave_devices: LATEST_UPDATE datetime
 zwave_devices: LOCATION_ID int(10) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_BASIC int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_SENSOR_BINARY int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_SENSOR_MULTILEVEL int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_SWITCH_BINARY int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_SWITCH_MULTILEVEL int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_METER int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_BATTERY int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_THERMOSTAT int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_SENSOR_ALARM int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_SCENE_CONTROLLER int(3) NOT NULL DEFAULT '0'
 zwave_devices: CLASS_CONFIG int(3) NOT NULL DEFAULT '0'
 zwave_devices: ALL_CLASSES varchar(255) NOT NULL DEFAULT ''
 zwave_devices: BRAND varchar(255) NOT NULL DEFAULT ''
 zwave_devices: PRODUCT varchar(255) NOT NULL DEFAULT ''
 zwave_devices: XMLFILE varchar(255) NOT NULL DEFAULT ''
 zwave_devices: RAW_DATA text NOT NULL DEFAULT ''

 zwave_properties: ID int(10) unsigned NOT NULL auto_increment
 zwave_properties: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 zwave_properties: UNIQ_ID varchar(100) NOT NULL DEFAULT ''
 zwave_properties: TITLE varchar(255) NOT NULL DEFAULT ''
 zwave_properties: COMMENTS varchar(255) NOT NULL DEFAULT ''
 zwave_properties: VALUE varchar(255) NOT NULL DEFAULT ''
 zwave_properties: VALUE_TYPE varchar(255) NOT NULL DEFAULT ''
 zwave_properties: READ_ONLY int(3) NOT NULL DEFAULT '0'
 zwave_properties: UPDATE_PERIOD int(10) NOT NULL DEFAULT '0'
 zwave_properties: VALIDATE int(3) NOT NULL DEFAULT '0'
 zwave_properties: VALID_FROM varchar(10) NOT NULL DEFAULT ''
 zwave_properties: VALID_TO varchar(10) NOT NULL DEFAULT ''
 zwave_properties: CORRECT_VALUE varchar(10) NOT NULL DEFAULT ''
 zwave_properties: COMMAND_CLASS varchar(10) NOT NULL DEFAULT ''
 zwave_properties: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 zwave_properties: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 zwave_properties: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''
 zwave_properties: UPDATED datetime
 zwave_properties: NEXT_UPDATE datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDMwLCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>