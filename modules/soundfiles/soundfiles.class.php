<?php
/**
* soundfiles 
*
* soundfiles
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 15:09:42 [Sep 30, 2014])
*/
//
//
class soundfiles extends module {
/**
* textfiles
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="soundfiles";
  $this->title="<#LANG_MODULE_SOUNDFILES#>";
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

 global $file;

 if ($this->mode=='update') {

   global $file;
   global $file_name;
   if ($file!='' && preg_match('/\.mp3/is', $file_name)) {
    copy($file, ROOT.'cms/sounds/'.strtolower($file_name));
    $out['OK']=1;
   }
  
 }

 if ($this->view_mode=='') {

  $files=array();

 $dir=ROOT.'cms/sounds';
 $handle = opendir( $dir );
 while ( false !== $thing = readdir( $handle ) ) { 
  if( $thing == '.' || $thing == '..' ) continue;
  if (preg_match('/(.+?)\.mp3$/', $thing, $m))  {
   $files[]=array('FILENAME'=>$m[1], 'FILENAME_URL'=>urlencode($m[1]));
  }
 } 
 closedir( $handle );


  $out['FILES']=$files;

 } elseif ($this->view_mode=='delete_file' && $file!='') {
  @unlink(ROOT.'cms/sounds/'.$file.".mp3");
  $this->redirect("?");

 } elseif ($this->view_mode=='edit_file') {

  if ($file!='') {
   $out['DATA']=htmlspecialchars($data);
  }
  $out['FILE']=$file;
  $out['FILE_URL']=urlencode($out['FILE']);
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
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 @umask(0);
  if (!Is_Dir(ROOT."./cms/sounds")) {
   mkdir(ROOT."./cms/sounds", 0777);
  }
  parent::install();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDMwLCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>