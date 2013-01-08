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
function saveParams() {
 $p=array();
 $p["action"]=$this->action;
 $p["print"]=$this->print;
 return parent::saveParams($p);
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

  if (IsSet($session->data["AUTHORIZED"])) {
   $this->authorized=1;
  }

  if ($this->print) {
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

   $modules=SQLSelect("SELECT * FROM project_modules WHERE `HIDDEN`='0' ORDER BY CATEGORY, `PRIORITY`, `TITLE`");
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
