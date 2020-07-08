<?php
/**
* History 
*
* History
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 12:05:22 [May 22, 2009])
*/
//
//
class history extends module {
/**
* history
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="history";
  $this->title="<#LANG_MODULE_OBJECTS_HISTORY#>";
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
  if (IsSet($this->method_id)) {
   $out['IS_SET_METHOD_ID']=1;
  }
  if (IsSet($this->value_id)) {
   $out['IS_SET_VALUE_ID']=1;
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
 if ($this->data_source=='history' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_history') {
   $this->search_history($out);
  }
  if ($this->view_mode=='edit_history') {
   $this->edit_history($out, $this->id);
  }
  if ($this->view_mode=='delete_history') {
   $this->delete_history($this->id);
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
* history search
*
* @access public
*/
 function search_history(&$out) {
  require(DIR_MODULES.$this->name.'/history_search.inc.php');
 }
/**
* history edit/add
*
* @access public
*/
 function edit_history(&$out, $id) {
  require(DIR_MODULES.$this->name.'/history_edit.inc.php');
 }
/**
* history delete record
*
* @access public
*/
 function delete_history($id) {
  $rec=SQLSelectOne("SELECT * FROM history WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM history WHERE ID='".$rec['ID']."'");
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
  SQLExec("UPDATE project_modules SET HIDDEN=1 WHERE NAME LIKE 'history'");
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
   SQLDropTable('history');
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
history - History
*/
  $data = <<<EOD
 history: ID int(10) unsigned NOT NULL auto_increment
 history: ADDED datetime
 history: OBJECT_ID int(10) NOT NULL DEFAULT '0'
 history: METHOD_ID int(10) NOT NULL DEFAULT '0'
 history: VALUE_ID int(10) NOT NULL DEFAULT '0'
 history: OLD_VALUE varchar(255) NOT NULL DEFAULT ''
 history: NEW_VALUE varchar(255) NOT NULL DEFAULT ''
 history: DETAILS text
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