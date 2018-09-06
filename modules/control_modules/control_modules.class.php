<?php
/*
* @version 0.1 (auto-set)
*/

/**
* @package project
* @author Serge Dzheigalo <jey@unit.local>
*/
// modules installed control
class control_modules extends module {
 var $modules; // all modules list
// --------------------------------------------------------------------
 function __construct() {
  // setting module name
  $this->name="control_modules";
  $this->title="<#LANG_MODULE_MODULES#>";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
 }

// --------------------------------------------------------------------
function saveParams($data=1) {
 // saving current module data and data of all parent modules
 $p=array();
 return parent::saveParams($p);
}

function getParams() {
  global $action; // getting param
  global $mode;
  $this->mode=$mode;
  $this->action=$action;
}

// --------------------------------------------------------------------
 function run() {
  // running current module
  global $mode;
  global $name;

  $rep_ext="";
  if (preg_match('/\.dev/is', $_SERVER['HTTP_HOST'])) $rep_ext='.dev';
  if (preg_match('/\.jbk/is', $_SERVER['HTTP_HOST'])) $rep_ext='.jbk';
  if (preg_match('/\.bk/is', $_SERVER['HTTP_HOST'])) $rep_ext='.bk';

  if ($rep_ext) {
   $out['LOCAL_PROJECT']=1;
   $out['REP_EXT']=$rep_ext;
   $out['HOST']=$_SERVER['HTTP_HOST'];
   $out['DOCUMENT_ROOT']=dirname($_SERVER['SCRIPT_FILENAME']);
  }

  if ($mode=="edit") {
   global $mode2;
   $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$name."'");
   $rec['NAME']=$name;
   if ($mode2 == "update") {
    global $title;
    global $category;
    $rec['TITLE']=$title;
    $rec['CATEGORY']=$category;
    SQLUpdate("project_modules", $rec);
    $this->redirect("?name=$name&mode=edit");
   } elseif ($mode2=="show") {
    if ($rec['HIDDEN']) {
     $rec['HIDDEN']=0;
    } else {
     $rec['HIDDEN']=1;
    }
    SQLUpdate('project_modules', $rec);
    $this->redirect("?");

   } elseif ($mode2=="ignore") {
    SQLExec("DELETE FROM ignore_updates WHERE NAME LIKE '".DBSafe($rec['NAME'])."'");
    $tmp=array();
    $tmp['NAME']=$rec['NAME'];
    SQLInsert('ignore_updates', $tmp);
    $this->redirect("?");
   } elseif ($mode2=="unignore") {
    SQLExec("DELETE FROM ignore_updates WHERE NAME LIKE '".DBSafe($rec['NAME'])."'");
    $this->redirect("?");
   } elseif ($mode2=="install") {
    $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$name."'");
    SQLExec("DELETE FROM project_modules WHERE NAME='".$name."'");
    @unlink(DIR_MODULES.$name."/installed");
    include_once(DIR_MODULES.$name."/".$name.".class.php");
    $obj="\$object$i";
    $code.="$obj=new ".$name.";\n";
    @eval($code);
    // add module to control access
    global $session;
    $user=SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='".DBSafe($session->data["USER_NAME"])."'");
    if ($user['ID'] && !Is_Integer(strpos($user["ACCESS"], $name))) {
     if ($user["ACCESS"]!='') {
      $user["ACCESS"].=",$name";
     } else {
      $user["ACCESS"]=$name;
     }
     SQLUpdate('admin_users', $user);
    }
    SQLExec("UPDATE project_modules SET HIDDEN='".(int)$rec['HIDDEN']."' WHERE NAME='".$name."'");
    // redirect to edit
    $this->redirect("?name=$name&mode=edit");
   } elseif ($mode2=='uninstall') {
    SQLExec("DELETE FROM project_modules WHERE NAME='".$name."'");
    @unlink(DIR_MODULES.$name."/installed");

    if (file_exists(DIR_MODULES.$name."/".$name.".class.php")) {
     include_once(DIR_MODULES.$name."/".$name.".class.php");
     $obj="\$object$i";
     $code.="$obj=new ".$name.";\n";
     $code.="$obj"."->uninstall();";
     eval ($code);
    }


    if ($out['LOCAL_PROJECT']) {
     $this->redirect("?mode=repository_uninstall&module=$name");
    } else {
     $this->redirect("?");
    }
   }
   outHash($rec, $out);
  }

  if ($mode=='repository_uninstall') {
    global $module;
    $out['MODULE']=$module;
  }

  $out["MODE"]=$mode;

  $this->getModulesList();
  $lst=$this->modules;
  $lstCnt = count($lst);

  for ($i = 0; $i < $lstCnt ;$i++)
  {
   $rec=SQLSelectOne("SELECT *, DATE_FORMAT(ADDED, '%M %d, %Y (%H:%i)') as DAT FROM project_modules WHERE NAME='".$lst[$i]['FILENAME']."'");
   if (IsSet($rec['ID'])) {
    outHash($rec, $lst[$i]);
   }
   $ignored=SQLSelectOne("SELECT ID FROM ignore_updates WHERE NAME LIKE '".DBSafe($lst[$i]['NAME'])."'");
   if ($ignored['ID']) {
    $lst[$i]['IGNORED']=1;
   }
  }

  $out["MODULES"]=$lst;

  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;

 }

