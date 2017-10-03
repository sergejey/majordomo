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

 function processSubscription($event_name, $details='') {
  if ($event_name=='HOURLY') {
   //...
   $this->getConfig();
   if ($this->config['CONNECT_BACKUP'] && ((int)date('H'))==(int)$this->config['CONNECT_BACKUP_HOUR']) {
    $this->cloudBackup();
   }
  }
 }


 function cloudBackup() {
  $connect_username=$this->config['CONNECT_USERNAME']; //username
  $connect_password=$this->config['CONNECT_PASSWORD'];
  if (!$connect_username || !$connect_password) {
   return false;
  }


  include_once(DIR_MODULES.'saverestore/saverestore.class.php');
  $sv=new saverestore();
  global $data;
  $data=1;
  $out=array();
  $sv->removeTree(ROOT.'saverestore/temp');
  $tar_name=$sv->dump($out);
  $sv->removeTree(ROOT.'saverestore/temp');
  $sv->removeTree(ROOT.'saverestore/temp');


  $dest_file=ROOT.'saverestore/'.$tar_name;


  if ($dest_file && file_exists($dest_file) && filesize($dest_file)>0) {

  if (function_exists('curl_file_create')) { // php 5.6+
   $cfile = curl_file_create($dest_file);
  } else { // 
   $cfile = '@' . realpath($dest_file);
  }
 
  $fields = array(
     'backupfile' => $cfile, 
     'force_data' => '1'
  );

  $url='http://connect.smartliving.ru/upload/';
  $ch = curl_init();

   DebMes("Cloudbackup file $dest_file to $url");

  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 60);
  curl_setopt($ch,CURLOPT_TIMEOUT, 120);
  curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch,CURLOPT_USERPWD, $connect_username.":".$connect_password); 

  //execute post
  $result = curl_exec($ch);
  //close connection
  curl_close($ch);

  //echo "POST RESULT: ".$result;
  if ($result=='OK') {
   @unlink($dest_file);
  }

  }

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
 $out['CONNECT_BACKUP']=$this->config['CONNECT_BACKUP'];

 $out['SEND_MENU']=$this->config['SEND_MENU'];
 $out['SEND_OBJECTS']=$this->config['SEND_OBJECTS'];
 $out['SEND_SCRIPTS']=$this->config['SEND_SCRIPTS'];
 $out['SEND_PATTERNS']=$this->config['SEND_PATTERNS'];


 if ($this->view_mode=='update_settings') {
   global $connect_username;
   global $connect_password;
   global $connect_sync;
   global $connect_backup;

   $this->config['CONNECT_USERNAME']=$connect_username;
   $this->config['CONNECT_PASSWORD']=$connect_password;
   $this->config['CONNECT_SYNC']=(int)$connect_sync;
   $this->config['CONNECT_BACKUP']=(int)$connect_backup;
   $this->config['CONNECT_BACKUP_HOUR']=(int)rand(0, 6);

   if ($this->config['CONNECT_BACKUP']) {
    subscribeToEvent($this->name, 'HOURLY');
    $this->cloudBackup(); // backup now
   }

   $this->saveConfig();
   setGlobal('cycle_connectControl', 'restart');
   $this->redirect("?");
 }

 if ($this->view_mode=='send_data') {
  $this->sendData($out);
 }

 if ($this->tab=='calls') {

  if ($this->view_mode=='sync') {
   if ($this->config['CONNECT_USERNAME']) {
    $this->sendCalls();
   }
   $this->redirect("?tab=".$this->tab);
  }

  if ($this->view_mode=='delete_calls') {
   global $id;
   SQLExec("DELETE FROM public_calls WHERE ID='".(int)$id."'");
   $this->redirect("?tab=".$this->tab."&view_mode=sync");
  }

  if ($this->view_mode=='edit_calls') {
   global $id;
   $rec=SQLSelectOne("SELECT * FROM public_calls WHERE ID='".(int)$id."'");
   if ($this->mode=='update') {
    $ok=1;

    global $title;
    $rec['TITLE']=$title;
    if (!$rec['TITLE']) {
     $out['ERR_TITLE']=1;
     $ok=0;
    }

    global $linked_object;
    $rec['LINKED_OBJECT']=$linked_object;

    global $linked_method;
    $rec['LINKED_METHOD']=$linked_method;

    global $protected;
    $rec['PROTECTED']=(int)$protected;

    global $public_username;
    $rec['PUBLIC_USERNAME']=$public_username;

    global $public_password;
    $rec['PUBLIC_PASSWORD']=$public_password;

    if ($ok) {
     if ($rec['ID']) {
      SQLUpdate('public_calls', $rec);
     } else {
      $rec['ID']=SQLInsert('public_calls', $rec);
     }

     if (preg_match_all('/%(\w+)\.(\w+)%/is',$rec['TITLE'],$m)) {
      $total = count($m[1]);
      for ($i = 0; $i < $total; $i++) {
       addLinkedProperty($m[1][$i],$m[2][$i],$this->name);
      }
     }

     $this->redirect("?tab=".$this->tab."&view_mode=sync");
    }
   }
   outHash($rec, $out);
  }
  $calls=SQLSelect("SELECT * FROM public_calls ORDER BY ID DESC");
  $out['CALLS']=$calls;
 }

 if ($_GET['uploaded']) {
  $out['UPLOADED']=1;
  $out['RESULT']=$_GET['result'];
 }

 $out['TAB']=$this->tab;

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
  $fields = array('merge'=>1, 'data' => urlencode(serialize($data)), 'force_data'=>$force_data);

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

 function propertySetHandle($object, $property, $value) {
  $calls=SQLSelect("SELECT ID FROM public_calls WHERE TITLE LIKE '%".DBSafe($object.'.'.$property)."%'");
  if ($calls[0]['ID']) {
   $this->sendCalls();
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function sendCalls() {

   $this->getConfig();
   // menu items
   $data=array();
   $calls=SQLSelect("SELECT * FROM public_calls");
   $total = count($calls);
   for ($i = 0; $i < $total; $i++) {
    $calls[$i]['TITLE']=processTitle($calls[$i]['TITLE']);
   }
   $data['PUBLIC_CALLS']=$calls;


  // POST TO SERVER
  $url = 'http://connect.smartliving.ru/upload/';
  $fields = array('merge'=>1, 'data' => urlencode(serialize($data)), 'force_data'=>$force_data);

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

  //echo $result;exit;
  $tmp=json_decode($result, true);
  if (is_array($tmp['PUBLIC_CALLS'])) {
   $total=count($tmp['PUBLIC_CALLS']);
   for($i=0;$i<$total;$i++) {
    SQLExec("UPDATE public_calls SET PUBLIC_KEY='".$tmp['PUBLIC_CALLS'][$i]['PUBLIC_KEY']."' WHERE ID='".$tmp['PUBLIC_CALLS'][$i]['INTERNAL_ID']."'");
   }
  }

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


  if (!function_exists('getCurlValue')) {
   function getCurlValue($filename, $contentType, $postname)
   {
     if (function_exists('curl_file_create')) {
         return curl_file_create($filename, $contentType, $postname);
     }
     $value = "@".$filename.";filename=" . $postname;
     if ($contentType) {
         $value .= ';type=' . $contentType;
     }
     return $value;
   }
  }

  $cfile = getCurlValue($datafile_name,'text/plain','datafile.txt');
 
//NOTE: The top level key in the array is important, as some apis will insist that it is 'file'.

  $fields = array(
     'datafile' => $cfile
  );

  /*
  $fields = array(
     'datafile' => '@'.realpath($datafile_name).';filename=datafile.txt'
  );
  */

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
  subscribeToEvent($this->name, 'HOURLY');  
  parent::install();
 }

 function dbInstall($data) {
/*
commands - Commands
*/
  $data = <<<EOD
 public_calls: ID int(10) unsigned NOT NULL auto_increment
 public_calls: TITLE varchar(255) NOT NULL DEFAULT ''
 public_calls: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 public_calls: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''
 public_calls: PUBLIC_USERNAME varchar(255) NOT NULL DEFAULT ''
 public_calls: PUBLIC_PASSWORD varchar(255) NOT NULL DEFAULT ''
 public_calls: PUBLIC_KEY varchar(255) NOT NULL DEFAULT ''
 public_calls: PROTECTED int(3) NOT NULL DEFAULT '0'

EOD;
  parent::dbInstall($data);


 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDI0LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>