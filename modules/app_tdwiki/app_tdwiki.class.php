<?php
/**
* TdWiki 
*
* TdWiki
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 11:11:36 [Nov 01, 2006])
*/
//
//
class app_tdwiki extends module {
/**
* tdwiki
*
* Module class constructor
*
* @access private
*/
function app_tdwiki() {
  $this->name="app_tdwiki";
  $this->title="<#LANG_APP_TDWIKI#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
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
 //DebMes(time());
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }

 if (!$this->name_id) {
  $this->name_id='default';
 }

 if ($this->mode=='reset') {

  $table_name='tdwiki';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE NAME='".$this->name_id."'");
  if (!$rec['ID']) {
   $rec['NAME']=$this->name_id;
   $rec['CONTENT']=LoadFile(DIR_TEMPLATES.$this->name.'/default.html');
   $rec['ID']=SQLInsert($table_name, $rec);
  } else {
   $rec['NAME']=$this->name_id;
   $rec['CONTENT']=LoadFile(DIR_TEMPLATES.$this->name.'/default.html');
   SQLUpdate($table_name, $rec);
  }
  //print_r($rec);exit;

 }

 if ($this->data_source=='tdwiki' || $this->data_source=='') {
  if ($this->view_mode=='search_tdwiki') {
   $this->search_tdwiki($out);
  }
  if ($this->view_mode=='edit_tdwiki' || $this->view_mode=='') {
   $this->edit_tdwiki($out, $this->id);
  }
  if ($this->view_mode=='delete_tdwiki') {
   $this->delete_tdwiki($this->id);
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
* tdwiki search
*
* @access public
*/
 function search_tdwiki(&$out) {
  require(DIR_MODULES.$this->name.'/tdwiki_search.inc.php');
 }
/**
* tdwiki edit/add
*
* @access public
*/
 function edit_tdwiki(&$out, $id) {
  require(DIR_MODULES.$this->name.'/tdwiki_edit.inc.php');
 }
/**
* tdwiki view record
*
* @access public
*/
 function view_tdwiki(&$out, $id) {
  require(DIR_MODULES.$this->name.'/tdwiki_view.inc.php');
 }
/**
* tdwiki delete record
*
* @access public
*/
 function delete_tdwiki($id) {
  $rec=SQLSelectOne("SELECT * FROM tdwiki WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM tdwiki WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS tdwiki');
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
tdwiki - TdWiki
*/
  $data = <<<EOD
 tdwiki: ID int(10) unsigned NOT NULL auto_increment
 tdwiki: NAME varchar(100) NOT NULL DEFAULT ''
 tdwiki: CONTENT text
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDAxLCAyMDA2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>