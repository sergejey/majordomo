<?php
/**
* Modbus 
*
* Modbus
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 17:07:34 [Jul 24, 2014])
*/
Define('DEF_REQUEST_TYPE_OPTIONS', 'FC1=FC1 Read coils|FC2=FC2 Read input discretes|FC3=FC3 Read holding registers|FC4=FC4 Read holding input registers|FC5=FC5 Write single coil|FC6=FC6 Write single register|FC15=FC15 Write multiple coils|FC16=FC16 Write multiple registers'); // options for 'REQUEST_TYPE' |FC23=FC23 Read/Write multiple registers
Define('DEF_RESPONSE_CONVERT_OPTIONS', '0=None (bytes)|r2f=REAL to Float|d2i=DINT to integer|dw2i=DWORD to integer|i2i=INT to integer|w2i=WORD to integer|s=String'); // options for 'RESPONSE_CONVERT'
//
//
class modbus extends module {
/**
* modbus
*
* Module class constructor
*
* @access private
*/
function modbus() {
  $this->name="modbus";
  $this->title="<#LANG_MODULE_MODBUS#>";
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
 if ($this->data_source=='modbusdevices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_modbusdevices') {
   $this->search_modbusdevices($out);
  }
  if ($this->view_mode=='edit_modbusdevices') {
   $this->edit_modbusdevices($out, $this->id);
  }
  if ($this->view_mode=='poll_device') {
   $this->poll_device($this->id);
   $this->redirect("?");
  }

  if ($this->view_mode=='delete_modbusdevices') {
   $this->delete_modbusdevices($this->id);
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

 function propertySetHandle($object, $property, $value) {
   $modbusdevices=SQLSelect("SELECT ID FROM modbusdevices WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($modbusdevices);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     $this->poll_device($modbusdevices[$i]['ID']);
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
 function readAll() {
  $devices=SQLSelect("SELECT ID FROM modbusdevices WHERE CHECK_NEXT<NOW()");
  $total=count($devices);
  for($i=0;$i<$total;$i++) {
   $this->poll_device($devices[$i]['ID']);
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function poll_device($id) {


   $rec=SQLSelectOne("SELECT * FROM modbusdevices WHERE ID='".(int)$id."'");
   if (!$rec['ID']) {
    return;
   }

   $rec['CHECK_LATEST']=date('Y-m-d H:i:s');
   $rec['CHECK_NEXT']=date('Y-m-d H:i:s', (time()+(int)$rec['POLLPERIOD']));
   SQLUpdate('modbusdevices', $rec);

   if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY'] && 
    ($rec['REQUEST_TYPE']=='FC5' || $rec['REQUEST_TYPE']=='FC6' || $rec['REQUEST_TYPE']=='FC15' || $rec['REQUEST_TYPE']=='FC16' || $rec['REQUEST_TYPE']=='FC23')) {
    $rec['DATA']=getGlobal($rec['LINKED_OBJECT'].'.'.$rec['LINKED_PROPERTY']);
   }


   require_once dirname(__FILE__) . '/ModbusMaster.php';
   $modbus = new ModbusMaster($rec['HOST'], $rec['PROTOCOL']);


   if ($rec['REQUEST_TYPE']=='FC1') {
   //FC1 Read coils
    try {
     $recData = $modbus->readCoils($rec['DEVICE_ID'], $rec['REQUEST_START'], $rec['REQUEST_TOTAL']);
     if (is_array($recData)) {
      foreach($recData as $k=>$v)
       $recData[$k]=(int)$v;
     }
    }
    catch (Exception $e) {
    // Print error information if any
     $rec['LOG']=date('Y-m-d H:i:s')." FC1 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC2') {
   //FC2 Read input discretes
    try {
     $recData = $modbus->readInputDiscretes($rec['DEVICE_ID'], $rec['REQUEST_START'], $rec['REQUEST_TOTAL']);
     if (is_array($recData)) {
      foreach($recData as $k=>$v)
       $recData[$k]=(int)$v;
     }
    }
    catch (Exception $e) {
    // Print error information if any
     $rec['LOG']=date('Y-m-d H:i:s')." FC2 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC3') {
   //FC3 Read holding registers
    try {
     $recData = $modbus->readMultipleRegisters($rec['DEVICE_ID'], $rec['REQUEST_START'], $rec['REQUEST_TOTAL']);
    }
    catch (Exception $e) {
    // Print error information if any
     $rec['LOG']=date('Y-m-d H:i:s')." FC3 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC4') {
   //FC4 Read holding input registers
    try {
     $recData = $modbus->readMultipleInputRegisters($rec['DEVICE_ID'], $rec['REQUEST_START'], $rec['REQUEST_TOTAL']);
    }
    catch (Exception $e) {
    // Print error information if any
     $rec['LOG']=date('Y-m-d H:i:s')." FC4 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC5') {
    //FC5 Write single coil
    if ((int)$rec['DATA']) {
     $data_set=array(TRUE);
    } else {
     $data_set=array(FALSE);
    }
    try {
     $modbus->writeSingleCoil($rec['DEVICE_ID'], $rec['REQUEST_START'], $data_set);
    }
    catch (Exception $e) {
     $rec['LOG']=date('Y-m-d H:i:s')." FC5 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC6') {
   //FC6 Write single register
    try {
     $data_set=array((int)$rec['DATA']);
     $dataTypes = array("INT"); //TO-DO: support of other data types
     $recData = $modbus->writeSingleRegister($rec['DEVICE_ID'], $rec['REQUEST_START'], $data_set, $dataTypes);
    }
    catch (Exception $e) {
    // Print error information if any
     $rec['LOG']=date('Y-m-d H:i:s')." FC6 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC15') {
   //FC15 Write multiple coils
    $data_set=explode(',', $rec['DATA']);
    foreach($data_set as $k=>$v) {
     $data_set[$k]=(bool)$v;
    }
    try {
     $modbus->writeMultipleCoils($rec['DEVICE_ID'], $rec['REQUEST_START'], $data_set);
    }
    catch (Exception $e) {
     $rec['LOG']=date('Y-m-d H:i:s')." FC15 Error: $modbus $e\n".$rec['LOG'];
    }

   } elseif ($rec['REQUEST_TYPE']=='FC16') {
   //FC16 Write multiple registers
    try {
     $data_set=explode(',', $rec['DATA']);
     $dataTypes=array();
     foreach($data_set as $k=>$v) {
      $data_set[$k]=(int)$v;
      $dataTypes[]="INT";  //TO-DO: support of other data types
     }
     $recData = $modbus->writeMultipleRegister($rec['DEVICE_ID'], $rec['REQUEST_START'], $data_set, $dataTypes);
    }
    catch (Exception $e) {
    // Print error information if any
     $rec['LOG']=date('Y-m-d H:i:s')." FC16 Error: $modbus $e\n".$rec['LOG'];
    }
   } elseif ($rec['REQUEST_TYPE']=='FC23') {
   //FC23 Read/Write multiple registers
   //TO-DO
   }



  if ($rec['REQUEST_TYPE']=='FC1' || $rec['REQUEST_TYPE']=='FC2' || $rec['REQUEST_TYPE']=='FC3' || $rec['REQUEST_TYPE']=='FC4') {
   // PROCESS RESPONSE

   if ($rec['RESPONSE_CONVERT']=='r2f') {
    //REAL to Float
    $values = array_chunk($recData, 4);   
    $recData=array();
    foreach($values as $bytes) echo $recData[]=PhpType::bytes2float($bytes);
   } elseif ($rec['RESPONSE_CONVERT']=='d2i') {
    //DINT to integer
    $values = array_chunk($recData, 4);   
    $recData=array();
    foreach($values as $bytes) echo $recData[]=PhpType::bytes2signedInt($bytes);
   } elseif ($rec['RESPONSE_CONVERT']=='dw2i') {
    //DWORD to integer
    $values = array_chunk($recData, 4);   
    $recData=array();
    foreach($values as $bytes) $recData[]=PhpType::bytes2unsignedInt($bytes);
   } elseif ($rec['RESPONSE_CONVERT']=='i2i') {
    //INT to integer
    $values = array_chunk($recData, 2);
    $recData=array();
    foreach($values as $bytes) $recData[]=PhpType::bytes2signedInt($bytes);
   } elseif ($rec['RESPONSE_CONVERT']=='w2i') {
    //WORD to integer
    $values = array_chunk($recData, 2);
    $recData=array();
    foreach($values as $bytes) $recData[]=PhpType::bytes2unsignedInt($bytes);
   } elseif ($rec['RESPONSE_CONVERT']=='s') {
    //String
    $recData=array(PhpType::bytes2string($recData));
   } else {
   //
   }
   $result=implode(',', $recData);
   if ($result && $result!=$rec['DATA']) {
    $rec['LOG']=date('Y-m-d H:i:s')." ".$result."\n".$rec['LOG'];
   }
   $rec['DATA']=$result;
   SQLUpdate('modbusdevices', $rec);
   if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
    setGlobal($rec['LINKED_OBJECT'].'.'.$rec['LINKED_PROPERTY'], $rec['DATA'], array($this->name=>'0'));
   }

  } else {
   SQLUpdate('modbusdevices', $rec);
  }


 }

/**
* modbusdevices search
*
* @access public
*/
 function search_modbusdevices(&$out) {
  require(DIR_MODULES.$this->name.'/modbusdevices_search.inc.php');
 }
/**
* modbusdevices edit/add
*
* @access public
*/
 function edit_modbusdevices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/modbusdevices_edit.inc.php');
 }
/**
* modbusdevices delete record
*
* @access public
*/
 function delete_modbusdevices($id) {
  $rec=SQLSelectOne("SELECT * FROM modbusdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM modbusdevices WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS modbusdevices');
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
modbusdevices - Modbus devices
*/
  $data = <<<EOD
 modbusdevices: ID int(10) unsigned NOT NULL auto_increment
 modbusdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 modbusdevices: HOST varchar(255) NOT NULL DEFAULT ''
 modbusdevices: PROTOCOL char(5) NOT NULL DEFAULT 'UDP'
 modbusdevices: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 modbusdevices: REQUEST_TYPE varchar(10) NOT NULL DEFAULT ''
 modbusdevices: REQUEST_START int(10) NOT NULL DEFAULT '0'
 modbusdevices: REQUEST_TOTAL int(10) NOT NULL DEFAULT '0'
 modbusdevices: RESPONSE_CONVERT varchar(10) NOT NULL DEFAULT ''
 modbusdevices: DATA text
 modbusdevices: CHECK_LATEST datetime
 modbusdevices: CHECK_NEXT datetime
 modbusdevices: POLLPERIOD int(10) NOT NULL DEFAULT '0'
 modbusdevices: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 modbusdevices: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 modbusdevices: LOG text NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDI0LCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>