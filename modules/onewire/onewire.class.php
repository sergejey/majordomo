<?php
/**
* onewire 
*
* onewire
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.1
*/
//
//
//check to make sure the file exists
if(!function_exists('bcadd'))  {
  if(@file_exists("/opt/owfs/bin/bcadd.php"))  {
    require "/opt/owfs/bin/bcadd.php";
  } else if(file_exists(DIR_MODULES."onewire/bcadd.php"))  {
    require DIR_MODULES."onewire/bcadd.php";
  } else  {
    die("File 'bcadd.php' is not found.\n");
  }
}

//check to make sure the file exists
if(@file_exists("/opt/owfs/bin/ownet.php"))  {
  require "/opt/owfs/bin/ownet.php";
} else if(file_exists(DIR_MODULES."onewire/ownet.php"))  {
  require DIR_MODULES."onewire/ownet.php";
} else {
  die("File 'ownet.php' is not found.\n");
}


class onewire extends module {
/**
* onewire
*
* Module class constructor
*
* @access private
*/
function onewire() {
  $this->name="onewire";
  $this->title="<#LANG_MODULE_ONEWIRE#>";
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
 if ($this->data_source=='onewire' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_onewire') {
   $this->search_onewire($out);
  }

  if ($this->view_mode=='scan') {
   $this->scanDevices();
   $this->redirect("?");
  }

  if ($this->view_mode=='edit_onewire') {
   $this->edit_onewire($out, $this->id);
  }
  
  if ($this->view_mode=='edit_display') {
   $this->edit_display($out, $this->id);
  }
  
  if ($this->view_mode=='delete_onewire') {
   $this->delete_onewire($this->id);
   $this->redirect("?");
  }
  
  if ($this->view_mode=='delete_display') {
   $this->delete_display($this->id);
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
* onewire search
*
* @access public
*/
 function search_onewire(&$out) {
  require(DIR_MODULES.$this->name.'/onewire_search.inc.php');
 }
 
 /**
* display edit/add
*
* @access public
*/
 function edit_display(&$out, $id) {
  require(DIR_MODULES.$this->name.'/display_edit.inc.php');
 }
 
/**
* onewire edit/add
*
* @access public
*/
 function edit_onewire(&$out, $id) {
  require(DIR_MODULES.$this->name.'/onewire_edit.inc.php');
 }
 
/**
* onewire delete display
*
* @access public
*/
 function delete_display($id) {
  SQLExec("DELETE FROM owdisplays WHERE ID='".$id."'");
 }

/**
* Title
*
* Description
*
* @access public
*/
 function propertySetHandle($object, $property, $value) {
  $owp=SQLSelect("SELECT ID FROM owproperties WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
  $total=count($owp);
  if ($total) {
   for($i=0;$i<$total;$i++) {
    $this->setProperty($owp[$i]['ID'], $value);
   }
  }
 }
/**
* onewire delete record
*
* @access public
*/
 function delete_onewire($id) {
  $rec=SQLSelectOne("SELECT * FROM owdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM owproperties WHERE DEVICE_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM owdevices WHERE ID='".$rec['ID']."'");
 }

/**
* Title
*
* Description
*
* @access public
*/

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
* Title
*
* Description
*
* @access public
*/
 function updateDevices($force=0, $device_id=0) {
  $sql=1;
  if (!$force) {
   $sql.=" AND CHECK_NEXT<='".date('Y-m-d H:i:s')."'";
  }
  if ($device_id) {
   $sql.=" AND ID='".(int)$device_id."'";
  }
  $devices=SQLSelect("SELECT ID, TITLE FROM owdevices WHERE ".$sql." ORDER BY CHECK_NEXT");
  $total=count($devices);
  for($i=0;$i<$total;$i++) {
   echo "Checking device: ".$devices[$i]['TITLE']."\n";
   $this->updateDevice($devices[$i]['ID']);
  }
 }

 function initDisplays() {
  $displays=SQLSelect("SELECT UDID FROM owdisplays");
  $total=count($displays);
  $ow=new OWNet(ONEWIRE_SERVER);
  for($i=0;$i<$total;$i++) {
   $ow->set($displays[$i]['UDID']."/LCD_H/message", str_pad("Starting...", 40));
  }
 }
 
 function updateDisplays($force=0, $display_id=0) {
  $sql=1;
  if (!$force) {
   $sql.=" AND UPDATE_NEXT<='".time()."'";
  }
  if ($display_id) {
   $sql.=" AND ID='".(int)$device_id."'";
  }
  $displays=SQLSelect("SELECT ID, TITLE FROM owdisplays WHERE ".$sql." ORDER BY UPDATE_NEXT");
  $total=count($displays);
  for($i=0;$i<$total;$i++) {
   echo "Updating display: ".$displays[$i]['TITLE']."\n";
   $this->updateDisplay($displays[$i]['ID']);
  }
 }
 
/**
* Title
*
* Description
*
* @access public
*/
 function scanDevices() {
  if (!defined('ONEWIRE_SERVER')) {
   return 0;
  }
  $ow=new OWNet(ONEWIRE_SERVER);
  $tmp=$ow->get("/",OWNET_MSG_DIR,false);
  if (!$tmp) {
   return 0;
  }
  $devices=explode(',', $tmp);
  $total=count($devices);
  for($i=0;$i<$total;$i++) {

   if (
    $devices[$i]=='/alarm' ||
    $devices[$i]=='/structure' ||
    $devices[$i]=='/system' ||
    $devices[$i]=='/settings' ||
    $devices[$i]=='/uncached' ||
    $devices[$i]=='/simultaneous' ||
    $devices[$i]=='/statistics' ||
    preg_match('/bus\.\d+$/', $devices[$i]) ||
    0
   ) {
    continue;
   }
   $udid=preg_replace('/^\//', '', $devices[$i]);
   $rec=SQLSelectOne("SELECT * FROM owdevices WHERE UDID='".$udid."'");
   if (!$rec['ID']) {
    $rec['UDID']=$udid;
    $rec['TITLE']=$rec['UDID'];
    $rec['STATUS']=1;
    $rec['ONLINE_INTERVAL']=60*60;
    $rec['LOG']=date('Y-m-d H:i:s').' Added';
    $rec['ID']=SQLInsert('owdevices', $rec);
   }
   $this->updateDevice($rec['ID']);
  }
 }


/**
* Title
*
* Description
*
* @access public
*/
 function setProperty($prop_id, $value, $update_device=1) {
  if (!defined('ONEWIRE_SERVER')) {
   return 0;
  }
  $property=SQLSelectOne("SELECT * FROM owproperties WHERE ID='".$prop_id."'");
  if (!$property['ID']) {
   return 0;
  }

  $ow=new OWNet(ONEWIRE_SERVER);
  $ow->set($property['PATH'],$value);

  if ($update_device) {
   $this->updateDevice($property['DEVICE_ID']);
  }

 }


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function updateStarred() {

   if (!defined('ONEWIRE_SERVER')) {
    return 0;
   }

   $ow=new OWNet(ONEWIRE_SERVER);

   $properties=SQLSelect("SELECT owproperties.*, owdevices.SCRIPT_ID, owdevices.CODE, owdevices.UDID FROM owproperties, owdevices WHERE owdevices.ID=owproperties.DEVICE_ID AND owproperties.STARRED=1 ORDER BY owproperties.UPDATED DESC");
   $total=count($properties);

   for($i=0;$i<$total;$i++) {
    $prec=$properties[$i];
    $old_value=$prec['VALUE'];
    //$value=$ow->get($prec['PATH'],OWNET_MSG_READ,false);
    $value=$ow->get('/uncached'.$prec['PATH'], OWNET_MSG_READ,false);

    if (!$value) {
     $device='/'.$prec['UDID'];
     $tmp=$ow->get($device,OWNET_MSG_DIR,false);
     if (!is_null($tmp)) {
      continue;
     }
    }


    if (!is_null($value)) {
     $prec['VALUE']=$value;
     $prec['UPDATED']=date('Y-m-d H:i:s');

     $script_id=$prec['SCRIPT_ID'];
     $code=$prec['CODE'];

     unset($prec['SCRIPT_ID']);
     unset($prec['CODE']);
     unset($prec['UDID']);
     SQLUpdate('owproperties', $prec);


     if ($prec['LINKED_OBJECT'] && $prec['LINKED_PROPERTY']) {
      setGlobal($prec['LINKED_OBJECT'].'.'.$prec['LINKED_PROPERTY'], $prec['VALUE'], array($this->name=>'0'));
     }

     if ($value!=$old_value) {

      $changed_values=array();
      $changed_values[$prec['SYSNAME']]=array('OLD_VALUE'=>$old_value, 'VALUE'=>$prec['VALUE']);

      $params=$changed_values;
      if ($script_id) {
       runScript($script_id, $params);
      } elseif ($code) {


                  try {
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error in 1-wire action code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                  }

      }

     }

    }
   }
  }

/**
* Title
*
* Description
*
* @access public
*/
function updateDisplay($id) {
  if (!defined('ONEWIRE_SERVER')) {
   return 0;
  }

  $rec=SQLSelectOne("SELECT * FROM owdisplays WHERE ID='".$id."'");
  if (!$rec['ID']) {
   return 0;
  }

  $ow=new OWNet(ONEWIRE_SERVER);
  $device='/'.$rec['UDID'];

  $rec['UPDATE_LATEST']=time();
  $rec['UPDATE_NEXT']=time()+(int)$rec['UPDATE_INTERVAL'];
  
  $rec['VALUE']=str_replace("\r", '', $rec['VALUE']);
  $text = explode("\n", $rec['VALUE']);

 
  for ($i = 1; $i <= $rec['ROWS']; $i++) {
        $line = $i.",1:".$text[$i-1];
        $line = processTitle($line);
    $ow->set($device."/LCD_H/screenyx", str_pad($line, 40));
  }
  
  SQLUpdate('owdisplays', $rec);
}

 function updateDevice($id) {

  if (!defined('ONEWIRE_SERVER')) {
   return 0;
  }

  $rec=SQLSelectOne("SELECT * FROM owdevices WHERE ID='".$id."'");
  if (!$rec['ID']) {
   return 0;
  }

  $ow=new OWNet(ONEWIRE_SERVER);
  $device='/'.$rec['UDID'];

  $rec['CHECK_LATEST']=date('Y-m-d H:i:s');
  $rec['CHECK_NEXT']=date('Y-m-d H:i:s', time()+(int)$rec['ONLINE_INTERVAL']);

   $old_status=$rec['STATUS'];
   $tmp=$ow->get($device,OWNET_MSG_DIR,false);
   if (!$tmp) {
    $rec['STATUS']=0;
   } else {
    $rec['STATUS']=1;
   }
   SQLUpdate('owdevices', $rec);

   if ($rec['STATUS']!=$old_status && ($rec['SCRIPT_ID'] || $rec['CODE'])) {
    $params=array();
    $params['DEVICE']=$device;
    $params['STATUS']=$rec['STATUS'];
    $params['STATUS_CHANGED']=1;
    if ($rec['SCRIPT_ID']) {
     runScript($rec['SCRIPT_ID'], $params);
    } elseif ($rec['CODE']) {

                  try {
                   $code=$rec['CODE'];
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error in 1-wire action code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                  }


    }
   }

   if (!$rec['STATUS']) {
    return 0;
   }

   $changed_values=array();
   $changed=0;
   $properties=explode(',', $tmp);
   $totalp=count($properties);
   for($ip=0;$ip<$totalp;$ip++) {
    $sysname=str_replace($device.'/', '', $properties[$ip]);
    //echo $properties[$ip]." (".$sysname."): ";
    $prec=SQLSelectOne("SELECT * FROM owproperties WHERE DEVICE_ID='".$rec['ID']."' AND SYSNAME='".DBSafe($sysname)."'");
    if (!$prec['ID']) {
     $prec['DEVICE_ID']=$rec['ID'];
     $prec['SYSNAME']=$sysname;
     $prec['PATH']=$properties[$ip];
     $prec['ID']=SQLInsert('owproperties', $prec);
    }
    $old_value=$prec['VALUE'];
    $value=$ow->get($properties[$ip],OWNET_MSG_READ,false);

    if (is_null($value)) {
     $ow->get("/",OWNET_MSG_DIR,false); // hack. for some reason it didn't work correct without it on some devices
     $value=$ow->get($properties[$ip],OWNET_MSG_READ,false);
    }

    if (!is_null($value)) {
     // value updated
     $prec['VALUE']=$value;
     $prec['UPDATED']=date('Y-m-d H:i:s');
     SQLUpdate('owproperties', $prec);
     //$rec['LOG']=date('Y-m-d H:i:s')." ".$prec['SYSNAME'].": ".$prec['VALUE']."\n".$rec['LOG'];
     //SQLUpdate('owdevices', $rec);

     if ($prec['LINKED_OBJECT'] && $prec['LINKED_PROPERTY']) {
      setGlobal($prec['LINKED_OBJECT'].'.'.$prec['LINKED_PROPERTY'], $prec['VALUE'], array($this->name=>'0'));
     }

     if ($old_value!=$value) {
      $changed=1;
      $changed_values[$prec['SYSNAME']]=array('OLD_VALUE'=>$old_value, 'VALUE'=>$prec['VALUE']);
     }

    }
   }

   if ($changed) {
    $params=$changed_values;
    $params['DEVICE']=$device;
    if ($rec['SCRIPT_ID']) {
     runScript($rec['SCRIPT_ID'], $params);
    } elseif ($rec['CODE']) {

                  try {
                   $code=$rec['CODE'];
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error in code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                  }

    }
   }

 }

/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS owdevices');
  SQLExec('DROP TABLE IF EXISTS owproperties');
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
onewire - onewire
*/
  $data = <<<EOD
 owdevices: ID int(10) unsigned NOT NULL auto_increment
 owdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 owdevices: UDID varchar(255) NOT NULL DEFAULT ''
 owdevices: STATUS int(3) NOT NULL DEFAULT '0'
 owdevices: CHECK_LATEST datetime
 owdevices: CHECK_NEXT datetime
 owdevices: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 owdevices: CODE text
 owdevices: ONLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 owdevices: LOG text

 owproperties: ID int(10) unsigned NOT NULL auto_increment
 owproperties: DEVICE_ID int(10) unsigned NOT NULL DEFAULT '0'
 owproperties: SYSNAME varchar(255) NOT NULL DEFAULT ''
 owproperties: PATH varchar(255) NOT NULL DEFAULT ''
 owproperties: VALUE varchar(255) NOT NULL DEFAULT ''
 owproperties: CHECK_LATEST datetime
 owproperties: UPDATED datetime
 owproperties: STARRED int(3) unsigned NOT NULL DEFAULT '0'
 owproperties: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 owproperties: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''

 owdisplays: ID int(10) unsigned NOT NULL auto_increment
 owdisplays: UDID int(10) unsigned NOT NULL DEFAULT '0'
 owdisplays: TITLE varchar(255) NOT NULL DEFAULT ''
 owdisplays: ROWS int(3) unsigned NOT NULL DEFAULT '0'
 owdisplays: COLS int(3) unsigned NOT NULL DEFAULT '0'
 owdisplays: UPDATE_INTERVAL int(10) unsigned NOT NULL DEFAULT '0'
 owdisplays: VALUE text
 owdisplays: UPDATE_LATEST int(10) unsigned NOT NULL DEFAULT '0'
 owdisplays: UPDATE_NEXT int(10) unsigned NOT NULL DEFAULT '0'



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