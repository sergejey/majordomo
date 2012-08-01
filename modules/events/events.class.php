<?
/**
* Events 
*
* Events
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 15:03:07 [Mar 27, 2009])
*/
//
//
class events extends module {
/**
* events
*
* Module class constructor
*
* @access private
*/
function events() {
  $this->name="events";
  $this->title="Events";
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
 if ($this->data_source=='events' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_events') {
   $this->search_events($out);
  }
  if ($this->view_mode=='edit_events') {
   $this->edit_events($out, $this->id);
  }
  if ($this->view_mode=='delete_events') {
   $this->delete_events($this->id);
   $this->redirect("?");
  }
  if ($this->view_mode=='multiple_events') {
   global $ids;
   if (is_array($ids)) {
    $total_selected=count($ids);
    global $delete;
    for($i=0;$i<$total_selected;$i++) {
     $id=$ids[$i];
     if ($delete) {
      // operation: DELETE
      $this->delete_events($id);
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
 global $session;

 if ($this->action=='addevent') {

  global $mode;
  $this->mode=$mode;

  if ($this->mode=='update') {
   global $type;
   global $window;
   global $details;
   global $terminal_to;
   global $user_to;
   $event=array();
   $event['EVENT_TYPE']=$type;
   $event['WINDOW']=$window;
   $event['DETAILS']=$details;
   $event['TERMINAL_TO']=$terminal_to;
   $event['TERMINAL_FROM']=$session->data['TERMINAL'];
   $event['USER_TO']=$user_to;
   $event['USER_FROM']=$session->data['USERNAME'];
   $event['ADDED']=date('Y-m-d H:i:s');
   $event['EXPIRE']=date('Y-m-d H:i:s', time()+5*60); //5 minutes expire
   SQLInsert('events', $event);
  }

   $terminals=SQLSelect("SELECT * FROM terminals ORDER BY TITLE");
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    if ($terminals[$i]['NAME']==$session->data['TERMINAL']) {
     $terminals[$i]['SELECTED']=1;
     $out['TERMINAL_TITLE']=$terminals[$i]['TITLE'];
    }
   }
   $out['TERMINALS']=$terminals;

   $users=SQLSelect("SELECT * FROM users ORDER BY NAME");
   $total=count($users);
   for($i=0;$i<$total;$i++) {
    if ($users[$i]['USERNAME']==$session->data['USERNAME']) {
     $users[$i]['SELECTED']=1;
     $out['USER_TITLE']=$users[$i]['NAME'];
    }
   }
   $out['USERS']=$users;

 }

 if ($this->action=='getnextevent') {
  if (!$session->data['TERMINAL']) {
   $session->data['TERMINAL']='temp'.date('YmdHis');
  }
  //echo "next event for ".$session->data['USERNAME']." on ".$session->data['TERMINAL'];//.date('H:i:s')
  SQLExec("DELETE FROM events WHERE EXPIRE<NOW() AND EVENT_TYPE!='system'");
  $qry="1";
  //$qry.=" AND TERMINAL_FROM!='".DBSafe($session->data['TERMINAL'])."'";
  $qry.=" AND EVENT_TYPE!='system'";
  $qry.=" AND PROCESSED=0";
  $qry.=" AND (TERMINAL_TO='*' OR TERMINAL_TO='".DBSafe($session->data['TERMINAL'])."')";
  $qry.=" AND (USER_TO='*' OR USER_TO='".DBSafe($session->data['USERNAME'])."')";
  $event=SQLSelectOne("SELECT * FROM events WHERE $qry ORDER BY ADDED");
  if ($event['ID']) {
   $res=$event['ID'].'|'.$event['EVENT_TYPE'].'|'.$event['WINDOW'].'|'.str_replace("\n", '\n', $event['DETAILS']);
   echo $res;
   $event['PROCESSED']=1;
   SQLUpdate('events', $event);
  }
  exit;
 }
}
/**
* events search
*
* @access public
*/
 function search_events(&$out) {
  require(DIR_MODULES.$this->name.'/events_search.inc.php');
 }
/**
* events edit/add
*
* @access public
*/
 function edit_events(&$out, $id) {
  require(DIR_MODULES.$this->name.'/events_edit.inc.php');
 }
/**
* events delete record
*
* @access public
*/
 function delete_events($id) {
  $rec=SQLSelectOne("SELECT * FROM events WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM events WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS events');
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
events - Events
*/
  $data = <<<EOD
 events: ID int(10) unsigned NOT NULL auto_increment
 events: EVENT_NAME varchar(255) NOT NULL DEFAULT ''
 events: EVENT_TYPE char(10) NOT NULL DEFAULT ''
 events: TERMINAL_FROM varchar(255) NOT NULL DEFAULT ''
 events: TERMINAL_TO varchar(255) NOT NULL DEFAULT ''
 events: USER_FROM varchar(255) NOT NULL DEFAULT ''
 events: USER_TO varchar(255) NOT NULL DEFAULT ''
 events: WINDOW varchar(255) NOT NULL DEFAULT ''
 events: DETAILS text
 events: ADDED datetime
 events: EXPIRE datetime
 events: PROCESSED int(3) NOT NULL DEFAULT '0'
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