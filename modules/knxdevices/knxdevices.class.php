<?php
/**
* Knxdevices 
*
* Knxdevices
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 12:02:05 [Feb 17, 2015])
*/
//
//
class knxdevices extends module {
/**
* knxdevices
*
* Module class constructor
*
* @access private
*/
function knxdevices() {
  $this->name="knxdevices";
  $this->title="KNX";
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
  if (IsSet($this->device_id)) {
   $out['IS_SET_DEVICE_ID']=1;
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
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='localhost';
 }
 $out['API_PORT']=$this->config['API_PORT'];
 if (!$out['API_PORT']) {
  $out['API_PORT']='6720';
 }
 $out['API_ENABLE']=(int)$this->config['API_ENABLE'];


 if ($this->view_mode=='bus_monitor') {
  global $ajax;
  if ($ajax) {
   echo nl2br(LoadFile(ROOT.'cached/knx_monitor.txt'));
   exit;
  }
 }


 if ($this->view_mode=='update_settings') {
   global $api_url;
   global $api_port;
   global $api_enable;
   $this->config['API_URL']=$api_url;
   $this->config['API_PORT']=$api_port;
   $old_status=$this->config['API_ENABLE'];
   $this->config['API_ENABLE']=(int)$api_enable;
   if ($this->config['API_ENABLE']!=$old_status) {
    SaveFile(ROOT.'reboot');
   }
   $this->saveConfig();
   $this->redirect("?");
 }

 if ($this->data_source=='knxdevices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_knxdevices') {
   $this->search_knxdevices($out);
   $out['API_STATUS']=$this->connect();
  }
  if ($this->view_mode=='edit_knxdevices') {
   $this->edit_knxdevices($out, $this->id);
  }
  if ($this->view_mode=='delete_knxdevices') {
   $this->delete_knxdevices($this->id);
   $this->redirect("?data_source=knxdevices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='knxproperties') {
  if ($this->view_mode=='' || $this->view_mode=='search_knxproperties') {
   $this->search_knxproperties($out);
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
* knxdevices search
*
* @access public
*/
 function search_knxdevices(&$out) {
  require(DIR_MODULES.$this->name.'/knxdevices_search.inc.php');
 }
/**
* knxdevices edit/add
*
* @access public
*/
 function edit_knxdevices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/knxdevices_edit.inc.php');
 }
/**
* knxdevices delete record
*
* @access public
*/
 function delete_knxdevices($id) {
  $rec=SQLSelectOne("SELECT * FROM knxdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM knxproperties WHERE DEVICE_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM knxdevices WHERE ID='".$rec['ID']."'");
 }
/**
* knxproperties search
*
* @access public
*/
 function search_knxproperties(&$out) {
  require(DIR_MODULES.$this->name.'/knxproperties_search.inc.php');
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

function propertySetHandle($object, $property, $value) {
   $properties=SQLSelect("SELECT ID FROM knxproperties WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."' AND CAN_WRITE=1");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     $this->setProperty($properties[$i]['ID'], $value);
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
 function setProperty($id, $value) {
  $prop=SQLSelectOne("SELECT * FROM knxproperties WHERE ID='".$id."' AND CAN_WRITE=1");
  if (!$prop['ID']) {
   return false;
  }

  if (!$this->connection) {
   echo "Connecting";
   $this->connect(0);
  }

  $prop['DATA_VALUE']=$value;

  switch($prop['DATA_TYPE'])
    {
    case "small":
      $encoded = $value;
      break;

    case "p1":
      if ($value < 0)
        $value = 0;
      if ($value > 100)
        $value = 100;
      $value = (int)(($value * 255) / 100 + 0.5);
      $encoded = array ($value & 0xff);
      break;

    case "b1":
      $encoded=array ($value & 0xff);
      break;

    case "b2":
      $encoded=array (($value >> 8) & 0xff, $value & 0xff);
      break;

    case "f2":
      $encoded=f2_encode($value);
      break;
    }

  if (is_array($encoded)) {
   $prop['DATA_RAW']=implode(',', $encoded);
  } else {
   $prop['DATA_RAW']=$encoded;
  }
  $prop['UPDATED']=date('Y-m-d H:i:s');
  SQLUpdate('knxproperties', $prop);

  $res=groupwrite ($this->connection, $prop['ADDRESS'], $encoded);

  return $res;


 }


/**
* Title
*
* Description
*
* @access public
*/
 function getProperty($id) {
  $prop=SQLSelectOne("SELECT * FROM knxproperties WHERE ID='".$id."' AND CAN_READ=1");
  if (!$prop['ID']) {
   return false;
  }

  if (!$this->connection) {
   $this->connect(0);
  }

  if ($this->connection) {
   $res=cacheread($this->connection, $prop['ADDRESS'], 0);

   //var_dump($res);
   // $res[0] - addr
   // $res[1] - from

   if (isset($res[2])) {
    $data=array();
    $total=count($res);
    for($i=2;$i<$total;$i++) {
     $data[]=(int)$res[$i];
    }
    $old_value=$prop['DATA_VALUE'];
    if ($prop['DATA_TYPE']=='small') {
     $prop['DATA_VALUE']=$data[0];
    } elseif ($prop['DATA_TYPE']=='p1') {
     $prop['DATA_VALUE']=round(($data[0]*100)/255, 2);
    } elseif ($prop['DATA_TYPE']=='f2') {
     $prop['DATA_VALUE']=f2_decode($data);
    // TO-DO: OTHER TYPES!!!
    } elseif ($prop['DATA_TYPE']=='b1') {
    } elseif ($prop['DATA_TYPE']=='b2') {
    }
    
    $prop['DATA_RAW']=implode(',', $data);
    $prop['UPDATED']=date('Y-m-d H:i:s');

    print_r($prop);

    SQLUpdate('knxproperties', $prop);

    if ($prop['LINKED_OBJECT'] && $prop['LINKED_PROPERTY']) {
     setGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'], $prop['DATA_VALUE'], array($this->name=>'0'));
    }
    if ($prop['LINKED_OBJECT'] && $prop['LINKED_METHOD'] && ($prop['DATA_VALUE']!=$old_value)) {
      $params=array();
      $params['VALUE']=$prop['DATA_VALUE'];
      callMethod($prop['LINKED_OBJECT'].'.'.$prop['LINKED_METHOD'], $params);
    }

   }

  }

 }


 function addressUpdated($addr) {
  global $knxCacheUpdated;
  global $knxCache;

  if (time()-$knxCacheUpdated>60) {
   echo "Cache refresh\n";
   $knxCache=array();
   $properties=SQLSelect("SELECT ID, ADDRESS FROM knxproperties");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    $knxCache[$properties[$i]['ADDRESS']]=$properties[$i]['ID'];
   }
   $knxCacheUpdated=time();
  }

  echo "Updated: (".$addr.")\n";

  if ($knxCache[$addr]) {
   $this->getProperty($knxCache[$addr]);
  }

 }


/**
* Title
*
* Description
*
* @access public
*/
 function connect($close=1) {
  $this->getConfig();
  include_once(DIR_MODULES.$this->name.'/eibclient.php');
  include_once(DIR_MODULES.$this->name.'/help.php');

  if (!$this->config['API_ENABLE']) {
   return 0;
  }

  $hostname='localhost';

  if ($this->config['API_URL']) {
   $hostname=$this->config['API_URL'];
  }

  $port=6720;
  if ((int)$this->config['API_PORT']) {
   $port=(int)$this->config['API_PORT'];
  }

  try {
   $connection = new EIBConnection($hostname, $port);
  } catch(Exception $e){
   $out['ERROR']=$e->getMessage();
   return 0;
  }

  if ($connection) {
   $this->connection=$connection;
   if ($close) {
    $this->disconnect();
    return 1;
   }
   return 1;
  } else {
   return 0;
  }
 }

 function disconnect() {
  if ($this->connection) {
   $this->connection->EIBClose();
   return 1;
  }
 }

/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS knxdevices');
  SQLExec('DROP TABLE IF EXISTS knxproperties');
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
knxdevices - Knx Devices
knxproperties - Knx Properties
*/
  $data = <<<EOD
 knxdevices: ID int(10) unsigned NOT NULL auto_increment
 knxdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 knxdevices: TYPE varchar(255) NOT NULL DEFAULT ''
 knxproperties: ID int(10) unsigned NOT NULL auto_increment
 knxproperties: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 knxproperties: ADDRESS varchar(255) NOT NULL DEFAULT ''
 knxproperties: TITLE varchar(255) NOT NULL DEFAULT ''
 knxproperties: DATA_TYPE char(5) NOT NULL DEFAULT ''
 knxproperties: DATA_VALUE varchar(255) NOT NULL DEFAULT ''
 knxproperties: DATA_RAW varchar(255) NOT NULL DEFAULT ''
 knxproperties: CAN_READ int(3) NOT NULL DEFAULT '0'
 knxproperties: CAN_WRITE int(3) NOT NULL DEFAULT '0'
 knxproperties: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 knxproperties: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 knxproperties: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''
 knxproperties: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDE3LCAyMDE1IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>