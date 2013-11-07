<?php
/**
* Security_rules 
*
* Security_rules
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 12:05:34 [May 04, 2013])
*/
//
//
class security_rules extends module {
/**
* security_rules
*
* Module class constructor
*
* @access private
*/
function security_rules() {
  $this->name="security_rules";
  $this->title="<#LANG_MODULE_SECURITY_RULES#>";
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
  $out['TAB']=$this->tab;
  if (IsSet($this->object_id)) {
   $out['IS_SET_OBJECT_ID']=1;
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

 global $object_id;
 global $object_type;
 if (!$this->view_mode && $object_id) {
  $tmp=SQLSelectOne("SELECT * FROM security_rules WHERE OBJECT_ID='".(int)$object_id."' AND OBJECT_TYPE='".DBSafe($object_type)."'");
  if ($tmp['ID']) {
   $this->id=$tmp['ID'];
  }
  $out['OBJECT_TYPE']=$object_type;
  $out['OBJECT_ID']=$object_id;
  $out['NO_CANCEL']=1;
  $this->view_mode='edit_security_rules';
 }

 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='security_rules' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_security_rules') {
   $this->search_security_rules($out);
  }
  if ($this->view_mode=='edit_security_rules') {
   $this->edit_security_rules($out, $this->id);
  }
  if ($this->view_mode=='delete_security_rules') {
   $this->delete_security_rules($this->id);
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
* security_rules search
*
* @access public
*/
 function search_security_rules(&$out) {
  require(DIR_MODULES.$this->name.'/security_rules_search.inc.php');
 }
/**
* security_rules edit/add
*
* @access public
*/
 function edit_security_rules(&$out, $id) {
  require(DIR_MODULES.$this->name.'/security_rules_edit.inc.php');
 }
/**
* security_rules delete record
*
* @access public
*/
 function delete_security_rules($id) {
  $rec=SQLSelectOne("SELECT * FROM security_rules WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM security_rules WHERE ID='".$rec['ID']."'");
 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkAccess($object_type, $object_id) {

  global $session;

  $rule=SQLSelectOne("SELECT * FROM security_rules WHERE OBJECT_TYPE='".$object_type."' AND OBJECT_ID='".(int)$object_id."'");
  if (!$rule['ID']) {
   return true;
  }

/*
 if ($object_id==11) {
  print_r($rule);
  exit;
 }
*/


  //times
  if ($rule['TIMES']) {
   $hours_matched=false;
   $tmp=explode(',', $rule['TIMES']);
   $total=count($tmp);
   for($i=0;$i<$total;$i++) {
    $tmp2=explode('-', $tmp[$i]);
    if (timeBetween($tmp2[0], $tmp2[1])) {
     $hours_matched=true;
    }
   }
   if (!$hours_matched && !$rule['TIMES_EXCEPT']) {
    return false;
   } elseif ($hours_matched && $rule['TIMES_EXCEPT']) {
    return false;
   }
  }

  global $session;
  //users
  if ($rule['USERS']) {
   $users_matched=false;
   if ($session->data['USERNAME'] && !$session->data['USER_ID']) {
    $user=SQLSelectOne("SELECT ID FROM users WHERE USERNAME='".$session->data['USERNAME']."'");
    if ($user['ID']) {
     $session->data['USER_ID']=$user['ID'];
    }
   }
   $user_id=(int)$session->data['USER_ID'];
   $tmp=explode(',',$rule['USERS']);
   if (in_array($user_id, $tmp)) {
    $users_matched=true;
   }
   if (!$users_matched && !$rule['USERS_EXCEPT']) {
    return false;
   } elseif ($users_matched && $rule['USERS_EXCEPT']) {
    return false;
   }
  }

  //terminals
  if ($rule['TERMINALS']) {
   $terminals_matched=false;
   if ($session->data['TERMINAL']) {// && !$session->data['TERMINAL_ID']
    $terminal=SQLSelectOne("SELECT ID FROM terminals WHERE NAME='".$session->data['TERMINAL']."'");
    if ($terminal['ID']) {
     $session->data['TERMINAL_ID']=$terminal['ID'];
    }
   }
   $terminal_id=(int)$session->data['TERMINAL_ID'];
   $tmp=explode(',',$rule['TERMINALS']);
   if (in_array($terminal_id, $tmp)) {
    $terminals_matched=true;
   }
   if (!$terminals_matched && !$rule['TERMINALS_EXCEPT']) {
    return false;
   } elseif ($terminals_matched && $rule['TERMINALS_EXCEPT']) {
    return false;
   }
  }


  return true;
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
  SQLExec('DROP TABLE IF EXISTS security_rules');
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
security_rules - Security_rules
*/
  $data = <<<EOD
 security_rules: ID int(10) unsigned NOT NULL auto_increment
 security_rules: OBJECT_TYPE char(20) NOT NULL DEFAULT ''
 security_rules: OBJECT_ID int(10) NOT NULL DEFAULT '0'
 security_rules: TERMINALS varchar(255) NOT NULL DEFAULT ''
 security_rules: TERMINALS_EXCEPT int(3) NOT NULL DEFAULT '0'
 security_rules: USERS varchar(255) NOT NULL DEFAULT ''
 security_rules: USERS_EXCEPT int(3) NOT NULL DEFAULT '0'
 security_rules: TIMES varchar(255) NOT NULL DEFAULT ''
 security_rules: TIMES_EXCEPT int(3) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDA0LCAyMDEzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>