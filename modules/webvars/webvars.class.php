<?
/**
* webvars 
*
* webvars
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.4 (wizard, 00:01:48 [Jan 06, 2011])
*/
Define('DEF_TYPE_OPTIONS', '0=PING (HOST)|1=WEB PAGE (URL)'); // options for 'HOST TYPE'
//
//
class webvars extends module {
/**
* webvars
*
* Module class constructor
*
* @access private
*/
function webvars() {
  $this->name="webvars";
  $this->title="<#LANG_MODULE_WEBVARS#>";
  $this->module_category="<#LANG_SECTION_OBJECTS#>";
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

  if ($this->mobile) {
   $out['MOBILE']=1;
  }

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
 if ($this->data_source=='webvars' || $this->data_source=='') {

  if ($this->view_mode=='checkall') {
   $this->checkAllVars(1);
   $this->redirect("?");
  }

  if ($this->view_mode=='' || $this->view_mode=='search_webvars') {
   $this->search_webvars($out);
  }
  if ($this->view_mode=='edit_webvars') {
   $this->edit_webvars($out, $this->id);
  }
  if ($this->view_mode=='delete_webvars') {
   $this->delete_webvars($this->id);
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
* webvars search
*
* @access public
*/
 function search_webvars(&$out) {
  require(DIR_MODULES.$this->name.'/webvars_search.inc.php');
 }
/**
* webvars edit/add
*
* @access public
*/
 function edit_webvars(&$out, $id) {
  require(DIR_MODULES.$this->name.'/webvars_edit.inc.php');
 }
/**
* webvars delete record
*
* @access public
*/
 function delete_webvars($id) {
  $rec=SQLSelectOne("SELECT * FROM webvars WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM webvars WHERE ID='".$rec['ID']."'");
 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkAllVars($force=0) {

  // ping hosts
  if ($force) {
   $pings=SQLSelect("SELECT * FROM webvars WHERE 1");
  } else {
   $pings=SQLSelect("SELECT * FROM webvars WHERE CHECK_NEXT<=NOW()");
  }
  
  $total=count($pings);
  for($i=0;$i<$total;$i++) {
   $host=$pings[$i];
   if (!$force) {
    echo "Checking webvar: ".$host['HOSTNAME']."\n";
   }
   $online_interval=$host['ONLINE_INTERVAL'];
   if (!$online_interval) {
    $online_interval=60;
   }
   $host['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$online_interval);
   SQLUpdate('webvars', $host);

   // checking

   //web host
   $old_status=$host['LATEST_VALUE'];
   if ($host['AUTH'] && $host['USERNAME']) {
    $content=getURL($host['HOSTNAME'], $host['ONLINE_INTERVAL'], $host['USERNAME'], $host['PASSWORD']);
   } else {
    $content=getURL($host['HOSTNAME'], $host['ONLINE_INTERVAL']);
   }
   
   if ($host['ENCODING']!='') {
    $content=iconv($host['ENCODING'], "UTF-8", $content);
   }

   $ok=1;
   $new_status='';
   if ($host['SEARCH_PATTERN']) {
    if (preg_match('/'.$host['SEARCH_PATTERN'].'/is', $content, $m)) {
     $new_status=$m[1];
    } else {
     $ok=0; // result did not matched
    }
   } else {
    $new_status=$content;
   }

   if ($host['CHECK_PATTERN'] && !preg_match('/'.$host['CHECK_PATTERN'].'/is', $new_status)) {
    $ok=0; // result did not pass the check
   }
   
   if (!$ok) {
    $host['LOG']=date('Y-m-d H:i:s').' incorrect value:'.$new_status."\n".$host['LOG'];
    SQLUpdate('webvars', $host);
    continue;
   }


   $host['CHECK_LATEST']=date('Y-m-d H:i:s');
   $host['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$online_interval);

   if ($old_status!=$new_status) {
     $host['LOG']=date('Y-m-d H:i:s').' new value:'.$new_status."\n".$host['LOG'];
   }

   $host['LATEST_VALUE']=$new_status;
   SQLUpdate('webvars', $host);

   if ($host['LINKED_OBJECT']!='' && $host['LINKED_PROPERTY']!='') {
    getObject($host['LINKED_OBJECT'])->setProperty($host['LINKED_PROPERTY'], $new_status);
   }

   if ($old_status!=$new_status && $old_status!='') {
    // do some status change actions
    $run_script_id=0;
    $run_code='';
     // got online
     if ($host['SCRIPT_ID']) {
      $run_script_id=$host['SCRIPT_ID'];
     } elseif ($host['CODE']) {
      $run_code=$host['CODE'];
     }

    if ($run_script_id) {
     //run script
     runScript($run_script_id);
    } elseif ($run_code) {
     //run code
     eval($run_code);
    }

   }
   

  } 


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
  SQLExec('DROP TABLE IF EXISTS webvars');
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
webvars - webvars
*/
  $data = <<<EOD
 webvars: ID int(10) unsigned NOT NULL auto_increment
 webvars: TITLE varchar(255) NOT NULL DEFAULT ''
 webvars: HOSTNAME varchar(255) NOT NULL DEFAULT ''
 webvars: TYPE int(30) NOT NULL DEFAULT '0'
 webvars: SEARCH_PATTERN varchar(255) NOT NULL DEFAULT ''
 webvars: CHECK_PATTERN varchar(255) NOT NULL DEFAULT ''
 webvars: LATEST_VALUE text NOT NULL DEFAULT ''
 webvars: CHECK_LATEST datetime
 webvars: CHECK_NEXT datetime
 webvars: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 webvars: ONLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 webvars: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 webvars: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 webvars: ENCODING varchar(50) NOT NULL DEFAULT ''
 webvars: AUTH int(3) NOT NULL DEFAULT '0'
 webvars: USERNAME varchar(100) NOT NULL DEFAULT ''
 webvars: PASSWORD varchar(100) NOT NULL DEFAULT ''
 webvars: CODE text
 webvars: LOG text
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDA2LCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>