<?
/**
* Scenes 
*
* Scenes
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 10:05:38 [May 24, 2012])
*/
Define('DEF_TYPE_OPTIONS', 'img=Image|html=HTML'); // options for 'TYPE'
Define('DEF_CONDITION_OPTIONS', '1=Equa|2=More|3=Less|4=Not equal'); // options for 'CONDITION'
//
//
class scenes extends module {
/**
* scenes
*
* Module class constructor
*
* @access private
*/
function scenes() {
  $this->name="scenes";
  $this->title="<#LANG_MODULE_SCENES#>";
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
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
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
  global $data_source;
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
  if (isset($data_source)) {
   $this->data_source=$data_source;
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

  $this->checkSettings();

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
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if (IsSet($this->scene_id)) {
   $out['IS_SET_SCENE_ID']=1;
  }
  if (IsSet($this->element_id)) {
   $out['IS_SET_ELEMENT_ID']=1;
  }
  if (IsSet($this->script_id)) {
   $out['IS_SET_SCRIPT_ID']=1;
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
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='scenes' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_scenes') {
   $this->search_scenes($out);
  }
  if ($this->view_mode=='edit_scenes') {
   $this->edit_scenes($out, $this->id);
  }
  if ($this->view_mode=='delete_scenes') {
   $this->delete_scenes($this->id);
   $this->redirect("?data_source=scenes");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='elements') {
  if ($this->view_mode=='' || $this->view_mode=='search_elements') {
   $this->search_elements($out);
  }
  if ($this->view_mode=='edit_elements') {
   $this->edit_elements($out, $this->id);
  }
  if ($this->view_mode=='delete_elements') {
   $this->delete_elements($this->id);
   $this->redirect("?data_source=elements");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='elm_states') {
  if ($this->view_mode=='' || $this->view_mode=='search_elm_states') {
   $this->search_elm_states($out);
  }
  if ($this->view_mode=='edit_elm_states') {
   $this->edit_elm_states($out, $this->id);
  }
  if ($this->view_mode=='delete_elm_states') {
   $this->delete_elm_states($this->id);
   $this->redirect("?data_source=elm_states");
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

 global $ajax;
 if ($ajax) {
    global $op;
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');
    if ($op=='checkAllStates') {
     $states=SQLSelect("SELECT elm_states.ID, elm_states.TITLE, elm_states.HTML, elements.SCENE_ID, elm_states.SWITCH_SCENE, elements.TYPE FROM elm_states, elements WHERE elm_states.ELEMENT_ID=elements.ID");
     $total=count($states);
     for($i=0;$i<$total;$i++) {
      $states[$i]['STATE']=$this->checkState($states[$i]['ID']);
      if ($states[$i]['TYPE']=='html') {
       $states[$i]['HTML']=processTitle($states[$i]['HTML'], $this);
      }
     }
     echo json_encode($states);
    }
    if ($op=='click') {
     global $id;
     $state=SQLSelectOne("SELECT * FROM elm_states WHERE ID='".$id."'");
     $params=array('STATE'=>$state['TITLE']);
     if ($state['ACTION_OBJECT'] && $state['ACTION_METHOD']) {
      callMethod($state['ACTION_OBJECT'].'.'.$state['ACTION_METHOD'], $params);
     }
     if ($state['SCRIPT_ID']) {
      runScript($state['SCRIPT_ID'], $params);
     }
     echo "OK";

    }
    exit;
 }

 $this->admin($out);
}

 function checkSettings() {
  $settings=array(
   array(
    'NAME'=>'SCENES_WIDTH', 
    'TITLE'=>'Scene width', 
    'TYPE'=>'text',
    'DEFAULT'=>'803'
    ),
   array(
    'NAME'=>'SCENES_HEIGHT', 
    'TITLE'=>'Scene height',
    'TYPE'=>'text',
    'DEFAULT'=>'606'
    )
   );


   foreach($settings as $k=>$v) {
    $rec=SQLSelectOne("SELECT ID FROM settings WHERE NAME='".$v['NAME']."'");
    if (!$rec['ID']) {
     $rec['NAME']=$v['NAME'];
     $rec['VALUE']=$v['DEFAULT'];
     $rec['DEFAULTVALUE']=$v['DEFAULT'];
     $rec['TITLE']=$v['TITLE'];
     $rec['TYPE']=$v['TYPE'];
     $rec['ID']=SQLInsert('settings', $rec);
     Define('SETTINGS_'.$rec['NAME'], $v['DEFAULT']);
    }
   }

 }

/**
* scenes search
*
* @access public
*/
 function search_scenes(&$out) {
  require(DIR_MODULES.$this->name.'/scenes_search.inc.php');
 }
/**
* scenes edit/add
*
* @access public
*/
 function edit_scenes(&$out, $id) {
  require(DIR_MODULES.$this->name.'/scenes_edit.inc.php');
 }
/**
* scenes delete record
*
* @access public
*/
 function delete_scenes($id) {
  $rec=SQLSelectOne("SELECT * FROM scenes WHERE ID='$id'");
  // some action for related tables
  $elements=SQLSelect("SELECT ID FROM elements WHERE SCENE_ID='".$rec['ID']."'");
  $total=count($elements);
  for($i=0;$i<$total;$i++) {
   $this->delete_elements($elements[$i]['ID']);
  }

  SQLExec("DELETE FROM scenes WHERE ID='".$rec['ID']."'");
 }
/**
* elements search
*
* @access public
*/
 function search_elements(&$out) {
  require(DIR_MODULES.$this->name.'/elements_search.inc.php');
 }
/**
* elements edit/add
*
* @access public
*/
 function edit_elements(&$out, $id) {
  require(DIR_MODULES.$this->name.'/elements_edit.inc.php');
 }
/**
* elements delete record
*
* @access public
*/
 function delete_elements($id) {
  $rec=SQLSelectOne("SELECT * FROM elements WHERE ID='$id'");
  // some action for related tables
  $states=SQLSelect("SELECT ID FROM elm_states WHERE ELEMENT_ID='".$rec['ID']."'");
  $total=count($states);
  for($i=0;$i<$total;$i++) {
   $this->delete_elm_states($states[$i]['ID']);
  }
  SQLExec("DELETE FROM elements WHERE ID='".$rec['ID']."'");
 }
/**
* elm_states search
*
* @access public
*/
 function search_elm_states(&$out) {
  require(DIR_MODULES.$this->name.'/elm_states_search.inc.php');
 }
/**
* elm_states edit/add
*
* @access public
*/
 function edit_elm_states(&$out, $id) {
  require(DIR_MODULES.$this->name.'/elm_states_edit.inc.php');
 }
/**
* elm_states delete record
*
* @access public
*/
 function delete_elm_states($id) {
  $rec=SQLSelectOne("SELECT * FROM elm_states WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM elm_states WHERE ID='".$rec['ID']."'");
 }


/**
* Title
*
* Description
*
* @access public
*/
 function checkState($id) {
  $rec=SQLSelectOne("SELECT * FROM elm_states WHERE ID='".$id."'");
  if (!$rec['IS_DYNAMIC']) {
   $status=1;
  } elseif ($rec['IS_DYNAMIC']==1) {
   if ($rec['LINKED_OBJECT']!='' && $rec['LINKED_PROPERTY']!='') {
    $value=gg(trim($rec['LINKED_OBJECT']).'.'.trim($rec['LINKED_PROPERTY']));
   } elseif ($rec['LINKED_PROPERTY']!='') {
    $value=gg($rec['LINKED_PROPERTY']);
   } else {
    $value=-1;
   }

   if ($rec['CONDITION']==1 && $value==$rec['CONDITION_VALUE']) {
    $status=1;
   } elseif ($rec['CONDITION']==2 && (float)$value>(float)$rec['CONDITION_VALUE']) {
    $status=1;
   } elseif ($rec['CONDITION']==3 && (float)$value<(float)$rec['CONDITION_VALUE']) {
    $status=1;
   } elseif ($rec['CONDITION']==4 && $value!=$rec['CONDITION_VALUE']) {
    $status=1;
   } else {
    $status=0;
   }

  } elseif ($rec['IS_DYNAMIC']==2) {

   $display=0;
   eval($rec['CONDITION_ADVANCED']);
   $status=$display;

  }

  if ($rec['CURRENT_STATE']!=$status) {
   $rec['CURRENT_STATE']=$status;
   SQLExec('UPDATE elm_states SET CURRENT_STATE='.$rec['CURRENT_STATE'].' WHERE ID='.(int)$rec['ID']);
  }

  return $status;
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
  if (!Is_Dir(ROOT."./cms/scenes")) {
   mkdir(ROOT."./cms/scenes", 0777);
  }
  if (!Is_Dir(ROOT."./cms/scenes/elements")) {
   mkdir(ROOT."./cms/scenes/elements", 0777);
  }
  if (!Is_Dir(ROOT."./cms/scenes/backgrounds")) {
   mkdir(ROOT."./cms/scenes/backgrounds", 0777);
  }
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
  SQLExec('DROP TABLE IF EXISTS scenes');
  SQLExec('DROP TABLE IF EXISTS elements');
  SQLExec('DROP TABLE IF EXISTS elm_states');
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
scenes - Scenes
elements - Elements
elm_states - Element states
*/
  $data = <<<EOD
 scenes: ID int(10) unsigned NOT NULL auto_increment
 scenes: TITLE varchar(255) NOT NULL DEFAULT ''
 scenes: BACKGROUND varchar(255) NOT NULL DEFAULT ''
 scenes: PRIORITY int(10) NOT NULL DEFAULT '0'

 elements: ID int(10) unsigned NOT NULL auto_increment
 elements: SCENE_ID int(10) NOT NULL DEFAULT '0'
 elements: TITLE varchar(255) NOT NULL DEFAULT ''
 elements: TYPE varchar(255) NOT NULL DEFAULT ''
 elements: TOP int(10) NOT NULL DEFAULT '0'
 elements: LEFT int(255) NOT NULL DEFAULT '0'
 elements: WIDTH int(255) NOT NULL DEFAULT '0'
 elements: HEIGHT int(255) NOT NULL DEFAULT '0'
 elements: CROSS_SCENE int(3) NOT NULL DEFAULT '0'
 elements: BACKGROUND int(3) NOT NULL DEFAULT '1'
 elements: JAVASCRIPT text

 elm_states: ID int(10) unsigned NOT NULL auto_increment
 elm_states: ELEMENT_ID int(10) NOT NULL DEFAULT '0'
 elm_states: TITLE varchar(255) NOT NULL DEFAULT ''
 elm_states: IMAGE varchar(255) NOT NULL DEFAULT ''
 elm_states: HTML text
 elm_states: IS_DYNAMIC int(3) NOT NULL DEFAULT '0'
 elm_states: CURRENT_STATE int(3) NOT NULL DEFAULT '0'
 elm_states: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 elm_states: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 elm_states: ACTION_OBJECT varchar(255) NOT NULL DEFAULT ''
 elm_states: ACTION_METHOD varchar(255) NOT NULL DEFAULT ''
 elm_states: CONDITION int(3) NOT NULL DEFAULT '0'
 elm_states: CONDITION_VALUE varchar(255) NOT NULL DEFAULT ''
 elm_states: CONDITION_ADVANCED text
 elm_states: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 elm_states: SWITCH_SCENE int(3) NOT NULL DEFAULT '0'
 elm_states: CURRENT_STATUS int(3) NOT NULL DEFAULT '0'
EOD;

  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDI0LCAyMDEyIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>