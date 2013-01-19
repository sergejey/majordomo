<?
/**
* App_readit 
*
* App_readit
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 14:01:17 [Jan 19, 2013])
*/
//
//
class app_readit extends module {
/**
* app_readit
*
* Module class constructor
*
* @access private
*/
function app_readit() {
  $this->name="app_readit";
  $this->title="<#LANG_APP_READIT#>";
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
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
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
  global $data_source;
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
  if (isset($data_source)) {
   $this->data_source=$data_source;
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
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if (IsSet($this->sys_id)) {
   $out['IS_SET_SYS_ID']=1;
  }
  if (IsSet($this->channel_id)) {
   $out['IS_SET_CHANNEL_ID']=1;
  }
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
 if ($this->data_source=='readit_urls' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_readit_urls') {
   $this->search_readit_urls($out);
  }
  if ($this->view_mode=='edit_readit_urls') {
   $this->edit_readit_urls($out, $this->id);
  }
  if ($this->view_mode=='delete_readit_urls') {
   $this->delete_readit_urls($this->id);
   $this->redirect("?data_source=readit_urls");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='readit_channels') {
  if ($this->view_mode=='' || $this->view_mode=='search_readit_channels') {
   $this->search_readit_channels($out);
  }
  if ($this->view_mode=='edit_readit_channels') {
   $this->edit_readit_channels($out, $this->id);
  }
  if ($this->view_mode=='delete_readit_channels') {
   $this->delete_readit_channels($this->id);
   $this->redirect("?data_source=readit_channels");
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

 global $ajax;
 if ($ajax) {
  global $op;
  if (!headers_sent()) {
   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');
  }


  if ($op=='delete') {
   global $id;
   SQLExec("DELETE FROM readit_urls WHERE ID='".(int)$id."'");
   echo "OK";
  }

  if ($op=='add') {
   global $url;
   global $title;
   global $channel_id;
   if (!$title) {
    $title=str_replace('http://', '', $url);
    $title=preg_replace('/\/$/', '', $title);
   }
   $rec=array();
   $rec['TITLE']=$title;
   $rec['URL']=$url;
   $rec['CHANNEL_ID']=$channel_id;
   $rec['ADDED']=date('Y-m-d H:i:s');
   SQLInsert('readit_urls', $rec);
   echo "OK";
  }

  if ($op=='geturls') {
   global $channel_id;
   $qry=1;
   if ($channel_id=='favorite') {
    $qry.=" AND FAVORITE=1";
   } elseif ($channel_id) {
    $qry.=" AND CHANNEL_ID='".(int)$channel_id."'";
   }
   $urls=SQLSelect("SELECT * FROM readit_urls WHERE $qry ORDER BY ID DESC LIMIT 50");
   $total=count($urls);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     $urls[$i]['TITLE_SAFE']=addcslashes($urls[$i]['TITLE'], "'");
    }
   } else {
    $urls=array();
   }
   $data['URLS']=$urls;
   echo json_encode($data);
  }  

  exit;
 }

 $out['CHANNELS']=SQLSelect("SELECT * FROM readit_channels ORDER BY TITLE");
}
/**
* readit_urls search
*
* @access public
*/
 function search_readit_urls(&$out) {
  require(DIR_MODULES.$this->name.'/readit_urls_search.inc.php');
 }
/**
* readit_urls edit/add
*
* @access public
*/
 function edit_readit_urls(&$out, $id) {
  require(DIR_MODULES.$this->name.'/readit_urls_edit.inc.php');
 }
/**
* readit_urls delete record
*
* @access public
*/
 function delete_readit_urls($id) {
  $rec=SQLSelectOne("SELECT * FROM readit_urls WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM readit_urls WHERE ID='".$rec['ID']."'");
 }
/**
* readit_channels search
*
* @access public
*/
 function search_readit_channels(&$out) {
  require(DIR_MODULES.$this->name.'/readit_channels_search.inc.php');
 }
/**
* readit_channels edit/add
*
* @access public
*/
 function edit_readit_channels(&$out, $id) {
  require(DIR_MODULES.$this->name.'/readit_channels_edit.inc.php');
 }
/**
* readit_channels delete record
*
* @access public
*/
 function delete_readit_channels($id) {
  $rec=SQLSelectOne("SELECT * FROM readit_channels WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM readit_channels WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS readit_urls');
  SQLExec('DROP TABLE IF EXISTS readit_channels');
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
readit_urls - ReadIt URLs
readit_channels - ReadIt Channels
*/
  $data = <<<EOD
 readit_urls: ID int(10) unsigned NOT NULL auto_increment
 readit_urls: URL char(255) NOT NULL DEFAULT ''
 readit_urls: TITLE varchar(255) NOT NULL DEFAULT ''
 readit_urls: FAVORITE int(3) NOT NULL DEFAULT '0'
 readit_urls: ADDED datetime
 readit_urls: SYS_ID varchar(255) NOT NULL DEFAULT ''
 readit_urls: CHANNEL_ID int(10) NOT NULL DEFAULT '0'
 readit_channels: ID int(10) unsigned NOT NULL auto_increment
 readit_channels: TITLE varchar(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDE5LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>