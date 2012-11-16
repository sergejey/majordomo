<?
/**
* Blank 
*
* Blank
*
* @package project
* @author Serge J. <info@atmatic.com>
* @copyright http://www.activeunit.com/ (c)
* @version 0.1 (wizard, 17:03:02 [Mar 04, 2010])
*/
//
//
class xray extends module {
/**
* blank
*
* Module class constructor
*
* @access private
*/
function xray() {
  $this->name="xray";
  $this->title="X-Ray";
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
function saveParams() {
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
 global $ajax;
 if ($ajax) {
  global $op;
  if ($op=='getcontent') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');
    if ($this->view_mode=='') {
     $res=SQLSelect("SELECT pvalues.*, objects.TITLE as OBJECT, properties.TITLE as PROPERTY FROM pvalues LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID  ORDER BY pvalues.UPDATED DESC");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>PROPERTY</b></td>';
      echo '<td><b>VALUE</b></td>';
      echo '<td><b>UPDATED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['OBJECT'].'.'.$res[$i]['PROPERTY'];
      echo '</td>';
      echo '<td>';
      echo $res[$i]['VALUE'].'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['UPDATED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';
    } elseif ($this->view_mode=='methods') {
     $res=SQLSelect("SELECT methods.*, objects.TITLE as OBJECT FROM methods LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE methods.OBJECT_ID<>0 ORDER BY methods.EXECUTED DESC");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>METHOD</b></td>';
      echo '<td><b>PARAMS</b></td>';
      echo '<td><b>EXECUTED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['OBJECT'].'.'.$res[$i]['TITLE'];
      echo '</td>';
      echo '<td>';
      echo str_replace(';', '; ', $res[$i]['EXECUTED_PARAMS']).'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['EXECUTED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';     
    } elseif ($this->view_mode=='scripts') {
     $res=SQLSelect("SELECT scripts.* FROM scripts ORDER BY scripts.EXECUTED DESC");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>SCRIPT</b></td>';
      echo '<td><b>PARAMS</b></td>';
      echo '<td><b>EXECUTED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['TITLE'];
      echo '</td>';
      echo '<td>';
      echo str_replace(';', '; ', $res[$i]['EXECUTED_PARAMS']).'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['EXECUTED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';     
    }
  exit;
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
* Install
*
* Module installation routine
*
* @access private
*/
 function install() {
  parent::install();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDA0LCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>