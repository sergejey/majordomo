<?php
/**
* Methods 
*
* Methods
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 12:05:18 [May 22, 2009])
*/
//
//
class methods extends module {
/**
* methods
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="methods";
  $this->title="<#LANG_MODULE_METHODS#>";
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
function saveParams($data=1) {
 $data=array();
 if (IsSet($this->id)) {
  $data["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $data["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $data["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $data["tab"]=$this->tab;
 }
 return parent::saveParams($data);
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
  if (IsSet($this->object_id)) {
   $out['IS_SET_OBJECT_ID']=1;
  }
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
 if ($this->data_source=='methods' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_methods') {
   $this->search_methods($out);
  }
  if ($this->view_mode=='edit_methods') {
   $this->edit_methods($out, $this->id);
  }
  if ($this->view_mode=='delete_methods') {
   $this->delete_methods($this->id);
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
* methods search
*
* @access public
*/
 function search_methods(&$out) {
  require(DIR_MODULES.$this->name.'/methods_search.inc.php');
 }
/**
* methods edit/add
*
* @access public
*/
 function edit_methods(&$out, $id) {
  require(DIR_MODULES.$this->name.'/methods_edit.inc.php');
 }
/**
* methods delete record
*
* @access public
*/
 function delete_methods($id) {
  $rec=SQLSelectOne("SELECT * FROM methods WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM methods WHERE ID='".$rec['ID']."'");
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
   SQLDropTable('methods');
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
methods - Methods
*/
  $data = <<<EOD
 methods: ID int(10) unsigned NOT NULL auto_increment
 methods: OBJECT_ID int(10) NOT NULL DEFAULT '0'
 methods: CLASS_ID int(10) NOT NULL DEFAULT '0'
 methods: CALL_PARENT int(3) NOT NULL DEFAULT '0'
 methods: TITLE varchar(255) NOT NULL DEFAULT ''
 methods: DESCRIPTION text
 methods: CODE text
 methods: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 methods: EXECUTED datetime
 methods: EXECUTED_PARAMS varchar(255)
 methods: EXECUTED_SRC varchar(255)
 methods: INDEX (OBJECT_ID)
 methods: INDEX (CLASS_ID)
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