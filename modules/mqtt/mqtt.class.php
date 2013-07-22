<?
/**
* MQTT 
*
* Mqtt
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 13:07:08 [Jul 19, 2013])
*/
//
//
class mqtt extends module {
/**
* mqtt
*
* Module class constructor
*
* @access private
*/
function mqtt() {
  $this->name="mqtt";
  $this->title="<#LANG_MODULE_MQTT#>";
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
  $out['TAB']=$this->tab;
  if (IsSet($this->location_id)) {
   $out['IS_SET_LOCATION_ID']=1;
  }
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}


/**
* Title
*
* Description
*
* @access public
*/
 function setProperty($id, $value, $set_linked=0) {
  $rec=SQLSelectOne("SELECT * FROM mqtt WHERE ID='".$id."'");
  if (!$rec['ID'] || !$rec['PATH']) {
   return 0;
  }
  include_once("./lib/mqtt/phpMQTT.php");

  $this->getConfig();

  if ($this->config['MQTT_AUTH']) {
   $username=$this->config['MQTT_USERNAME'];
   $password=$this->config['MQTT_PASSWORD'];
  }
  if ($this->config['MQTT_HOST']) {
   $host=$this->config['MQTT_HOST'];
  } else {
   $host='localhost';
  }
  if ($this->config['MQTT_PORT']) {
   $port=$this->config['MQTT_PORT'];
  } else {
   $port=1883;
  }

  $mqtt_client = new phpMQTT($host, $port, "MajorDoMo MQTT Client");

  if(!$mqtt_client->connect(true, NULL,$username,$password)) /*Clean, Will, User, Password*/
  {
   return 0;
  }
  $mqtt_client->publish($rec['PATH'],$value); /*$topic, $content, $qos = 0, $retain = 0*/
  $mqtt_client->close();

  if ($set_linked && $rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
   setGlobal($rec['LINKED_OBJECT'].'.'.$rec['LINKED_PROPERTY'], $value, 1);
  }

 }

/**
* Title
*
* Description
*
* @access public
*/
 function processMessage($path, $value) {

  if (preg_match('/\#$/', $path)) {
   return 0;
  }

  $rec=SQLSelectOne("SELECT * FROM mqtt WHERE PATH LIKE '".DBSafe($path)."'");

  if ($rec['ID'] && $value==$rec['VALUE']) {
   return;
  }

  if (!$rec['ID']) {
   $rec['PATH']=$path;
   $rec['TITLE']=$path;
   $rec['ID']=SQLInsert('mqtt', $rec);
  }
  $rec['VALUE']=$value;
  $rec['UPDATED']=date('Y-m-d H:i:s');
  SQLUpdate('mqtt', $rec);
  if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
   setGlobal($rec['LINKED_OBJECT'].'.'.$rec['LINKED_PROPERTY'], $rec['VALUE'], 1);
  }
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
 $out['MQTT_HOST']=$this->config['MQTT_HOST'];
 $out['MQTT_PORT']=$this->config['MQTT_PORT'];
 $out['MQTT_QUERY']=$this->config['MQTT_QUERY'];

 if (!$out['MQTT_HOST']) {
  $out['MQTT_HOST']='localhost';
 }
 if (!$out['MQTT_PORT']) {
  $out['MQTT_PORT']='1883';
 }
 if (!$out['MQTT_QUERY']) {
  $out['MQTT_QUERY']='/var/now/#';
 }

 $out['MQTT_USERNAME']=$this->config['MQTT_USERNAME'];
 $out['MQTT_PASSWORD']=$this->config['MQTT_PASSWORD'];
 $out['MQTT_AUTH']=$this->config['MQTT_AUTH'];

 if ($this->view_mode=='update_settings') {
   global $mqtt_host;
   global $mqtt_username;
   global $mqtt_password;
   global $mqtt_auth;
   global $mqtt_port;
   global $mqtt_query;

   $this->config['MQTT_HOST']=$mqtt_host;
   $this->config['MQTT_USERNAME']=$mqtt_username;
   $this->config['MQTT_PASSWORD']=$mqtt_password;
   $this->config['MQTT_AUTH']=(int)$mqtt_auth;
   $this->config['MQTT_PORT']=(int)$mqtt_port;
   $this->config['MQTT_QUERY']=trim($mqtt_query);
   $this->saveConfig();
   $this->redirect("?");
 }

 if (!$this->config['MQTT_HOST']) {
  $this->config['MQTT_HOST']='localhost';
  $this->saveConfig();
 }
 if (!$this->config['MQTT_PORT']) {
  $this->config['MQTT_PORT']='1883';
  $this->saveConfig();
 }
 if (!$this->config['MQTT_QUERY']) {
  $this->config['MQTT_QUERY']='/var/now/#';
  $this->saveConfig();
 }


 if ($this->data_source=='mqtt' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_mqtt') {
   $this->search_mqtt($out);
  }
  if ($this->view_mode=='edit_mqtt') {
   $this->edit_mqtt($out, $this->id);
  }
  if ($this->view_mode=='delete_mqtt') {
   $this->delete_mqtt($this->id);
   $this->redirect("?");
  }
 }
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
* mqtt search
*
* @access public
*/
 function search_mqtt(&$out) {
  require(DIR_MODULES.$this->name.'/mqtt_search.inc.php');
 }
/**
* mqtt edit/add
*
* @access public
*/
 function edit_mqtt(&$out, $id) {
  require(DIR_MODULES.$this->name.'/mqtt_edit.inc.php');
 }
/**
* mqtt delete record
*
* @access public
*/
 function delete_mqtt($id) {
  $rec=SQLSelectOne("SELECT * FROM mqtt WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM mqtt WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS mqtt');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall() {
/*
mqtt - MQTT
*/
  $data = <<<EOD
 mqtt: ID int(10) unsigned NOT NULL auto_increment
 mqtt: TITLE varchar(255) NOT NULL DEFAULT ''
 mqtt: LOCATION_ID int(10) NOT NULL DEFAULT '0'
 mqtt: UPDATED datetime
 mqtt: VALUE varchar(255) NOT NULL DEFAULT ''
 mqtt: PATH varchar(255) NOT NULL DEFAULT ''
 mqtt: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 mqtt: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDE5LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>