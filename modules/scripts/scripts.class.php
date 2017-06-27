<?php
/**
* Scripts 
*
* Scripts
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.4 (wizard, 18:09:04 [Sep 13, 2010])
*/
//
//
class scripts extends module {
/**
* scripts
*
* Module class constructor
*
* @access private
*/
function scripts() {
  $this->name="scripts";
  $this->title="<#LANG_MODULE_SCRIPTS#>";
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
  global $data_source;
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
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['DATA_SOURCE']=$this->data_source;
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
   * Title
   *
   * Description
   *
   * @access public
   */
  function runScript($id, $params = '')
  {
    $rec = SQLSelectOne("SELECT * FROM scripts WHERE ID='" . (int)$id . "' OR TITLE LIKE '" . DBSafe($id) . "'");
    if ($rec['ID']) {
      $rec['EXECUTED'] = date('Y-m-d H:i:s');
      if ($params) {
        $rec['EXECUTED_PARAMS'] = serialize($params);
      }
      SQLUpdate('scripts', $rec);

      try {
        $code = $rec['CODE'];
        $success = eval($code);
        if ($success === false) {
          //getLogger($this)->error(sprintf('Error in script "%s". Code: %s', $rec['TITLE'], $code));
          registerError('script', sprintf('Error in script "%s". Code: %s', $rec['TITLE'], $code));
        }
        return $success;
      } catch (Exception $e) {
        //getLogger($this)->error(sprintf('Error in script "%s"', $rec['TITLE']), $e);
        registerError('script', sprintf('Error in script "%s": '.$e->getMessage(), $rec['TITLE']));
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
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='scripts' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_scripts') {
   $this->search_scripts($out);
  }
  if ($this->view_mode=='run_script') {
   $this->runScript($this->id);
   exit;
   //$this->redirect("?");
  }

  if ($this->view_mode=='clone' && $this->id) {
   $this->clone_script($this->id);
  }

  if ($this->view_mode=='edit_scripts') {
   $this->edit_scripts($out, $this->id);
  }
  if ($this->view_mode=='delete_scripts') {
   $this->delete_scripts($this->id);
   $this->redirect("?");
  }
 }

 if ($this->data_source=='categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_categories') {
   //$this->search_scripts($out);
   $result=SQLSelect("SELECT * FROM script_categories ORDER BY TITLE");
   if ($result) {
    $out['RESULT']=$result;
   }
  }
  if ($this->view_mode=='edit_categories') {
   $this->edit_categories($out, $this->id);
  }
  if ($this->view_mode=='delete_categories') {
   $this->delete_categories($this->id);
   $this->redirect("?data_source=".$this->data_source);
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
 function clone_script($id) {
  $rec=SQLSelectOne("SELECT * FROM scripts WHERE ID='".(int)$id."'");
  $rec['TITLE'].='_copy';
  unset($rec['ID']);
  unset($rec['EXECUTED']);
  $rec['ID']=SQLInsert('scripts', $rec);
  $this->redirect("?view_mode=edit_scripts&id=".$rec['ID']);
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
* scripts search
*
* @access public
*/
 function search_scripts(&$out) {
  require(DIR_MODULES.$this->name.'/scripts_search.inc.php');
 }
/**
* scripts edit/add
*
* @access public
*/
 function edit_scripts(&$out, $id) {
  require(DIR_MODULES.$this->name.'/scripts_edit.inc.php');
 }

 function edit_categories(&$out, $id) {
  require(DIR_MODULES.$this->name.'/categories_edit.inc.php');
 }
/**
* scripts delete record
*
* @access public
*/
 function delete_scripts($id) {
  $rec=SQLSelectOne("SELECT * FROM scripts WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM scripts WHERE ID='".$rec['ID']."'");
 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkScheduledScripts() {
  $scripts=SQLSelect("SELECT ID, TITLE, RUN_DAYS, RUN_TIME FROM scripts WHERE RUN_PERIODICALLY=1 AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(EXECUTED))>1200");



  $total=count($scripts);
  for($i=0;$i<$total;$i++) {

   $rec=$scripts[$i];

   if ($rec['RUN_DAYS']==='') {
    continue;
   }

   $run_days=explode(',', $rec['RUN_DAYS']);
   if (!in_array(date('w'), $run_days)) {
    continue;
   }

   $tm=strtotime(date('Y-m-d').' '.$rec['RUN_TIME']);

   $diff=time()-$tm;

   if ($diff<0 || $diff>=10*60) {
    continue;
   }

   runScriptSafe($rec['TITLE']);

   $rec['DIFF']=$diff;

   //print_r($rec);

  }
  //print_r($scripts);

 }


 function delete_categories($id) {
  $rec=SQLSelectOne("SELECT * FROM script_categories WHERE ID='$id'");
  // some action for related tables
  SQLExec("UPDATE scripts SET CATEGORY_ID=0 WHERE CATEGORY_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM script_categories WHERE ID='".$rec['ID']."'");
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
  SQLExec('DROP TABLE IF EXISTS scripts');
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
scripts - Scripts
*/
  $data = <<<EOD
 scripts: ID int(10) unsigned NOT NULL auto_increment
 scripts: TITLE varchar(255) NOT NULL DEFAULT ''
 scripts: DESCRIPTION text
 scripts: CODE text
 scripts: TYPE int(3) unsigned NOT NULL DEFAULT 0
 scripts: CATEGORY_ID int(10) unsigned NOT NULL DEFAULT 0
 scripts: XML text
 scripts: EXECUTED datetime
 scripts: EXECUTED_PARAMS varchar(255)
 scripts: RUN_PERIODICALLY int(3) unsigned NOT NULL DEFAULT 0
 scripts: RUN_DAYS char(30) NOT NULL DEFAULT ''
 scripts: RUN_TIME char(30) NOT NULL DEFAULT ''

 script_categories: ID int(10) unsigned NOT NULL auto_increment
 script_categories: TITLE varchar(255) NOT NULL DEFAULT ''


 safe_execs: ID int(10) unsigned NOT NULL auto_increment
 safe_execs: COMMAND text
 safe_execs: EXCLUSIVE int(3) NOT NULL DEFAULT 0
 safe_execs: PRIORITY int(10) NOT NULL DEFAULT 0
 safe_execs: ADDED datetime


EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDEzLCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>