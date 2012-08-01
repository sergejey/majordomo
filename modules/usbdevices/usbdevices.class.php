<?
/**
* usbdevices 
*
* usbdevices
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 15:03:07 [Mar 27, 2009])
*/
//
//
class usbdevices extends module {
/**
* usbdevices
*
* Module class constructor
*
* @access private
*/
function usbdevices() {
  $this->name="usbdevices";
  $this->title="usbdevices";
  $this->module_category="CMS";
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

 if ($this->mode=='delete_unknown') {
  SQLExec("DELETE FROM usbdevices WHERE USER_ID=0");
  $this->redirect("?");
 }


 if ($this->data_source=='usbdevices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_usbdevices') {
   $this->search_usbdevices($out);
  }
  if ($this->view_mode=='edit_usbdevices') {
   global $id;
   $this->edit_usbdevices($out, $id);
  }

  if ($this->view_mode=='delete_usbdevices') {
   $this->delete_usbdevices($this->id);
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
* usbdevices search
*
* @access public
*/
 function search_usbdevices(&$out) {

  $usbdevices=SQLSelect("SELECT usbdevices.*, users.NAME FROM usbdevices LEFT JOIN users ON usbdevices.USER_ID=users.ID ORDER BY LAST_FOUND DESC");
  if ($usbdevices[0]['ID']) {
   $out['DEVICES']=$usbdevices;
  }

 }
/**
* usbdevices edit/add
*
* @access public
*/
 function edit_usbdevices(&$out, $id) {
  $rec=SQLSelectOne("SELECT * FROM usbdevices WHERE ID='".(int)$id."'");
  if ($this->mode=='update') {
   global $title;
   global $user_id;
   global $script;
   $rec['TITLE']=$title;
   $rec['SCRIPT']=trim($script);

   global $run_type;

       if ($run_type=='script') {
        global $script_id;
        $rec['SCRIPT_ID']=$script_id;
       } else {
        $rec['SCRIPT_ID']=0;
       }


   if ($rec['SCRIPT']!='' && $run_type=='code') {
    //echo $content;
    $errors=php_syntax_error($rec['SCRIPT']);
    if ($errors) {
     $out['ERR_SCRIPT']=1;
     $out['ERRORS']=nl2br($errors);
     $ok=0;
    }
   }

   $rec['USER_ID']=$user_id;
   SQLUpdate('usbdevices', $rec);
   $this->redirect("?");
  }
  $rec['LOG']=nl2br($rec['LOG']);
  outHash($rec, $out);
  $out['USERS']=SQLSelect("SELECT * FROM users ORDER BY NAME");
  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
 }
/**
* usbdevices delete record
*
* @access public
*/
 function delete_usbdevices($id) {
  $rec=SQLSelectOne("SELECT * FROM usbdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM usbdevices WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS usbdevices');
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
usbdevices - usbdevices
*/
  $data = <<<EOD
 usbdevices: ID int(10) unsigned NOT NULL auto_increment
 usbdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 usbdevices: SERIAL varchar(255) NOT NULL DEFAULT ''
 usbdevices: LOG text NOT NULL DEFAULT ''
 usbdevices: SCRIPT text NOT NULL DEFAULT ''
 usbdevices: SCRIPT_ID int(10) unsigned NOT NULL DEFAULT '0'
 usbdevices: LAST_FOUND datetime
 usbdevices: FIRST_FOUND datetime
 usbdevices: USER_ID int(10) NOT NULL DEFAULT '0'
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