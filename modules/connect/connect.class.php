<?php
/**
* Connect 
*
* Connect
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 13:07:13 [Jul 24, 2013])
*/
//
//
class connect extends module {
/**
* connect
*
* Module class constructor
*
* @access private
*/
function connect() {
  $this->name="connect";
  $this->title="<#LANG_MODULE_CONNECT#>";
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

 $this->getConfig();

 $out['CONNECT_USERNAME']=$this->config['CONNECT_USERNAME'];
 $out['CONNECT_PASSWORD']=$this->config['CONNECT_PASSWORD'];
 $out['CONNECT_SYNC']=$this->config['CONNECT_SYNC'];

 $out['SEND_MENU']=$this->config['SEND_MENU'];
 $out['SEND_OBJECTS']=$this->config['SEND_OBJECTS'];
 $out['SEND_SCRIPTS']=$this->config['SEND_SCRIPTS'];
 $out['SEND_PATTERNS']=$this->config['SEND_PATTERNS'];


 if ($this->view_mode=='update_settings') {
   global $connect_username;
   global $connect_password;
   global $connect_sync;

   $this->config['CONNECT_USERNAME']=$connect_username;
   $this->config['CONNECT_PASSWORD']=$connect_password;
   $this->config['CONNECT_SYNC']=(int)$connect_sync;

   $this->saveConfig();
   $this->redirect("?");
 }

 if ($this->view_mode=='send_data') {
  $this->sendData($out);
 }

 if ($_GET['uploaded']) {
  $out['UPLOADED']=1;
  $out['RESULT']=$_GET['result'];
 }

}

/**
* Title
*
* Description
*
* @access public
*/
 function sendMenu($force_data=0) {
   // menu items
   $data=array();
   $data['COMMANDS']=SQLSelect("SELECT * FROM commands");
   $total=count($data['COMMANDS']);
   for($i=0;$i<$total;$i++) {
    if (!$this->config['CONNECT_SYNC'] && !$force_data) {
     unset($data['COMMANDS'][$i]['CUR_VALUE']);
     unset($data['COMMANDS'][$i]['RENDER_TITLE']);
     unset($data['COMMANDS'][$i]['RENDER_DATA']);
    }
   }

  // POST TO SERVER
  $url = 'http://connect.smartliving.ru/upload/';
  $fields = array('merge'=>1, 'data' => urlencode(serialize($data)));

  //url-ify the data for the POST
  foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
  rtrim($fields_string, '&');

  //open connection
  $ch = curl_init();
  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 30);
  curl_setopt($ch,CURLOPT_TIMEOUT, 30);


  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
  curl_setopt($ch, CURLOPT_USERPWD, $this->config['CONNECT_USERNAME'].":".$this->config['CONNECT_PASSWORD']); 

  //execute post
  $result = curl_exec($ch);
  //close connection
  curl_close($ch);

 }

/**
* Title
*
* Description
*
* @access public
*/
 function sendData(&$out, $silent=0) {
  global $send_menu;
  global $send_objects;
  global $send_scripts;
  global $send_patterns;

  $this->config['SEND_MENU']=(int)$send_menu;
  $this->config['SEND_OBJECTS']=(int)$send_objects;
  $this->config['SEND_SCRIPTS']=(int)$send_scripts;
  $this->config['SEND_PATTERNS']=(int)$send_patterns;
  $this->saveConfig();

  $data=array();

  if ($this->config['SEND_MENU']) {
   // menu items
   $data['COMMANDS']=SQLSelect("SELECT * FROM commands");
   $total=count($data['COMMANDS']);
   for($i=0;$i<$total;$i++) {
    if (!$this->config['CONNECT_SYNC']) {
     unset($data['COMMANDS'][$i]['CUR_VALUE']);
     unset($data['COMMANDS'][$i]['RENDER_TITLE']);
     unset($data['COMMANDS'][$i]['RENDER_DATA']);
    }
   }
  }

  if ($this->config['SEND_OBJECTS']) {
   // objects and classes
   $data['CLASSES']=SQLSelect("SELECT * FROM classes");
   $data['OBJECTS']=SQLSelect("SELECT * FROM objects");
   $data['METHODS']=SQLSelect("SELECT * FROM methods");
   $total=count($data['METHODS']);
   for($i=0;$i<$total;$i++) {
    unset($data['METHODS'][$i]['EXECUTED_PARAMS']);
    unset($data['METHODS'][$i]['EXECUTED']);
   }
   $data['PROPERTIES']=SQLSelect("SELECT * FROM properties");
  }

  if ($this->config['SEND_SCRIPTS']) {
   // objects scripts
   $data['SCRIPTS']=SQLSelect("SELECT * FROM scripts");
   $data['SCRIPT_CATEGORIES']=SQLSelect("SELECT * FROM script_categories");
  }

  if ($this->config['SEND_PATTERNS']) {
   // patterns
   $data['PATTERNS']=SQLSelect("SELECT * FROM patterns");
  }

  // POST TO SERVER
  $url = 'http://connect.smartliving.ru/upload/';
  $datafile_name=ROOT.'cached/connect_data.txt';
  SaveFile($datafile_name, serialize($data));


  $fields = array(
     'datafile' => '@'.realpath($datafile_name).';filename=datafile.txt'
  );

  //print_r($fields);exit;


  //url-ify the data for the POST
  //foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
  //rtrim($fields_string, '&');

  //sleep(1);
  //open connection
  $ch = curl_init();
  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 60);
  curl_setopt($ch,CURLOPT_TIMEOUT, 120);
  curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
  curl_setopt($ch,CURLOPT_USERPWD, $this->config['CONNECT_USERNAME'].":".$this->config['CONNECT_PASSWORD']); 

  //execute post
  $result = curl_exec($ch);
  //close connection
  curl_close($ch);

  if (!$silent) {
   $this->redirect("?uploaded=1&result=".urlencode($result));
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
  parent::install();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDI0LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>