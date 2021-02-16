<?php
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
function __construct() {
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
  $out['CLASS_ID']=$this->class_id;
  
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
  $values=SQLSelect("SELECT * FROM pvalues WHERE PROPERTY_ID='".$rec['ID']."'");
  $total=count($values);
  for($i=0;$i<$total;$i++) {
   SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$values[$i]['ID']."'");
   SQLExec("DELETE FROM pvalues WHERE ID='".$values[$i]['ID']."'");
  }
  SQLExec("DELETE FROM properties WHERE ID='".$rec['ID']."'");
  clearCacheData();
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
   SQLDropTable('properties');
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