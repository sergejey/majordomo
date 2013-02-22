<?
/**
* Properties 
*
* Properties
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 12:05:47 [May 22, 2009])
*/
//
//
class properties extends module {
/**
* properties
*
* Module class constructor
*
* @access private
*/
function properties() {
  $this->name="properties";
  $this->title="<#LANG_MODULE_PROPERTIES#>";
  $this->module_category="<#LANG_SECTION_OBJECTS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams() {
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
  if (IsSet($this->class_id)) {
   $out['IS_SET_CLASS_ID']=1;
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
 if ($this->data_source=='properties' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_properties') {
   $this->search_properties($out);
  }
  if ($this->view_mode=='edit_properties') {
   $this->edit_properties($out, $this->id);
  }
  if ($this->view_mode=='delete_properties') {
   $this->delete_properties($this->id);
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
* properties search
*
* @access public
*/
 function search_properties(&$out) {
  require(DIR_MODULES.$this->name.'/properties_search.inc.php');
 }
/**
* properties edit/add
*
* @access public
*/
 function edit_properties(&$out, $id) {
  require(DIR_MODULES.$this->name.'/properties_edit.inc.php');
 }
/**
* properties delete record
*
* @access public
*/
 function delete_properties($id) {
  $rec=SQLSelectOne("SELECT * FROM properties WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM properties WHERE ID='".$rec['ID']."'");
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install() {
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
  SQLExec('DROP TABLE IF EXISTS properties');
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
properties - Properties
*/
  $data = <<<EOD
 properties: ID int(10) unsigned NOT NULL auto_increment
 properties: CLASS_ID int(10) NOT NULL DEFAULT '0'
 properties: OBJECT_ID int(10) NOT NULL DEFAULT '0'
 properties: TITLE varchar(255) NOT NULL DEFAULT ''
 properties: KEEP_HISTORY int(10) NOT NULL DEFAULT '0'
 properties: DESCRIPTION text
 properties: ONCHANGE varchar(255) NOT NULL DEFAULT ''

 phistory: ID int(10) unsigned NOT NULL auto_increment
 phistory: VALUE_ID int(10) unsigned NOT NULL DEFAULT '0'
 phistory: ADDED datetime

EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDIyLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>