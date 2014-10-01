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
 if ($this->view_mode=='update_settings') {
   global $zwave_api_url;
   global $zwave_api_username;
   global $zwave_api_password;
   global $zwave_api_auth;

   if (!preg_match('/\/$/', $zwave_api_url)) {
    $zwave_api_url=$zwave_api_url.'/';
   }
   $this->config['ZWAVE_API_URL']=$zwave_api_url;
   $this->config['ZWAVE_API_USERNAME']=$zwave_api_username;
   $this->config['ZWAVE_API_PASSWORD']=$zwave_api_password;
   $this->config['ZWAVE_API_AUTH']=(int)$zwave_api_auth;
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
    $this->pollDevice($rec['ID'], $devices[$i]);

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
   if ($rec['TITLE']=='Thermostat mode' && $device['CLASS_THERMOSTAT']) {
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
 function pollUpdates() {
  if (!$this->latestPoll) {
   $latest=$this->scanNetwork();
   if (!$latest) {
    echo "Error getting data!\n";
   } else {
    $this->latestPoll=$latest;
   }
  } else {
   $data=$this->apiCall('/ZWaveAPI/Data/'.$this->latestPoll);
   $latest=$data->updateTime;
   if (!$latest) {
    echo "Error getting updates!\n";
   } else {
    $this->latestPoll=$latest;

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
       } else {
        echo "Polling device (".$m[0].") ...";
        $this->pollDevice($rec['ID']);
        echo "OK\n";
       }
      }
     }
    }
    //print_r($data);
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

   $properties=array();

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

   if ($data->data->updateTime) {
    $updateTime=$data->data->updateTime;
   }

   if ($rec['CLASS_BASIC']) {
    $value=$data->commandClasses->{"32"}->data->value;
    if ($value!==$rec['BASIC']) {
     $rec['BASIC']=$value;
     $rec_updated=1;
    }
    $properties['Basic']=$rec['BASIC'];
    if ($data->commandClasses->{"32"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"32"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_SENSOR_BINARY']) {
    // ...
    $value=(int)$data->commandClasses->{"48"}->data->level->value;
    if ($value!==$rec['LEVEL']) {
     $rec['LEVEL']=$value;
     $rec_updated=1;
    }
    $properties['Level']=$rec['LEVEL'];
    if ($data->commandClasses->{"48"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"48"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_SENSOR_MULTILEVEL']) {
    // multiple sensor support required!
    //SENSOR_VALUE
    $values=array();
    for($i=0;$i<255;$i++) {
     if (isset($data->commandClasses->{"49"}->data->{"$i"})) {
      $sensor=$data->commandClasses->{"49"}->data->{"$i"};
      $values[]=$sensor->sensorTypeString->value.': '.$sensor->val->value.$sensor->scaleString->value;
      $properties[$sensor->sensorTypeString->value.', '.$sensor->scaleString->value]=$sensor->val->value;
      if ($data->commandClasses->{"49"}->data->{"$i"}->{"updateTime"}>$updateTime) {
       $updateTime=$data->commandClasses->{"49"}->data->{"$i"}->{"updateTime"};
      }
     }
    }
    $value=implode('; ', $values);
    if ($value!=$rec['SENSOR_VALUE']) {
     $rec['SENSOR_VALUE']=$value;
     $rec_updated=1;
    }
   }

   if ($rec['CLASS_THERMOSTAT']) {
    $value=$data->commandClasses->{"64"}->data->{$data->commandClasses->{"64"}->data->mode->value}->modeName->value;
    if ($value!=$rec['MODE_VALUE']) {
     $rec['MODE_VALUE']=$value;
     $rec_updated=1;
    }
    $properties['Thermostat mode']=$rec['MODE_VALUE'];
    if ($data->commandClasses->{"64"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"64"}->data->{"updateTime"};
    }
   }


   if ($rec['CLASS_SWITCH_BINARY']) {
    $value=(int)$data->commandClasses->{"37"}->data->level->value;
    if ($value!==$rec['LEVEL']) {
     $rec['LEVEL']=$value;
     $rec_updated=1;
    }
    $properties['Level']=$rec['LEVEL'];
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
    if ($data->commandClasses->{"38"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"38"}->data->{"updateTime"};
    }
   }
   if ($rec['CLASS_BATTERY']) {
    $value=(int)$data->commandClasses->{"128"}->data->last->value;
    if ($value!=$rec['BATTERY_LEVEL']) {
     $rec['BATTERY_LEVEL']=$value;
     $rec_updated=1;
    }
    $properties['Battery']=$rec['BATTERY_LEVEL'];
    if ($data->commandClasses->{"128"}->data->{"updateTime"}>$updateTime) {
     $updateTime=$data->commandClasses->{"128"}->data->{"updateTime"};
    }
   }

   if ($rec['CLASS_METER']) {
    // ...
   }

   if ($rec['CLASS_SENSOR_ALARM']) {
    // ... $data->commandClasses->{"156"}->data
    if (is_object($data->commandClasses->{"156"}->data->{"0"}->sensorState)) {
     $properties['AlarmGeneral']=$data->commandClasses->{"156"}->data->{"0"}->sensorState->value;
     $value=$properties['AlarmGeneral'];
     if ($value!=$rec['LEVEL']) {
      $rec['LEVEL']=$value;
      $rec_updated=1;
     }
    }
    if (is_object($data->commandClasses->{"156"}->data->{"1"}->sensorState)) {
     $properties['AlarmSmoke']=$data->commandClasses->{"156"}->data->{"1"}->sensorState->value;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"2"}->sensorState)) {
     $properties['AlarmCarbonMonoxide']=$data->commandClasses->{"156"}->data->{"2"}->sensorState->value;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"3"}->sensorState)) {
     $properties['AlarmCarbonDioxide']=$data->commandClasses->{"156"}->data->{"3"}->sensorState->value;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"4"}->sensorState)) {
     $properties['AlarmHeat']=$data->commandClasses->{"156"}->data->{"4"}->sensorState->value;
    }
    if (is_object($data->commandClasses->{"156"}->data->{"5"}->sensorState)) {
     $properties['AlarmFlood']=$data->commandClasses->{"156"}->data->{"5"}->sensorState->value;
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
    $prop['VALUE']=$v;
    $prop['UPDATED']=date('Y-m-d H:i:s');
    if ($prop['ID']) {
     SQLUpdate('zwave_properties', $prop);
     if ($prop['LINKED_OBJECT'] && $prop['LINKED_PROPERTY']) {
      setGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'], $prop['VALUE'], array('zwave_properties'=>'0'));
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
 zwave_devices: ALL_CLASSES varchar(255) NOT NULL DEFAULT ''

 zwave_properties: ID int(10) unsigned NOT NULL auto_increment
 zwave_properties: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 zwave_properties: UNIQ_ID varchar(100) NOT NULL DEFAULT ''
 zwave_properties: TITLE varchar(255) NOT NULL DEFAULT ''
 zwave_properties: VALUE varchar(255) NOT NULL DEFAULT ''
 zwave_properties: VALUE_TYPE varchar(255) NOT NULL DEFAULT ''
 zwave_properties: READ_ONLY int(3) NOT NULL DEFAULT '0'
 zwave_properties: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 zwave_properties: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 zwave_properties: UPDATED datetime
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