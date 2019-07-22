<?php
/**
* Shoutrooms 
*
* Shoutrooms
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 17:03:02 [Mar 29, 2007])
*/
//
//
class shoutrooms extends module {
/**
* shoutrooms
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="shoutrooms";
  $this->title="<#LANG_MODULE_SHOUTROOMS#>";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data = 0) {
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
 if ($this->data_source=='shoutrooms' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_shoutrooms') {
   $this->search_shoutrooms($out);
  }
  if ($this->view_mode=='edit_shoutrooms') {
   $this->edit_shoutrooms($out, $this->id);
  }
  if ($this->view_mode=='delete_shoutrooms') {
   $this->delete_shoutrooms($this->id);
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
* shoutrooms search
*
* @access public
*/
 function search_shoutrooms(&$out) {
  require(DIR_MODULES.$this->name.'/shoutrooms_search.inc.php');
 }
/**
* shoutrooms edit/add
*
* @access public
*/
 function edit_shoutrooms(&$out, $id) {
  require(DIR_MODULES.$this->name.'/shoutrooms_edit.inc.php');
 }
/**
* shoutrooms delete record
*
* @access public
*/
 function delete_shoutrooms($id) {
  $rec=SQLSelectOne("SELECT * FROM shoutrooms WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM shouts WHERE ROOM_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM shoutrooms WHERE ID='".$rec['ID']."'");
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
   SQLDropTable('shoutrooms');
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
shoutrooms - Shoutrooms
*/
  $data = <<<EOD
 shoutrooms: ID int(10) unsigned NOT NULL auto_increment
 shoutrooms: TITLE varchar(250) NOT NULL DEFAULT ''
 shoutrooms: PRIORITY int(10) NOT NULL DEFAULT '0'
 shoutrooms: ADDED_BY int(10) NOT NULL DEFAULT '0'
 shoutrooms: ADDED datetime
 shoutrooms: IS_PUBLIC int(3) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDI5LCAyMDA3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>