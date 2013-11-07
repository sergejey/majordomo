<?php
/*
* @version 0.1 (auto-set)
*/

/**
* @author Serge Dzheigalo <jey@unit.local>
* @package project
*/

 class panel extends module {

  var $action;

// --------------------------------------------------------------------
  function panel() {
   $this->name="panel";
  }

// --------------------------------------------------------------------
function saveParams($data=1) {
 $data=array();
 $data["action"]=$this->action;
 $data["print"]=$this->print;
 return parent::saveParams($data);
}

// --------------------------------------------------------------------
function getParams() {
 global $action;
 $this->action=$action;
}

// --------------------------------------------------------------------
 function run() {
  global $session;
  Define('ALTERNATIVE_TEMPLATES', 'templates_alt');

  global $action;
  if (!$this->action && $action) {
   $this->action=$action;
  }

  if (!$session->data["AUTHORIZED"] && $session->data['USERNAME']) {
   $user=SQLSelectOne("SELECT * FROM users WHERE USERNAME LIKE '".DBSafe($session->data['USERNAME'])."'");
   if ($user['IS_ADMIN']) {
    $user=SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='admin'");
    $session->data['USER_NAME']=$user['LOGIN'];
    $session->data['USER_LEVEL']=$user['PRIVATE'];
    $session->data['USER_ID']=$user['ID'];
    $session->data["AUTHORIZED"]=1;
   }
  }

  if (IsSet($session->data["AUTHORIZED"])) {
   $this->authorized=1;
  }

  if ($this->print || $_GET['print']) {
   $this->print=1;
   $out['PRINT']=1;
  }

  $out["TODAY"]=date('l, F d, Y');
  $out["AUTHORIZED"]=$this->authorized;

  if ($this->authorized) {

   include_once(DIR_MODULES."control_access/control_access.class.php");
   $acc=new control_access();
   if (!$acc->checkAccess($this->action, 1)) {
    $this->redirect("?");
   }

   if ($_SERVER['REQUEST_METHOD']=='POST') {
    clearCache(0);
   }

   $modules=SQLSelect("SELECT * FROM project_modules WHERE `HIDDEN`='0' ORDER BY FIELD(CATEGORY, '<#LANG_SECTION_OBJECTS#>', '<#LANG_SECTION_DEVICES#>', '<#LANG_SECTION_APPLICATIONS#>', '<#LANG_SECTION_SETTINGS#>', '<#LANG_SECTION_SYSTEM#>'), `PRIORITY`, `TITLE`");
   $old_cat='some_never_should_be_category_name';
   for($i=0;$i<count($modules);$i++) {
    if ($modules[$i]['NAME'] == $this->action) {
     $modules[$i]['SELECTED']=1;
    }
    if ($modules[$i]['CATEGORY']!=$old_cat) {
     $modules[$i]['NEW_CATEGORY']=1;
     $old_cat=$modules[$i]['CATEGORY'];
     if ($i>0) {
      //echo $last_allow."<br>";
      $modules[$last_allow]['LAST_IN_CATEGORY']=1;
     }
    }

    if (!$acc->checkAccess($modules[$i]['NAME'])) {
     $modules[$i]['DENIED']=1;
    } else {
     $last_allow=$i;
    }

    if (file_exists(ROOT.'img/admin/icons/ico_'.$modules[$i]['NAME'].'_sm.gif')) {
     $modules[$i]['ICON_SM']=ROOTHTML.'img/admin/icons/ico_'.$modules[$i]['NAME'].'_sm.gif';
    } else {
     $modules[$i]['ICON_SM']=ROOTHTML.'img/admin/icons/ico_default_sm.gif';
    }
   }
   $modules[$last_allow]['LAST_IN_CATEGORY']=1;
   $out["SUB_MODULES"]=$modules;
  }

  $out["ACTION"]=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name.".html", $this->data, $this);
  return $p->result;

 }

// --------------------------------------------------------------------
 }
?>
