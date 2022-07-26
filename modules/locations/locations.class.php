<?php
/**
* Locations 
*
* Locations
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 12:05:35 [May 22, 2009])
*/
//
//
class locations extends module {
/**
* locations
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="locations";
  $this->title="<#LANG_MODULE_LOCATIONS#>";
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
function saveParams($data = 0) {
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
 if ($this->data_source=='locations' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_locations') {
      if (gr('location_id')) {
          $this->redirect("?view_mode=edit_locations&id=".gr('location_id'));
      }
   $this->search_locations($out);
  }

     if ($this->view_mode=='priority_up') {
         global $id;
         $this->reorder_items($id,'up');
         $this->redirect("?");
     }
     if ($this->view_mode=='priority_down') {
         global $id;
         $this->reorder_items($id,'down');
         $this->redirect("?");
     }

  if ($this->view_mode=='edit_locations') {
   $this->edit_locations($out, $this->id);
  }
  if ($this->view_mode=='delete_locations') {
   $this->delete_locations($this->id);
   $this->redirect("?");
  }
 }
}

    function reorder_items($id, $direction='up') {
        $all_elements=SQLSelect("SELECT * FROM locations WHERE 1 ORDER BY PRIORITY DESC, TITLE");
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
            SQLUpdate('locations', $all_elements[$i]);
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
* locations search
*
* @access public
*/
 function search_locations(&$out) {
  require(DIR_MODULES.$this->name.'/locations_search.inc.php');
 }
/**
* locations edit/add
*
* @access public
*/
 function edit_locations(&$out, $id) {
  require(DIR_MODULES.$this->name.'/locations_edit.inc.php');
 }
/**
* locations delete record
*
* @access public
*/
 function delete_locations($id) {
  $rec=SQLSelectOne("SELECT * FROM locations WHERE ID='$id'");

     $tables=array('devices','objects');
     foreach($tables as $t) {
         SQLExec("UPDATE $t SET LOCATION_ID=0 WHERE LOCATION_ID=".$rec['ID']);
     }

  // some action for related tables
  SQLExec("DELETE FROM locations WHERE ID='".$rec['ID']."'");
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
   SQLDropTable('locations');
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
locations - Locations
*/
  $data = <<<EOD
 locations: ID int(10) unsigned NOT NULL auto_increment
 locations: TITLE varchar(255) NOT NULL DEFAULT ''
 locations: PRIORITY int(10) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDIyLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>