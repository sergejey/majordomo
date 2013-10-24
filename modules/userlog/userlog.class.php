<?php
/**
* User Log 
*
* User Log
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 15:10:45 [Oct 26, 2006])
*/
//
//
class userlog extends module {
/**
* userlog
*
* Module class constructor
*
* @access private
*/
function userlog() {
  $this->name="userlog";
  $this->title="<#LANG_MODULE_USERLOG#>";
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
  if (IsSet($this->user_id)) {
   $out['IS_SET_USER_ID']=1;
  }
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}

function newEntry($msg, $check_last=0) {
 global $session;

 $rec=array();
 //($session->data['USER_NAME']);
 $rec['USER_ID']=(int)($session->data['USER_ID']);
 $rec['MESSAGE']=$msg;
 $rec['IP']=$_SERVER['REMOTE_ADDR'];
 if (!$check_last) {
  SQLInsert('userlog', $rec);
 } else {
  $tmp=SQLSelectOne("SELECT * FROM userlog WHERE USER_ID='".$rec['USER_ID']."' AND MESSAGE='".DBSafe($rec['MESSAGE'])."' ORDER BY ID DESC LIMIT 1");
  if ($tmp['ID']) {
   $rec['ID']=$tmp['ID'];
   SQLUpdate('userlog', $rec);
  } else {
   SQLInsert('userlog', $rec);
  }
 }
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
 if ($this->data_source=='userlog' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_userlog') {
   $this->search_userlog($out);
   $this->calendar_userlog($out, 'ADDED');
  }
  if ($this->view_mode=='delete_userlog') {
   $this->delete_userlog($this->id);
   $this->redirect("?");
  }
  if ($this->view_mode=='multiple_userlog') {
   global $ids;
   if (is_array($ids)) {
    $total_selected=count($ids);
    global $delete;
    global $export;
    if ($export) {
     $this->export_userlog($ids);
    }
    for($i=0;$i<$total_selected;$i++) {
     $id=$ids[$i];
     if ($delete) {
      // operation: DELETE
      $this->delete_userlog($id);
     }
    }
   }
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
* userlog search
*
* @access public
*/
 function search_userlog(&$out) {
  require(DIR_MODULES.$this->name.'/userlog_search.inc.php');
 }
/**
* userlog data import
*
* @access public
*/
 function export_userlog($ids) {
  require(DIR_MODULES.$this->name.'/userlog_export.inc.php');
 }
/**
* userlog calendar
*
* @access public
*/
 function calendar_userlog(&$out, $field='') {
  require(DIR_MODULES.$this->name.'/userlog_calendar.inc.php');
 }
/**
* userlog delete record
*
* @access public
*/
 function delete_userlog($id) {
  $rec=SQLSelectOne("SELECT * FROM userlog WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM userlog WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS userlog');
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
userlog - User Log
*/
  $data = <<<EOD
 userlog: ID int(10) unsigned NOT NULL auto_increment
 userlog: USER_ID int(10) NOT NULL DEFAULT '0'
 userlog: MESSAGE varchar(100) NOT NULL DEFAULT ''
 userlog: IP varchar(20) NOT NULL DEFAULT ''
 userlog: ADDED timestamp(14)
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgT2N0IDI2LCAyMDA2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>