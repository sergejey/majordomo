<?
/**
* Layouts 
*
* Layouts
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 18:09:58 [Sep 10, 2010])
*/
Define('DEF_TYPE_OPTIONS', 'html=HTML Code|app=Application|url=URL'); // options for 'TYPE' //page=Page|
//
//
class layouts extends module {
/**
* layouts
*
* Module class constructor
*
* @access private
*/
function layouts() {
  $this->name="layouts";
  $this->title="<#LANG_MODULE_LAYOUTS#>";
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

  if ($this->mobile) {
   $out['MOBILE']=1;
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
 if ($this->data_source=='layouts' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_layouts') {
   $this->search_layouts($out);
  }
  if ($this->view_mode=='edit_layouts') {
   $this->edit_layouts($out, $this->id);
  }
  if ($this->view_mode=='view_layouts') {
   $this->view_layouts($out, $this->id);
  }
  if ($this->view_mode=='delete_layouts') {
   $this->delete_layouts($this->id);
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
* layouts search
*
* @access public
*/
 function search_layouts(&$out) {
  require(DIR_MODULES.$this->name.'/layouts_search.inc.php');
 }
/**
* layouts edit/add
*
* @access public
*/
 function edit_layouts(&$out, $id) {
  require(DIR_MODULES.$this->name.'/layouts_edit.inc.php');
 }

/**
* Title
*
* Description
*
* @access public
*/
 function view_layouts(&$out, $id) {
  $rec=SQLSelectOne("SELECT * FROM layouts WHERE ID='".(int)$id."'");
  if (!$rec['ID']) {
   return 0;
  }
  outHash($rec, $out);
 }

/**
* layouts delete record
*
* @access public
*/
 function delete_layouts($id) {
  $rec=SQLSelectOne("SELECT * FROM layouts WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM layouts WHERE ID='".$rec['ID']."'");
  @unlink(ROOT.'cms/layouts/'.$rec['ID'].'.html');
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
  SQLExec('DROP TABLE IF EXISTS layouts');
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
layouts - Layouts
*/
  $data = <<<EOD
 layouts: ID int(10) unsigned NOT NULL auto_increment
 layouts: TITLE varchar(255) NOT NULL DEFAULT ''
 layouts: PRIORITY int(10) NOT NULL DEFAULT '0'
 layouts: TYPE varchar(255) NOT NULL DEFAULT ''
 layouts: CODE text
 layouts: APP varchar(255) NOT NULL DEFAULT ''
 layouts: URL char(255) NOT NULL DEFAULT ''
 layouts: REFRESH int(10) NOT NULL DEFAULT '0'
 layouts: DETAILS text
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDEwLCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>