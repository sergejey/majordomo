<?
/**
* Users 
*
* Users
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 15:03:53 [Mar 27, 2009])
*/
//
//
class users extends module {
/**
* users
*
* Module class constructor
*
* @access private
*/
function users() {
  $this->name="users";
  $this->title="<#LANG_MODULE_USERS#>";
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
 if ($this->data_source=='users' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_users') {
   $this->search_users($out);
  }
  if ($this->view_mode=='edit_users') {
   $this->edit_users($out, $this->id);
  }
  if ($this->view_mode=='delete_users') {
   $this->delete_users($this->id);
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
* users search
*
* @access public
*/
 function search_users(&$out) {
  require(DIR_MODULES.$this->name.'/users_search.inc.php');
 }
/**
* users edit/add
*
* @access public
*/
 function edit_users(&$out, $id) {
  require(DIR_MODULES.$this->name.'/users_edit.inc.php');
 }
/**
* users delete record
*
* @access public
*/
 function delete_users($id) {
  $rec=SQLSelectOne("SELECT * FROM users WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM users WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS users');
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
users - Users
*/
  $data = <<<EOD
 users: ID int(10) unsigned NOT NULL auto_increment
 users: USERNAME varchar(255) NOT NULL DEFAULT ''
 users: NAME varchar(255) NOT NULL DEFAULT ''
 users: SKYPE varchar(255) NOT NULL DEFAULT ''
 users: MOBILE varchar(255) NOT NULL DEFAULT ''
 users: EMAIL char(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDI3LCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>