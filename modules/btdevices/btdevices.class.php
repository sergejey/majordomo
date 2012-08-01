<?
/**
* btdevices 
*
* btdevices
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 15:03:07 [Mar 27, 2009])
*/
//
//
class btdevices extends module {
/**
* btdevices
*
* Module class constructor
*
* @access private
*/
function btdevices() {
  $this->name="btdevices";
  $this->title="<#LANG_MODULE_BT_DEVICES#>";
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
 if ($this->data_source=='btdevices' || $this->data_source=='') {

 if ($this->mode=='delete_unknown') {
  SQLExec("DELETE FROM btdevices WHERE USER_ID=0");
  $this->redirect("?");
 }

 if ($this->mode=='delete_once') {
  SQLExec("DELETE FROM btdevices WHERE USER_ID=0 AND TO_DAYS(FIRST_FOUND)=TO_DAYS(LAST_FOUND)");
  $this->redirect("?");
 }


  if ($this->view_mode=='' || $this->view_mode=='search_btdevices') {
   $this->search_btdevices($out);
  }
  if ($this->view_mode=='edit_btdevices') {
   global $id;
   $this->edit_btdevices($out, $id);
  }

  if ($this->view_mode=='delete_btdevices') {
   $this->delete_btdevices($this->id);
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

}
/**
* btdevices search
*
* @access public
*/
 function search_btdevices(&$out) {

  $btdevices=SQLSelect("SELECT btdevices.*, users.NAME FROM btdevices LEFT JOIN users ON btdevices.USER_ID=users.ID ORDER BY LAST_FOUND DESC");
  if ($btdevices[0]['ID']) {
   $out['DEVICES']=$btdevices;
  }

 }
/**
* btdevices edit/add
*
* @access public
*/
 function edit_btdevices(&$out, $id) {
  $rec=SQLSelectOne("SELECT * FROM btdevices WHERE ID='".(int)$id."'");
  if ($this->mode=='update') {
   global $title;
   global $user_id;
   $rec['TITLE']=$title;
   $rec['USER_ID']=$user_id;
   SQLUpdate('btdevices', $rec);
   $this->redirect("?");
  }
  $rec['LOG']=nl2br($rec['LOG']);
  outHash($rec, $out);
  $out['USERS']=SQLSelect("SELECT * FROM users ORDER BY NAME");
 }
/**
* btdevices delete record
*
* @access public
*/
 function delete_btdevices($id) {
  $rec=SQLSelectOne("SELECT * FROM btdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM btdevices WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS btdevices');
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
btdevices - btdevices
*/
  $data = <<<EOD
 btdevices: ID int(10) unsigned NOT NULL auto_increment
 btdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 btdevices: MAC varchar(255) NOT NULL DEFAULT ''
 btdevices: LOG text NOT NULL DEFAULT ''
 btdevices: LAST_FOUND datetime
 btdevices: FIRST_FOUND datetime
 btdevices: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 btdevices: USER_ID int(10) NOT NULL DEFAULT '0'
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