// --------------------------------------------------------------------
 function getModulesList() {
  $dir=openDir(DIR_MODULES);
  $lst=array();
  while ($file = readDir($dir)) {
   if ((Is_Dir(DIR_MODULES."$file")) && ($file!=".") && ($file!="..")) {
    $rec=array();
    $rec['FILENAME']=$file;
    $lst[]=$rec;
   }
  }

  usort($lst, function ($a,$b) {
   return strcmp($a["FILENAME"], $b["FILENAME"]);
  });

  $this->modules=$lst;
  return $lst;
 }

function install($parent_name = "")
{
   parent::install($parent_name);

   global $db;
 if (!is_object($db) || !$db->connected) {
  return false;
 }

   $this->getModulesList();

   $lst    = $this->modules;

   $prelist=array('settings','objects', 'devices');
   $prelist=array_reverse($prelist);
   foreach($prelist as $v) {
    $rec=array('FILENAME'=>$v);
    array_unshift($lst, $rec);
   }

   $lstCnt = count($lst);

   SQLExec("ALTER TABLE `project_modules` CHANGE `ID` `ID` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT");

   for ($i = 0; $i < $lstCnt; $i++)
   {
      if (file_exists(DIR_MODULES . $lst[$i]['FILENAME'] . "/" . $lst[$i]['FILENAME'] . ".class.php"))
      {
         if ($lst[$i]['FILENAME'] == 'control_modules')
            continue;

         $installedFile = DIR_MODULES . $lst[$i]['FILENAME'] . "/installed";
         if (file_exists($installedFile))
            unlink($installedFile);

         include_once(DIR_MODULES . $lst[$i]['FILENAME'] . "/" . $lst[$i]['FILENAME'] . ".class.php");
         $obj = "\$object$i";
         $code = "$obj=new " . $lst[$i]['FILENAME'] . ";\n";
         //echo "Installing ".$lst[$i]['FILENAME']."\n";
         @eval("$code");
      }
   }


   SQLExec("UPDATE project_modules SET HIDDEN=0 WHERE NAME LIKE '" . $this->name . "'");
}

// --------------------------------------------------------------------
 function dbInstall($data) {
  $data = <<<EOD

   project_modules: ID tinyint(3) unsigned NOT NULL auto_increment
   project_modules: NAME varchar(50)  DEFAULT '' NOT NULL
   project_modules: TITLE varchar(100)  DEFAULT '' NOT NULL
   project_modules: CATEGORY varchar(50)  DEFAULT '' NOT NULL
   project_modules: PARENT_NAME varchar(50)  DEFAULT '' NOT NULL
   project_modules: DATA text
   project_modules: HIDDEN int(3)  DEFAULT '0' NOT NULL
   project_modules: PRIORITY int(10)  DEFAULT '0' NOT NULL
   project_modules: ADDED timestamp

   ignore_updates: ID tinyint(3) unsigned NOT NULL auto_increment
   ignore_updates: NAME varchar(50)  DEFAULT '' NOT NULL


EOD;
  parent::dbInstall($data);
 }

// --------------------------------------------------------------------
}
