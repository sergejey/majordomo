<?php
/**
* jobs 
*
* jobs
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 15:03:07 [Mar 27, 2009])
*/
//
//
class jobs extends module {
/**
* jobs
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="jobs";
  $this->title="<#LANG_MODULE_JOBS#>";
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
 if ($this->data_source=='jobs' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_jobs') {
   $this->search_jobs($out);
  }
  if ($this->view_mode=='delete_jobs') {
   $this->delete_jobs($this->id);
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
* jobs search
*
* @access public
*/
 function search_jobs(&$out) {
  //require(DIR_MODULES.$this->name.'/jobs_search.inc.php');
  if ($this->mode=='add') {
   global $commands;
   global $runin;
   global $runtype;
   global $title;
   if ($commands!='' && $title!='') {
    if ($runtype=='m') {
     $runin=$runin*60;
    } elseif ($runtype=='h') {
     $runin=$runin*60*60;
    }
    setTimeOut($title, $commands, $runin);
    $this->redirect("?");
   }
  }

  if ($this->mode=='cancel') {
   global $id;
   deleteScheduledJob($id);
   $this->redirect("?");
  }

  $jobs=SQLSelect("SELECT * FROM jobs ORDER BY RUNTIME");
  if ($jobs[0]['ID']) {
   $out['JOBS']=$jobs;
  }

 }
/**
* jobs edit/add
*
* @access public
*/
 function edit_jobs(&$out, $id) {
  
 }
/**
* jobs delete record
*
* @access public
*/
 function delete_jobs($id) {
  $rec=SQLSelectOne("SELECT * FROM jobs WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM jobs WHERE ID='".$rec['ID']."'");
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
  SQLExec("UPDATE project_modules SET HIDDEN=1 WHERE NAME LIKE '".$this->name."'");
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
   SQLDropTable('jobs');
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
jobs - jobs
*/
  $data = <<<EOD
 jobs: ID int(10) unsigned NOT NULL auto_increment
 jobs: TITLE varchar(255) NOT NULL DEFAULT ''
 jobs: COMMANDS text
 jobs: RUNTIME datetime
 jobs: EXPIRE datetime
 jobs: STARTED datetime
 jobs: PROCESSED int(3) NOT NULL DEFAULT '0'
 jobs: EXPIRED int(3) NOT NULL DEFAULT '0'
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