<?php
/**
* Shoutbox 
*
* Shoutbox
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 23:01:48 [Jan 30, 2007])
*/
//
//
class shoutbox extends module {
/**
* shoutbox
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="shoutbox";
  $this->title="<#LANG_MODULE_SHOUTBOX#>";
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
function saveParams($data=1) {
 $data=array();
 if (IsSet($this->id)) {
  $data["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $data["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $data["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $data["tab"]=$this->tab;
 }
 return parent::saveParams($data);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams($data = 1) {
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

 global $session;

 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }

 global $delete_room;
 if ($delete_room!='' && LOGGED_USER) {
  $room=SQLSelectOne("SELECT * FROM shoutrooms WHERE ID='".(int)$delete_room."' AND ADDED_BY=".(int)$session->data['SITE_USER_ID']);
  if ($room['ID']) {
   SQLExec("DELETE FROM shouts WHERE ROOM_ID=".$room['ID']);
   SQLExec("DELETE FROM shoutrooms WHERE ID='".$room['ID']."'");
  }
  unset($session->data['SHOUT_ROOM_ID']);
  $this->redirect("/chat.html");
 }
 
 global $change_visibility;
 if ($change_visibility && LOGGED_USER)
 {
        $room=SQLSelectOne("SELECT * FROM shoutrooms WHERE ID='".(int)$change_visibility."' AND ADDED_BY=".LOGGED_USER_ID);
        if ($room['ID'])
        {
                $room['IS_PUBLIC'] = $room['IS_PUBLIC']?0:1;
                SQLUpdate('shoutrooms',$room);
        }
        $this->redirect("/chat/room".$room['ID'].'.html');
 }
 
 if ($this->mode=='newroom' && $session->data['LOGGED_USER_ID']) {
  $rec=array();
  global $room_title;
  global $make_public;
  $rec['TITLE']=htmlspecialchars($room_title);
  $rec['ADDED_BY']=$session->data['LOGGED_USER_ID'];
  $rec['ADDED']=date('Y-m-d H:i:s');
  $rec['IS_PUBLIC']=(int)$make_public;
  if ($rec['TITLE']) {
   $rec['ID']=SQLInsert('shoutrooms', $rec);
   $this->redirect("/chat/room".$rec['ID'].'.html');
  }
 }

 if ($this->data_source=='shouts' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_shouts') {
   $this->search_shouts($out);

  }
  if ($this->view_mode=='delete_shouts') {
   $this->delete_shouts($this->id);
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
 SQLExec("DELETE FROM shouts WHERE (TO_DAYS(NOW())-TO_DAYS(ADDED))>7");
 $this->admin($out);
}
/**
* shouts search
*
* @access public
*/
 function search_shouts(&$out) {
  require(DIR_MODULES.$this->name.'/shouts_search.inc.php');
 }
/**
* shouts delete record
*
* @access public
*/
 function delete_shouts($id) {
  $rec=SQLSelectOne("SELECT * FROM shouts WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM shouts WHERE ID='".$rec['ID']."'");
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
   SQLDropTable('shouts');
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
shouts - Shoutbox
*/
  $data = <<<EOD
 shouts: ID int(10) unsigned NOT NULL auto_increment
 shouts: ROOM_ID int(10) NOT NULL DEFAULT '0'
 shouts: MEMBER_ID int(10) NOT NULL DEFAULT '0'
 shouts: MESSAGE varchar(255) NOT NULL DEFAULT ''
 shouts: IMPORTANCE int(10) NOT NULL DEFAULT '0'
 shouts: ADDED datetime
 shouts: SOURCE varchar(255) NOT NULL DEFAULT ''
 shouts: IMAGE varchar(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDMwLCAyMDA3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
