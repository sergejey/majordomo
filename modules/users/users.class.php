<?php
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
function __construct() {
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
 global $session;
 $users=SQLSelect("SELECT * FROM users ORDER BY NAME");
 $total=count($users);
 for($i=0;$i<$total;$i++) {
  if ($users[$i]['ID']==$session->data['SITE_USER_ID']) {
   $users[$i]['SELECTED']=1;
  }
 }
 $out['USERS']=$users;
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
   SQLDropTable('users');
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
users - Users
*/
   
 // update password for users
 $users=SQLSelect("SELECT * FROM users");
 foreach ($users as $user) {
     if (strlen ($user['PASSWORD']) < 128 ) {
          $user['PASSWORD'] = hash('sha512', $user['PASSWORD']);
          SQLUpdate ('users', $user);
      }
  }
   
  $data = <<<EOD
 users: ID int(10) unsigned NOT NULL auto_increment
 users: USERNAME varchar(255) NOT NULL DEFAULT ''
 users: NAME varchar(255) NOT NULL DEFAULT ''
 users: SKYPE varchar(255) NOT NULL DEFAULT ''
 users: MOBILE varchar(255) NOT NULL DEFAULT ''
 users: AVATAR varchar(255) NOT NULL DEFAULT ''
 users: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 users: PASSWORD varchar(255) NOT NULL DEFAULT ''
 users: IS_ADMIN tinyint(3) NOT NULL DEFAULT '0'
 users: IS_DEFAULT tinyint(3) NOT NULL DEFAULT '0'
 users: HOST varchar(255) NOT NULL DEFAULT ''
 users: EMAIL char(255) NOT NULL DEFAULT ''
 users: ACTIVE_CONTEXT_ID int(10) NOT NULL DEFAULT '0'
 users: ACTIVE_CONTEXT_EXTERNAL int(3) NOT NULL DEFAULT '0'
 users: ACTIVE_CONTEXT_UPDATED datetime
 users: ACTIVE_CONTEXT_HISTORY text
 users: COLOR char(20) NOT NULL DEFAULT ''
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
