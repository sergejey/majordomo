<?php
/**
* App_quotes 
*
* App_quotes
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 13:02:00 [Feb 09, 2013])
*/
//
//
class app_quotes extends module {
/**
* app_quotes
*
* Module class constructor
*
* @access private
*/
function app_quotes() {
  $this->name="app_quotes";
  $this->title="<#LANG_APP_QUOTES#>";
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
 if ($this->data_source=='app_quotes' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_app_quotes') {
   $this->search_app_quotes($out);
  }
  if ($this->view_mode=='edit_app_quotes') {
   $this->edit_app_quotes($out, $this->id);
  }
  if ($this->view_mode=='delete_app_quotes') {
   $this->delete_app_quotes($this->id);
   $this->redirect("?");
  }
  if ($this->view_mode=='import_app_quotes') {
   $this->import_app_quotes($out);
  }
  if ($this->view_mode=='multiple_app_quotes') {
   global $ids;
   if (is_array($ids)) {
    $total_selected=count($ids);
    global $delete;
    global $export;
    if ($export) {
     $this->export_app_quotes($ids);
    }
    for($i=0;$i<$total_selected;$i++) {
     $id=$ids[$i];
     if ($delete) {
      // operation: DELETE
      $this->delete_app_quotes($id);
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

 if ($this->order) {
  $order='ID DESC';
 } else {
  $order='RAND()';
 }

  global $session;
  if (!$session->data['SEEN_QUOTES']) {
   $session->data['SEEN_QUOTES']='0';
  }
   $res=SQLSelectOne("SELECT * FROM app_quotes WHERE ID NOT IN (".$session->data['SEEN_QUOTES'].") ORDER BY ".$order." LIMIT 1");
   if (!$res['ID']) {
    $session->data['SEEN_QUOTES']='0';
    $res=SQLSelectOne("SELECT * FROM app_quotes WHERE ID NOT IN (".$session->data['SEEN_QUOTES'].") ORDER BY ".$order." LIMIT 1");
   }
   if ($res['ID']) {
    $session->data['SEEN_QUOTES'].=','.$res['ID'];
   }
   $session->save();

 if ($res['ID']) {
  $out['BODY']=$res['BODY']; //.' '.$session->data['SEEN_QUOTES']
 }


}
/**
* app_quotes search
*
* @access public
*/
 function search_app_quotes(&$out) {
  require(DIR_MODULES.$this->name.'/app_quotes_search.inc.php');
 }
/**
* app_quotes edit/add
*
* @access public
*/
 function edit_app_quotes(&$out, $id) {
  require(DIR_MODULES.$this->name.'/app_quotes_edit.inc.php');
 }
/**
* app_quotes data import
*
* @access public
*/
 function import_app_quotes(&$out) {
  require(DIR_MODULES.$this->name.'/app_quotes_import.inc.php');
 }
/**
* app_quotes data import
*
* @access public
*/
 function export_app_quotes($ids) {
  require(DIR_MODULES.$this->name.'/app_quotes_export.inc.php');
 }
/**
* app_quotes delete record
*
* @access public
*/
 function delete_app_quotes($id) {
  $rec=SQLSelectOne("SELECT * FROM app_quotes WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM app_quotes WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS app_quotes');
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
app_quotes - Quotes
*/
  $data = <<<EOD
 app_quotes: ID int(10) unsigned NOT NULL auto_increment
 app_quotes: BODY text
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDA5LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>