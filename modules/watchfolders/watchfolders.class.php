<?php
/**
* Watchfolders 
*
* Watchfolders
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 23:01:07 [Jan 13, 2011])
*/
Define('DEF_SCRIPT_TYPE_OPTIONS', '0=Never|1=For every new file|2=Once (if any files were changed)'); // options for 'RUN SCRIPT'
//
//
class watchfolders extends module {
/**
* watchfolders
*
* Module class constructor
*
* @access private
*/
function watchfolders() {
  $this->name="watchfolders";
  $this->title="<#LANG_MODULE_WATCHFOLDERS#>";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
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
  if (IsSet($this->script_id)) {
   $out['IS_SET_SCRIPT_ID']=1;
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
 if ($this->data_source=='watchfolders' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_watchfolders') {
   $this->search_watchfolders($out);
  }
  if ($this->view_mode=='edit_watchfolders') {
   $this->edit_watchfolders($out, $this->id);
  }

  if ($this->view_mode=='check_watchfolders') {
   $this->checkWatchFolder($this->id);
   $this->redirect("?");
  }

  if ($this->view_mode=='delete_watchfolders') {
   $this->delete_watchfolders($this->id);
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
* Title
*
* Description
*
* @access public
*/
 function checkAllFolders() {
  $res=SQLSelect("SELECT ID FROM watchfolders WHERE CHECK_NEXT<NOW()");
  $total=count($res);
  for($i=0;$i<$total;$i++) {
   $this->checkWatchFolder($res[$i]['ID']);
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkWatchFolder($id, $no_script=0) {
  $rec=SQLSelectOne("SELECT * FROM watchfolders WHERE ID=".(int)$id);
  if (!$rec['ID']) {
   return 0;
  }

  $res=$this->getTree($rec['FOLDER'], $rec['CHECK_SUB'], $rec['CHECK_MASK']);


  $rec['CHECK_LATEST']=date('Y-m-d H:i:s');
  if (!$rec['CHECK_INTERVAL']) {
   $rec['CHECK_INTERVAL']=60;
  }
  $rec['CHECK_NEXT']=date('Y-m-d H:i:s', (time()+$rec['CHECK_INTERVAL']*60));


  if ($rec['CHECK_RESULTS']!=serialize($res)) {
   $files_updated=1;
  } else {
   $files_updated=0;
  }

  if ($rec['CHECK_RESULTS']) {
   $last_check_results=unserialize($rec['CHECK_RESULTS']);
  } else {
   $last_check_results=array();
  }
  
  $rec['CHECK_RESULTS']=serialize($res);

  SQLUpdate('watchfolders', $rec);

  if ($files_updated && !$no_script && $rec['SCRIPT_ID'] && $rec['SCRIPT_TYPE']) {

   //checking for updated files
   $new_files=array();
   foreach($res as $k=>$v) {
    if (!$last_check_results[$k] || $last_check_results[$k]['SIZE']!=$res[$k]['SIZE']) {
     $new_files[$k]=$v;
    }
   }

   //run script if set
   if ($rec['SCRIPT_TYPE']==2) {
    // run script for all files
      $params=array();
      $params['FOLDER']=$rec['FOLDER'];
      $params['FILES_UPDATED']=$new_files;
      runScript($rec['SCRIPT_ID'], $params);
   } elseif ($rec['SCRIPT_TYPE']==1) {
    // run script for every new file
    foreach($new_files as $k=>$v) {
      $params=array();
      $params['FOLDER']=$rec['FOLDER'];
      $params['FILENAME']=$k;
      runScript($rec['SCRIPT_ID'], $params);
    }
   }
  }

 }
/**
* watchfolders search
*
* @access public
*/
 function search_watchfolders(&$out) {
  require(DIR_MODULES.$this->name.'/watchfolders_search.inc.php');
 }
/**
* watchfolders edit/add
*
* @access public
*/
 function edit_watchfolders(&$out, $id) {
  require(DIR_MODULES.$this->name.'/watchfolders_edit.inc.php');
 }

//----------------------------
 function getTree($source, $recursive=0, $mask='') {

  $res=array();

  $orig_mask=$mask;

  if (!$mask) {
   $mask='*';
  }

  $mask=preg_quote($mask);
  $mask=str_replace('\*', '.*?', $mask);
  $mask='^'.$mask.'$';


  if (!Is_Dir($source)) {
   return 0; // incorrect source path
  }

 if ($dir = @opendir($source)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..') && $recursive) {
     $res2=$this->getTree($source."/".$file, $recursive, $orig_mask);
     foreach($res2 as $k=>$v) {
      $res[$k]=$v;
     }
    } elseif (Is_File($source."/".$file) && preg_match('/'.$mask.'/', $file)) {
     $res[$source."/".$file]=array('FILENAME'=>$file, 'SIZE'=>filesize($source."/".$file), 'MTIME'=>filemtime($source."/".$file));
    }
  }   
  closedir($dir); 
 }
 return $res;
 }


/**
* watchfolders delete record
*
* @access public
*/
 function delete_watchfolders($id) {
  $rec=SQLSelectOne("SELECT * FROM watchfolders WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM watchfolders WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS watchfolders');
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
watchfolders - Watchfolders
*/
  $data = <<<EOD
 watchfolders: ID int(10) unsigned NOT NULL auto_increment
 watchfolders: TITLE varchar(255) NOT NULL DEFAULT ''
 watchfolders: FOLDER varchar(255) NOT NULL DEFAULT ''
 watchfolders: CHECK_MASK varchar(255) NOT NULL DEFAULT ''
 watchfolders: CHECK_LATEST datetime
 watchfolders: CHECK_NEXT datetime
 watchfolders: CHECK_INTERVAL int(255) NOT NULL DEFAULT '0'
 watchfolders: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 watchfolders: CHECK_SUB int(3) NOT NULL DEFAULT '0'
 watchfolders: SCRIPT_TYPE int(255) NOT NULL DEFAULT '0'
 watchfolders: CHECK_RESULTS longtext

EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDEzLCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>