<?php
/**
* Layouts 
*
* Layouts
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 18:09:58 [Sep 10, 2010])
*/
Define('DEF_TYPE_OPTIONS', 'html=HTML Code|app=Application|url=URL|dashboard=Dashboard'); // options for 'TYPE' //page=Page|
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
function __construct() {
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

  if ($this->view_mode=='moveup' && $this->id) {
   $this->reorder_items($this->id, 'up');
   $this->redirect("?");
  }
  if ($this->view_mode=='movedown' && $this->id) {
   $this->reorder_items($this->id, 'down');
   $this->redirect("?");
  }

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
 if ($this->ajax) {
  global $op;
  global $id;
  if ($op=='loaddashboard') {
   $page_rec=SQLSelectOne("SELECT * FROM layouts WHERE ID=".(int)$id);
   echo $page_rec['DETAILS'];
  }
  if ($op=='savedashboard') {
   global $data;
   $page_rec=SQLSelectOne("SELECT * FROM layouts WHERE ID=".(int)$id);
   $page_rec['DETAILS']=$data;
   SQLUpdate('layouts',$page_rec);
  }
  exit;
 }
 if ($this->owner->action=='apps') {
  $this->redirect(ROOTHTML."pages.html");
 }
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
  $rec=SQLSelectOne("SELECT * FROM layouts WHERE ID='".(int)$id."' OR TITLE LIKE '".DBSafe($id)."'");
  if (!$rec['ID']) {
   if ($id=='Panel') {
    $rec=array();
    $rec['TITLE']='Panel';
    $rec['HIDDEN']=1;
    $rec['TYPE']='dashboard';
    $rec['ID']=SQLInsert('layouts',$rec);
   } else {
    return 0;
   }
  }
  if ($rec['TYPE']=='dashboard') {
   $url=ROOTHTML."3rdparty/freeboard/?layout_id=".$rec['ID'];
   if ($_GET['theme']) {
    $url.="&theme=".$_GET['theme'];
   } elseif ($rec['THEME']) {
    $url.="&theme=".$rec['THEME'];
   }
   if ($rec['BACKGROUND_IMAGE']) {
    $url.="&background_image=".urlencode($rec['BACKGROUND_IMAGE']);
   }
   echo "<head>      
    <title>Page</title>      
    <meta http-equiv=\"refresh\" content=\"0;URL='".$url."'\" />    
  </head><body>Redirecting...</body>";
   exit;
   //$this->redirect($url);
  }
  if ($rec['TYPE']=='url') {
   $this->redirect($rec['URL']);
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

 function reorder_items($id, $direction='up') {
  $element=SQLSelectOne("SELECT * FROM layouts WHERE ID='".(int)$id."'");
  $all_elements=SQLSelect("SELECT * FROM layouts WHERE 1 ORDER BY PRIORITY DESC, TITLE");
  $total=count($all_elements);
  for($i=0;$i<$total;$i++) {
   if ($all_elements[$i]['ID']==$id && $i>0 && $direction=='up') {
    $tmp=$all_elements[$i-1];
    $all_elements[$i-1]=$all_elements[$i];
    $all_elements[$i]=$tmp;
    break;
   }
   if ($all_elements[$i]['ID']==$id && $i<($total-1) && $direction=='down') {
    $tmp=$all_elements[$i+1];
    $all_elements[$i+1]=$all_elements[$i];
    $all_elements[$i]=$tmp;
    break;
   }
  }
  $priority=($total)*10;
  for($i=0;$i<$total;$i++) {
   $all_elements[$i]['PRIORITY']=$priority;
   $priority-=10;
   SQLUpdate('layouts', $all_elements[$i]);
  }
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
   SQLDropTable('layouts');
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
layouts - Layouts
*/
  $data = <<<EOD
 layouts: ID int(10) unsigned NOT NULL auto_increment
 layouts: TITLE varchar(255) NOT NULL DEFAULT ''
 layouts: PRIORITY int(10) NOT NULL DEFAULT '0'
 layouts: TYPE varchar(255) NOT NULL DEFAULT ''
 layouts: CODE text
 layouts: APP varchar(255) NOT NULL DEFAULT ''
 layouts: ICON varchar(50) NOT NULL DEFAULT ''
 layouts: URL char(255) NOT NULL DEFAULT ''
 layouts: REFRESH int(10) NOT NULL DEFAULT '0'
 layouts: DETAILS text
 layouts: HIDDEN int(3) NOT NULL DEFAULT '0'
 layouts: BACKGROUND_IMAGE varchar(255) NOT NULL DEFAULT ''
 layouts: THEME varchar(50) NOT NULL DEFAULT ''
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