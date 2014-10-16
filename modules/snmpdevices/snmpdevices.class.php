<?php
/**
* Snmp Devices 
*
* Snmpdevices
*
* @package project
* @author Serge J. <CMS>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 12:04:10 [Apr 29, 2013])
*/
//
//
class snmpdevices extends module {
/**
* snmpdevices
*
* Module class constructor
*
* @access private
*/
function snmpdevices() {
  $this->name="snmpdevices";
  $this->title="<#LANG_MODULE_SNMP#>";
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
* Title
*
* Description
*
* @access public
*/
 function readDevice($id) {
  $props=SQLSelect("SELECT ID FROM snmpproperties WHERE DEVICE_ID='".$id."' ORDER BY UPDATED DESC");  
  $total=count($props);
  for($i=0;$i<$total;$i++) {
   $this->readProperty($props[$i]['ID']);
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function readAll($force=0) {

  if ($force) {
   $props=SQLSelect("SELECT ID FROM snmpproperties WHERE 1 ORDER BY UPDATED DESC");
  } else {
   $props=SQLSelect("SELECT ID FROM snmpproperties WHERE ONLINE_INTERVAL>0 AND CHECK_NEXT<=NOW() ORDER BY CHECK_NEXT");
  }
  
  $total=count($props);
  for($i=0;$i<$total;$i++) {
   $this->readProperty($props[$i]['ID']);
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
function readProperty($p_id) {
 $prec=SQLSelectOne("SELECT * FROM snmpproperties WHERE ID='".$p_id."'");
 if (!$prec['ID']) {
  return;
 }
 $drec=SQLSelectOne("SELECT * FROM snmpdevices WHERE ID='".$prec['DEVICE_ID']."'");
 if (!$drec['ID']) {
  return 0;
 }

 $snmp_oid = $prec['OID'];
 $snmp_host = $drec['HOST'];
 $snmp_community = $drec['READ_COMMUNITY'];
 @$value = snmpget($snmp_host, $snmp_community, $snmp_oid);
 if ($value===false) {
  return false;
 }

 if (preg_match('/^(\w+:)/', $value, $m)) {
  $value=trim(str_replace($m[1], '', $value));
 }

 $prec['VALUE']=$value;
 $prec['UPDATED']=date('Y-m-d H:i:s');
 if ($prec['ONLINE_INTERVAL']) {
  $prec['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$prec['ONLINE_INTERVAL']);
 }
 SQLUpdate('snmpproperties', $prec);

 if ($prec['LINKED_OBJECT'] && $prec['LINKED_PROPERTY']) {
  setGlobal($prec['LINKED_OBJECT'].'.'.$prec['LINKED_PROPERTY'], $value, array($this->name=>'0'));
 }
 return $value;

}

function setProperty($p_id, $value) {
 $prec=SQLSelectOne("SELECT * FROM snmpproperties WHERE ID='".$p_id."'");  
 if (!$prec['ID']) {
  return 0;
 }
 $drec=SQLSelectOne("SELECT * FROM snmpdevices WHERE ID='".$prec['DEVICE_ID']."'");
 if (!$drec['ID']) {
  return 0;
 }
 $snmp_oid = $prec['OID'];
 $snmp_host = $drec['HOST'];
 $snmp_community = $drec['READ_COMMUNITY'];

 $type='i';
 //int string counter oid ip
 if ($prec['TYPE']=='int') {
  $type='i';
 } elseif ($prec['TYPE']=='uint') {
  $type='u';
 } elseif ($prec['TYPE']=='string') {
  $type='s';
 } elseif ($prec['TYPE']=='counter') {
  $type='t';
 } elseif ($prec['TYPE']=='oid') {
  $type='o';
 } elseif ($prec['TYPE']=='ip') {
  $type='a';
 }

 @$res=snmpset($snmp_host, $snmp_community, $snmp_oid, $type, $value);
 if ($res) {
  $this->readProperty($p_id);
 }
 return $res;
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
 if ($this->data_source=='snmpdevices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_snmpdevices') {
   $this->search_snmpdevices($out);
  }

  if ($this->view_mode=='update_all') {
   $this->readAll(1);
   $this->redirect("?");
  }

  if ($this->view_mode=='edit_snmpdevices') {
   $this->edit_snmpdevices($out, $this->id);
  }
  if ($this->view_mode=='delete_snmpdevices') {
   $this->delete_snmpdevices($this->id);
   $this->redirect("?data_source=snmpdevices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='snmpproperties') {
  if ($this->view_mode=='' || $this->view_mode=='search_snmpproperties') {
   $this->search_snmpproperties($out);
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
* snmpdevices search
*
* @access public
*/
 function search_snmpdevices(&$out) {
  require(DIR_MODULES.$this->name.'/snmpdevices_search.inc.php');
 }
/**
* snmpdevices edit/add
*
* @access public
*/
 function edit_snmpdevices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/snmpdevices_edit.inc.php');
 }

 function propertySetHandle($object, $property, $value) {
   $snmpdevices=SQLSelect("SELECT ID FROM snmpproperties WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($snmpdevices);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     $this->setProperty($snmpdevices[$i]['ID'], $value);
    }
   }
 }
    

/**
* snmpdevices delete record
*
* @access public
*/
 function delete_snmpdevices($id) {
  $rec=SQLSelectOne("SELECT * FROM snmpdevices WHERE ID='$id'");
  // some action for related tables
  @unlink(ROOT.'./cms/snmpdevices/'.$rec['MIB_FILE']);
  SQLExec("DELETE FROM snmpdevices WHERE ID='".$rec['ID']."'");
 }
/**
* snmpproperties search
*
* @access public
*/
 function search_snmpproperties(&$out) {
  require(DIR_MODULES.$this->name.'/snmpproperties_search.inc.php');
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install($data);
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS snmpdevices');
  SQLExec('DROP TABLE IF EXISTS snmpproperties');
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
snmpdevices - Snmp Devices
snmpproperties - Snmp Properties
*/
  $data = <<<EOD
 snmpdevices: ID int(10) unsigned NOT NULL auto_increment
 snmpdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 snmpdevices: HOST varchar(255) NOT NULL DEFAULT ''
 snmpdevices: STATUS int(3) NOT NULL DEFAULT '0'
 snmpdevices: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 snmpdevices: CODE text
 snmpdevices: MIB_FILE varchar(70) NOT NULL DEFAULT ''
 snmpdevices: READ_COMMUNITY varchar(255) NOT NULL DEFAULT ''
 snmpdevices: WRITE_COMMUNITY varchar(255) NOT NULL DEFAULT ''
 snmpdevices: TRAPLOG text

 snmpproperties: ID int(10) unsigned NOT NULL auto_increment
 snmpproperties: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 snmpproperties: TITLE varchar(255) NOT NULL DEFAULT ''
 snmpproperties: OID varchar(255) NOT NULL DEFAULT ''
 snmpproperties: VALUE varchar(255) NOT NULL DEFAULT ''
 snmpproperties: TYPE char(10) NOT NULL DEFAULT ''
 snmpproperties: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 snmpproperties: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 snmpproperties: ONLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 snmpproperties: CHECK_NEXT datetime
 snmpproperties: UPDATED datetime

EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDI5LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>