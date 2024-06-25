<?php
/*
* @version 0.2 (08/27/2010)
*/

/**
* @package project
* @author Serge Dzheigalo <jey@unit.local>
*/
// control panel access validation
class control_access extends module {
 var $id;
// --------------------------------------------------------------------
 function __construct() {
  // setting module name
  $this->name="control_access";
  $this->title="Control Access";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
 }

// --------------------------------------------------------------------
function saveParams($data=1) {
 // saving current module data and data of all parent modules
 $data=array();
 return parent::saveParams($data);
}

function getParams() {
  global $action; // getting param
  global $mode;
  $this->mode=$mode;
  if (IsSet($action)) {
   $this->action=$action;
  }
}

// --------------------------------------------------------------------
 function run() {
  // running current module
  global $session;

  if ($this->owner->name!='panel' && $this->owner->name!='master') {
   echo "Unauthorized Access";
   exit;
  }

     if (!$this->mode && gr('mode')) {
         $this->mode=gr('mode');
     }


        if ($this->id=='1') {
                $this->mode='edit';
                global $id;
                global $mode;
                $id=$this->id;
                $mode='edit';
                $out['MASTER']=1;
        }


  if ($this->mode=='logoff') {
   UnSet($session->data['AUTHORIZED']);
   UnSet($session->data['USER_NAME']);
   UnSet($session->data['USERNAME']);
   UnSet($session->data['SITE_USERNAME']);
   UnSet($session->data['SITE_USER_ID']);
   Unset($session->data["cp_requested_url"]);
   $this->redirect("/");
  }

  if ($this->action=="enter") {

   global $md;
   global $login;
   if (!$session->data["cp_requested_url"] && ($md!='panel' || $action!='') && !$login) {
    $session->data["cp_requested_url"]=$_SERVER['REQUEST_URI'];
   }


   if ($this->mode=="check") {
    global $login;
    global $psw;
//    $user=SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='$login' AND PASSWORD='".($psw)."'");
    $user=SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='".DBSafe($login)."' AND PASSWORD='".DBSafe(md5($psw))."'");

//    $user=SQLSelectOne("SELECT * FROM admin_users WHERE 1");

    if (!IsSet($user['ID'])) {
     $out["ERRMESS"]="Wrong username and/or password";
    } else {
     $session->data['AUTHORIZED']=1;
     $session->data['USER_NAME']=$user['LOGIN'];
     $session->data['USER_LEVEL']=$user['PRIVATE'];
     $session->data['USER_ID']=$user['ID'];

     

     if (!$session->data["cp_requested_url"]) {
      if (file_exists(DIR_MODULES.'dashboard/dashboard.class.php')) {
       $this->owner->redirect("?action=dashboard");
      }
      $this->owner->redirect("?");
     } else {
      $this->owner->redirect($session->data["cp_requested_url"]);
     }


    }
   }

  } elseif ($this->action=="logged") {

   $out["USER_NAME"]=$session->data["USER_NAME"];


   $tmp=SQLSelectOne("SELECT ID FROM admin_users WHERE LOGIN='admin' AND PASSWORD='".md5('admin')."'");
   if ($tmp['ID']) {
    $out['WARNING']=1;
   }

    $user=SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='".$session->data["USER_NAME"]."'");

    if (!$user['ID']) {
     UnSet($session->data['AUTHORIZED']);
     UnSet($session->data['USER_NAME']);
     $session->save();
     $this->owner->redirect("?");
    }


    $modules    = SQLSelect("SELECT * FROM project_modules WHERE HIDDEN='0' ORDER BY CATEGORY, NAME");
    $modulesCnt = count($modules);

    for ($i = 0; $i < $modulesCnt; $i++)
    {
      if (
            (preg_match("/,".$modules[$i]['NAME'].",/i", @$user["ACCESS"])) ||
            (preg_match("/,".$modules[$i]['NAME']."$/i", @$user["ACCESS"])) ||
            (preg_match("/^".$modules[$i]['NAME'].",/i", @$user["ACCESS"])) ||
            (preg_match("/^".$modules[$i]['NAME']."$/i", @$user["ACCESS"])) ||
            0
         )
      {
         $new[] = $modules[$i];
      }
   }


   $on_row = 0;
   $newCnt = count($new);
   
   for ($i = 0; $i < $newCnt; $i++)
   {
      if ($new[$i]['CATEGORY'] != $new_category)
      {
         $new[$i]['NEWCATEGORY']=1;
         $new_category=$new[$i]['CATEGORY'];
         $on_row=0;
      }

      $on_row++;

      if ($on_row % 6 == 0 && $on_row >= 6)
      {
         $new[$i]['NEWROW']=1;
      }

      if (file_exists(ROOT.'img/admin/icons/ico_'.$new[$i]['NAME'].'.gif'))
      {
         $new[$i]['ICON']=ROOTHTML.'img/admin/icons/ico_'.$new[$i]['NAME'].'.gif';
      }
      else
      {
         $new[$i]['ICON']=ROOTHTML.'img/admin/icons/ico_default.gif';
      }
   }

   $out["MODULES"]=$new;

   if (file_exists(DIR_MODULES.'saverestore/saverestore.class.php')) {
    $out['CHECK_UPDATES']=1;
    global $check;
    if ($check) {
     include_once(DIR_MODULES.'saverestore/saverestore.class.php');
     $sv=new saverestore();
     $sv->checkUpdates($o);
     if ($o['NO_UPDATES'] || $o['ERROR_CHECK']) {
      echo "no";
     } else {
      echo "yes";
     }
     exit;
    }
   }


  } elseif ($this->action=="logoff") {
   UnSet($session->data['AUTHORIZED']);
   UnSet($session->data['USER_NAME']);
   UnSet($session->data['USERNAME']);
   $this->redirect("/");
  } elseif ($this->action=="admin") {
   global $mode;
   global $mode2;
   global $id;

   if (!$session->data['AUTHORIZED']) exit;

   if ($mode == "delete") {
    SQLExec("DELETE FROM admin_users WHERE ID='".$id."'");
    $this->redirect("?");
   }

   if ($mode == "edit") {
    $user=SQLSelectOne("SELECT * FROM admin_users WHERE ID='".$id."'");
    if ($mode2 == "update") {
     $ok=1;
     global $name;
     global $login;
     global $password;
     global $email;
     global $comments;
     global $sel;
     global $private;
     global $EMAIL_ORDERS;
     global $EMAIL_INVENTORY;


     $user['NAME']=$name;
     if (!checkGeneral($user['NAME'])) {
      $out["ERR_NAME"]=1;
      $ok=0;
     }
     $user['LOGIN']=$login;
     if (!checkGeneral($user['LOGIN'])) {
      $out["ERR_LOGIN"]=1;
      $ok=0;
     }

     if ($password!='' || !$user['ID']) {
      $user['PASSWORD']=$password;
      if (!checkGeneral($user['PASSWORD'])) {
       $out["ERR_PASSWORD"]=1;
       $ok=0;
      } else {
       $user['PASSWORD']=md5($user['PASSWORD']);
      }
     }

     $user['EMAIL']=$email;
     $user['COMMENTS']=$comments;
     $user['PRIVATE']=(int)$private;

     $user['EMAIL_ORDERS']=$EMAIL_ORDERS;
     $user['EMAIL_INVENTORY']=$EMAIL_INVENTORY;


     if (count($sel)>0) {
      $user['ACCESS']=join(",", $sel);    
     } else {
      $user['ACCESS']="";
     }

     if ($ok) {
      SQLUpdateInsert("admin_users", $user);
      $out["OK"]=1;
     }
    }

    $modules=SQLSelect("SELECT * FROM project_modules");
    $modulesCnt = count($modules);
    for($i=0;$i<$modulesCnt;$i++) {
     if (
      (preg_match("/,".$modules[$i]['NAME'].",/i", @$user["ACCESS"])) ||
      (preg_match("/,".$modules[$i]['NAME']."$/i", @$user["ACCESS"])) ||
      (preg_match("/^".$modules[$i]['NAME'].",/i", @$user["ACCESS"])) ||
      (preg_match("/^".$modules[$i]['NAME']."$/i", @$user["ACCESS"])) ||
      0
     )
     {
      $modules[$i]["SELECTED"]=1;
     }
     if (($i+1)%3==0) {
      $modules[$i]['NEWR']=1;
     }
    }
    $user["MODULES"]=$modules;
    outHash($user, $out);
   }

   $users=SQlSelect("SELECT * FROM admin_users ORDER BY ID DESC");
   $out["USERS"]=$users;

  }

  $out["MODE"]=$mode;
  $out["ACTION"]=$this->action;

  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;

 }

// --------------------------------------------------------------------
   function checkAccess($action = "", $log = 0)
   {

      if (defined('NO_DATABASE_CONNECTION')) {
       return 1;
      }
      global $session;

      if ($session->data['USER_ID'] == 1) 
         return 1;
  
      $user = SQLSelectOne("SELECT ID FROM admin_users WHERE LOGIN='".$session->data['USER_NAME']."'");
      if (!$user['ID'])
      {
         UnSet($session->data['AUTHORIZED']);
         UnSet($session->data['USER_NAME']);
         $this->redirect("?");
      }

      if ($action != "") 
         $user=SQLSelectOne("SELECT ID FROM admin_users WHERE LOGIN='".$session->data['USER_NAME']."' AND (ACCESS LIKE '$action' OR ACCESS LIKE '$action,%' OR ACCESS LIKE '%,$action' OR ACCESS LIKE '%,$action,%')");
    
      if (isset($user['ID']))
         return 1;
  
      return 0;
   }

// --------------------------------------------------------------------
 function dbInstall($data) {
  $data = <<<EOD
   admin_users: ID tinyint(3) unsigned NOT NULL auto_increment
   admin_users: NAME varchar(100)  DEFAULT '' NOT NULL 
   admin_users: LOGIN varchar(100)  DEFAULT '' NOT NULL 
   admin_users: PASSWORD varchar(100)  DEFAULT '' NOT NULL 
   admin_users: EMAIL varchar(100)  DEFAULT '' NOT NULL 
   admin_users: COMMENTS text
   admin_users: ACCESS text
   admin_users: PRIVATE tinyint(3) unsigned DEFAULT '0' NOT NULL
   admin_users: EMAIL_ORDERS tinyint(3) unsigned NOT NULL default '0'
   admin_users: EMAIL_INVENTORY tinyint(3) unsigned NOT NULL default '0'

EOD;
  parent::dbInstall($data);
 }

// --------------------------------------------------------------------
}
?>