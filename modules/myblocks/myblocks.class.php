<?php
/**
* Myblocks 
*
* Myblocks
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 14:09:42 [Sep 23, 2014])
*/
//
//
class myblocks extends module {
/**
* myblocks
*
* Module class constructor
*
* @access private
*/
function myblocks() {
  $this->name="myblocks";
  $this->title="<#LANG_MODULE_MYBLOCKS#>";
  $this->module_category="<#LANG_SECTION_SETTINGS#>";
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
 if ($this->data_source=='myblocks' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_myblocks') {
   $this->search_myblocks($out);
  }
  if ($this->view_mode=='edit_myblocks') {
   $this->edit_myblocks($out, $this->id);
  }
  if ($this->view_mode=='delete_myblocks') {
   $this->delete_myblocks($this->id);
   $this->redirect("?data_source=myblocks");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='myblocks_categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_myblocks_categories') {
   $this->search_myblocks_categories($out);
  }
  if ($this->view_mode=='edit_myblocks_categories') {
   $this->edit_myblocks_categories($out, $this->id);
  }
  if ($this->view_mode=='delete_myblocks_categories') {
   $this->delete_myblocks_categories($this->id);
   $this->redirect("?data_source=myblocks_categories");
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
* myblocks search
*
* @access public
*/
 function search_myblocks(&$out) {
  require(DIR_MODULES.$this->name.'/myblocks_search.inc.php');
 }
/**
* myblocks edit/add
*
* @access public
*/
 function edit_myblocks(&$out, $id) {
  require(DIR_MODULES.$this->name.'/myblocks_edit.inc.php');
 }
/**
* myblocks delete record
*
* @access public
*/
 function delete_myblocks($id) {
  $rec=SQLSelectOne("SELECT * FROM myblocks WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM myblocks WHERE ID='".$rec['ID']."'");
 }
/**
* myblocks_categories search
*
* @access public
*/
 function search_myblocks_categories(&$out) {
  require(DIR_MODULES.$this->name.'/myblocks_categories_search.inc.php');
 }
/**
* myblocks_categories edit/add
*
* @access public
*/
 function edit_myblocks_categories(&$out, $id) {
  require(DIR_MODULES.$this->name.'/myblocks_categories_edit.inc.php');
 }
/**
* myblocks_categories delete record
*
* @access public
*/
 function delete_myblocks_categories($id) {
  $rec=SQLSelectOne("SELECT * FROM myblocks_categories WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM myblocks_categories WHERE ID='".$rec['ID']."'");
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
   SQLDropTable('myblocks');
   SQLDropTable('myblocks_categories');
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
myblocks - Myblocks
myblocks_categories - Myblocks Categories
*/
  $data = <<<EOD
 myblocks: ID int(10) unsigned NOT NULL auto_increment
 myblocks: SYSTEM varchar(255) NOT NULL DEFAULT ''
 myblocks: TITLE varchar(255) NOT NULL DEFAULT ''
 myblocks: CATEGORY_ID int(10) NOT NULL DEFAULT '0'
 myblocks: BLOCK_TYPE char(10) NOT NULL DEFAULT ''
 myblocks: BLOCK_COLOR int(10) NOT NULL DEFAULT '0'
 myblocks: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 myblocks: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 myblocks: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 myblocks_categories: ID int(10) unsigned NOT NULL auto_increment
 myblocks_categories: TITLE varchar(255) NOT NULL DEFAULT ''
 myblocks_categories: SYSTEM varchar(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDIzLCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>