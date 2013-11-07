<?php
/**
* General Settings 
*
* Settings
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 23:11:54 [Nov 12, 2006])
*/
//
//
class settings extends module {
/**
* settings
*
* Module class constructor
*
* @access private
*/
 function settings() {
  $this->name="settings";
  $this->title="<#LANG_MODULE_SETTINGS#>";
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
 global $updated;

 if ($updated) {
  $out['UPDATED']=1;
 }

 if ($this->data_source=='settings' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_settings') {
   $this->search_settings($out);
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
* settings search
*
* @access public
*/
 function search_settings(&$out) {
  require(DIR_MODULES.$this->name.'/settings_search.inc.php');
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  if (!Is_Dir(ROOT."./settings")) {
   mkdir(ROOT."./settings", 0777);
  }
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
  SQLExec('DROP TABLE IF EXISTS settings');
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
$data = <<<EOD

 // (description:settings) Web-site settings table
 settings: ID int(10) unsigned NOT NULL auto_increment   // Record ID
 settings: PRIORITY int(3) unsigned NOT NULL DEFAULT '0' // Setting Priority
 settings: HR int(3) unsigned NOT NULL DEFAULT '0'       // Separator flag (1=<hr>)
 settings: TITLE varchar(255) NOT NULL DEFAULT ''        // Setting Title
 settings: NAME varchar(50) NOT NULL DEFAULT ''          // Setting system name
 settings: TYPE varchar(59) NOT NULL DEFAULT ''          // Setting value type
 settings: NOTES text NOT NULL DEFAULT ''                // Setting Notes / Description
 settings: VALUE varchar(255) NOT NULL DEFAULT ''        // Setting Value
 settings: DEFAULTVALUE varchar(255) NOT NULL DEFAULT '' // Setting Default Value
 settings: URL varchar(255) NOT NULL DEFAULT ''          // URL for more details
 settings: URL_TITLE varchar(255) NOT NULL DEFAULT ''    // URL description

EOD;

parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDEyLCAyMDA2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>