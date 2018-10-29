<?php
/**
* Terminals 
*
* Terminals
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3
*/
//
//
class terminals extends module {
/**
* terminals
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="terminals";
  $this->title="<#LANG_MODULE_TERMINALS#>";
  $this->module_category="<#LANG_SECTION_SETTINGS#>";
  $this->checkInstalled();
  $this->serverip=$this->getLocalIp();
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
 if ($this->data_source=='terminals' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_terminals') {
   $this->search_terminals($out);
  }
  if ($this->view_mode=='edit_terminals') {
   $this->edit_terminals($out, $this->id);
  }
  if ($this->view_mode=='delete_terminals') {
   $this->delete_terminals($this->id);
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
* terminals search
*
* @access public
*/
 function search_terminals(&$out) {
  require(DIR_MODULES.$this->name.'/terminals_search.inc.php');
 }
/**
* terminals edit/add
*
* @access public
*/
 function edit_terminals(&$out, $id) {
  require(DIR_MODULES.$this->name.'/terminals_edit.inc.php');
 }

	/**
	* terminals delete record
	*
	* @access public
	*/
	function delete_terminals($id) {
		if($rec = getTerminalByID($id)) {
			SQLExec('DELETE FROM `terminals` WHERE `ID` = '.$rec['ID']);
		}
	}

/**
* terminals subscription events
*
* @access public
*/
function processSubscription($event, $details='') {
    $this->getConfig();
    if ($event=='SAYTO') {
        if($this->debug == 1) debmes('mpt sayto start');
        $level=$details['level'];
        $message=$details['message'];
        $target = $details['destination'];
	if(!$target) return 0;
        //DebMes($details);
        $level = $details['level'];
        $message = $details['message'];
        $levelmes = getGlobal('ThisComputer.minMsgLevel');
        }
    	
    	
    if ($event=='ASK') {
       $tartget = $this->targetToIp($details['target']);
       if(!$target) return 0;
       $message=$details['prompt'];
       $this->send_mpt('ask', $message, $target);
       if($this->debug == 1) debmes('mpt ask ' . $message . '; target = ' . $target);
       //DebMes($details);
       $level = $details['level'];
       $message = $details['message'];
       $levelmes = getGlobal('ThisComputer.minMsgLevel');
       }
    
    if ($event=='SAYREPLY') {
       $level=$details['level'];
       $message=$details['message'];
       $source=$details['source'];
       $target = $this->targetToIp($details['replyto']);
       if(!$target) return 0;
       $this->send_mpt('tts', $message, $target);
       $levelmes = getGlobal('ThisComputer.minMsgLevel');
       if($this->debug == 1) debmes('mpt sayto ' . $message . '; level = ' . $level . '; to = ' . $destination);
       } 
 
    // chek the level message for nigth or darknest mode 
    if ($levelmes<$level){
        // main play instruction with generate message for terminals when not installed TTS 
        // check the existed files generated from tts 
        if (file_exists(ROOT.'/cms/cached/voice/' . md5($message) . '_google.mp3')) {
            $cached_filename = 'http://'. $this->serverip. '/cms/cached/voice/' . md5($message) . '_google.mp3';
        } else if (file_exists(ROOT.'/cms/cached/voice/' . md5($message) . '_yandex.mp3')) {
            $cached_filename = 'http://'. $this->serverip. '/cms/cached/voice/' . md5($message) . '_yandex.mp3';
        } else if (file_exists(ROOT.'/cms/cached/voice/rh_' . md5($message) . '.mp3')) {
            $cached_filename = 'http://'. $this->serverip. '/cms/cached/voice/rh_' . md5($message) . '.mp3';
        } else {
            // generate message from google tts
            $filename = md5($message) . '_google.mp3';
            $cachedVoiceDir = ROOT . 'cms/cached/voice';
            $cachedFileName = $cachedVoiceDir . '/' . $filename;
            $base_url = 'https://translate.google.com/translate_tts?';
            $lang = SETTINGS_SITE_LANGUAGE;
            if ($lang == 'ua') { 
                $lang = 'uk';
            }else if ($lang == 'ru') {
     	       $lang = 'ru';
            }else {
     	       $lang = 'en';
            }
            $qs = http_build_query([ 'ie' => 'UTF-8', 'client' => 'tw-ob', 'q' => $message, 'tl' => $lang,]);
            try {
               $contents = file_get_contents($base_url . $qs);
            } catch (Exception $e) {
               registerError('ssdp_finder', get_class($e) . ', ' . $e->getMessage());
            }
            if (isset($contents)) {
               CreateDir($cachedVoiceDir);
               SaveFile($cachedFileName, $contents);
            }
            $cached_filename = 'http://'. $this->getLocalIp(). '/cms/cached/voice/' . md5($message) . '_google.mp3';
       	   }
         playMedia($cached_filename, $target, true);
     }
}
    
/**
* get local IP 
*
* @access public
*/
function getLocalIp() { 
  $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  socket_connect($s ,'8.8.8.8', 53);  // connecting to a UDP address doesn't send packets
  socket_getsockname($s, $local_ip_address, $port);
  @socket_shutdown($s, 2);
  socket_close($s);
  
  return $local_ip_address; 
}


/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  subscribeToEvent($this->name, 'SAYREPLY','',99);
  subscribeToEvent($this->name, 'SAYTO','',99);
  subscribeToEvent($this->name, 'ASK','',99);
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
   SQLDropTable('terminals');
   unsubscribeFromEvent($this->name, 'SAYTO');
   unsubscribeFromEvent($this->name, 'ASK');
   unsubscribeFromEvent($this->name, 'SAYREPLY');
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
terminals - Terminals
*/
  $data = <<<EOD
 terminals: ID int(10) unsigned NOT NULL auto_increment
 terminals: NAME varchar(255) NOT NULL DEFAULT ''
 terminals: HOST varchar(255) NOT NULL DEFAULT ''
 terminals: TITLE varchar(255) NOT NULL DEFAULT ''
 terminals: CANPLAY int(3) NOT NULL DEFAULT '0'
 terminals: PLAYER_TYPE char(10) NOT NULL DEFAULT ''
 terminals: PLAYER_PORT varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_USERNAME varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_PASSWORD varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_CONTROL_ADDRESS varchar(255) NOT NULL DEFAULT ''
 terminals: IS_ONLINE int(3) NOT NULL DEFAULT '0'
 terminals: MAJORDROID_API int(3) NOT NULL DEFAULT '0'
 terminals: LATEST_REQUEST varchar(255) NOT NULL DEFAULT ''
 terminals: LATEST_REQUEST_TIME datetime
 terminals: LATEST_ACTIVITY datetime
 terminals: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 terminals: LEVEL_LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
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
