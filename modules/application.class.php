<?php
/*
* @version 0.3 (auto-set)
*/

/**
* @author Serge Dzheigalo <jey@unit.local>
* @package project
*/

 class application extends module {

  var $action;
  var $member_id;

// --------------------------------------------------------------------
  function __construct() {
   $this->name="application";
 }

// --------------------------------------------------------------------

function saveParams($data = 1) {
 $p=array();
 if (isset($this->action) && $this->action) {
  $p["action"]=$this->action;
 }
 if (isset($this->doc_name) && $this->doc_name) {
  $p['doc_name']=$this->doc_name;
 }
 if (isset($this->ajax) && $this->ajax) {
  $p['ajax']=$this->ajax;
 }
 if (isset($this->popup) && $this->popup) {
  $p['popup']=$this->popup;
 }
 if (isset($this->app_action) && $this->app_action) {
  $p['app_action']=$this->app_action;
 }
 return parent::saveParams($p);
 }

// --------------------------------------------------------------------
function getParams() {
 global $action;
 if ($action!='') $this->action=$action;
}

// --------------------------------------------------------------------
  function run() {
  global $session;

   Define('ALTERNATIVE_TEMPLATES', 'templates_alt');

   $theme = SETTINGS_THEME;
   if ($this->action=='layouts' && $this->id) {
    $layout_rec=SQLSelectOne("SELECT * FROM layouts WHERE ID=".(int)$this->id);
    if ($layout_rec['THEME']) {
     $theme=$layout_rec['THEME'];
    }
    if ($layout_rec['BACKGROUND_IMAGE']) {
     $out['BODY_CSS'].='background-image:url('.$layout_rec['BACKGROUND_IMAGE'].')';
    }
   }
   Define('THEME',$theme);

   if ($this->action=='ajaxgetglobal') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');
    $_GET['var']=str_replace('%', '', $_GET['var']);
    $res['DATA']=getGlobal($_GET['var']);
    echo json_encode($res);
    exit;
   }

   if ($this->action=='ajaxsetglobal') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');
    $_GET['var']=str_replace('%', '', $_GET['var']);
    setGlobal($_GET['var'], $_GET['value']);
    $res['DATA']='OK';
    echo json_encode($res);
    exit;
   }
   
   if ($this->action=='getlatestnote') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');

    $msg=SQLSelectOne("SELECT * FROM shouts WHERE MEMBER_ID=0 ORDER BY ID DESC LIMIT 1");
    $res=array();
    $res['DATA']=$msg['MESSAGE'];
    echo json_encode($res);
    exit;
   }

   if ($this->action=='getlatestmp3') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');

    if ($dir = @opendir(ROOT."cms/cached/voice")) {
       while (($file = readdir($dir)) !== false) { 
       if (preg_match('/\.mp3$/', $file)) {
        $mtime=filemtime(ROOT."cms/cached/voice/".$file);
        /*
        if ((time()-$mtime)>60*60*24 && $mtime>0) {
         //old file, delete?
         unlink(ROOT."cms/cached/voice".$file);
        } else {
        }
        */
        $files[]=array('FILENAME'=>$file, 'MTIME'=>$mtime);
       }

       if (preg_match('/\.wav$/', $file)) {
        $mtime=filemtime(ROOT."cms/cached/voice/".$file);
        /*
        if ((time()-$mtime)>60*60*24 && $mtime>0) {
         //old file, delete?
         unlink(ROOT."cms/cached/voice/".$file);
        }
        */
       }

      }
     closedir($dir); 
    } 

    //print_r($files);exit;

    if (is_array($files)) {
     function sortFiles($a, $b) {
         if ($a['MTIME'] == $b['MTIME']) return 0; 
         return ($a['MTIME'] > $b['MTIME']) ? -1 : 1; 
     }
     usort($files, 'sortFiles');
     echo '/cms/cached/voice/'.$files[0]['FILENAME'];
    }

    exit;
   }

   if (!defined('SETTINGS_SITE_LANGUAGE') || !defined('SETTINGS_SITE_TIMEZONE') || !defined('SETTINGS_HOOK_BEFORE_SAY')) {
    $this->action='first_start';
   }

   if ($this->action=='first_start') {
    include(DIR_MODULES.'first_start.php');
   } elseif ($this->action=='apps') {
    include(DIR_MODULES.'apps.php');
   }

   $out["ACTION"]=$this->action;
   $out["TODAY"]=date('l, F d, Y');
   $out["DOC_NAME"]=$this->doc_name;

   global $username;
   
   if ($username) {
       $user=SQLSelectOne("SELECT * FROM users WHERE USERNAME LIKE '".DBSafe($username)."'");
       if (hash('sha512', '') == $user['PASSWORD']) {
           $session->data['SITE_USERNAME']=$user['USERNAME'];
           $session->data['SITE_USER_ID']=$user['ID'];
       } else {
           if (!isset($_SERVER['PHP_AUTH_USER'])) {
               header("WWW-Authenticate: Basic realm=\"" . PROJECT_TITLE . "\"");
               header('HTTP/1.0 401 Unauthorized');
               echo 'Password required!';
               exit;
           } else {
               if ($_SERVER['PHP_AUTH_USER']==$user['USERNAME'] && hash('sha512', $_SERVER['PHP_AUTH_PW']) ==$user['PASSWORD']) {
                   $session->data['SITE_USERNAME']=$user['USERNAME'];
                   $session->data['SITE_USER_ID']=$user['ID'];
               } else {
                   header("WWW-Authenticate: Basic realm=\"" . PROJECT_TITLE . "\"");
                   header('HTTP/1.0 401 Unauthorized');
                   echo 'Incorrect username/password!';
                   exit;
               }
          }    
      }
   }
   global $terminal;
   if ($terminal) {
    $session->data['TERMINAL']=$terminal;
   }

   if ($this->action!='apps') {
    if (preg_match('/^app_\w+$/is', $this->action) || $this->action=='xray') {
     $out['APP_ACTION']=1;
    }

    if ($this->app_action) {
     $out['APP_ACTION']=1;
    }
   }

   if ($this->app_action=='panel') {
    $this->redirect(ROOTHTML.'admin.php');
   }


   $terminals = getAllTerminals(-1, 'TITLE');
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    if ($terminals[$i]['HOST']!='' && $_SERVER['REMOTE_ADDR']==$terminals[$i]['HOST'] && !$session->data['TERMINAL']) {
     $session->data['TERMINAL']=$terminals[$i]['NAME'];
    }
    if (mb_strtoupper($terminals[$i]['NAME'], 'UTF-8')==mb_strtoupper($session->data['TERMINAL'], 'UTF-8')) {
     $terminals[$i]['LATEST_ACTIVITY']=date('Y-m-d H:i:s');
     $terminals[$i]['IS_ONLINE']=1;
     SQLUpdate('terminals', $terminals[$i]);
     $out['TERMINAL_TITLE']=$terminals[$i]['TITLE'];
     $terminals[$i]['SELECTED']=1;
    }
   }

   $main_terminal=getTerminalsByName('MAIN')[0];
   if (!$main_terminal['ID']) {
    $main_terminal=array();
    $main_terminal['NAME']='MAIN';
    $main_terminal['TITLE']='MAIN';
    $main_terminal['HOST']=$_SERVER['SERVER_ADDR'];
    SQLInsert('terminals',$main_terminal);
   }

   if (!$out['TERMINAL_TITLE'] && $session->data['TERMINAL']) {
    $new_terminal=array();
    $new_terminal['TITLE']=$session->data['TERMINAL'];
    $new_terminal['HOST']=$_SERVER['REMOTE_ADDR'];
    $new_terminal['NAME']=$new_terminal['TITLE'];
    $new_terminal['LATEST_ACTIVITY']=date('Y-m-d H:i:s');
    $new_terminal['IS_ONLINE']=1;
    SQLInsert('terminals', $new_terminal);
    $out['TERMINAL_TITLE']=$new_terminal['TITLE'];
    $new_terminal['SELECTED']=1;
    $out['TERMINALS'][]=$new_terminal;
   }

   $out['TERMINALS']=$terminals;
   if ($total==1) {
    $out['HIDE_TERMINALS']=1;
    $session->data['TERMINAL']=$terminals[0]['NAME'];
   }

   $users=SQLSelect("SELECT * FROM users ORDER BY NAME");
   $total=count($users);
   for($i=0;$i<$total;$i++) {
    if ($users[$i]['USERNAME']==$session->data['SITE_USERNAME']) {
     $users[$i]['SELECTED']=1;
     $out['USER_TITLE']=$users[$i]['NAME'];
     $out['USER_AVATAR']=$users[$i]['AVATAR'];
    } elseif (!$session->data['SITE_USERNAME'] && $users[$i]['HOST'] && $users[$i]['HOST']==$_SERVER['REMOTE_ADDR']) {
     $session->data['SITE_USERNAME']=$users[$i]['USERNAME'];
     $session->data['SITE_USER_ID']=$users[$i]['ID'];     
     $out['USER_TITLE']=$users[$i]['NAME'];
     $out['USER_AVATAR']=$users[$i]['AVATAR'];
    }
    if ($users[$i]['IS_DEFAULT']==1) {
     $out['DEFAULT_USERNAME']=$users[$i]['USERNAME'];
     $out['DEFAULT_USER_ID']=$users[$i]['ID'];
    }
   }
   $out['USERS']=$users;
   if ($total==1) {
    $out['HIDE_USERS']=1;
    $session->data['SITE_USERNAME']=$users[0]['USERNAME'];
    $session->data['SITE_USER_ID']=$users[0]['ID'];
   }
   if (!$session->data['SITE_USERNAME'] && $out['DEFAULT_USERNAME']) {
    $session->data['SITE_USERNAME']=$out['DEFAULT_USERNAME'];
    $session->data['SITE_USER_ID']=$out['DEFAULT_USER_ID'];
    for($i=0;$i<$total;$i++) {
     if ($users[$i]['USERNAME']==$session->data['USERNAME']) {
      $users[$i]['SELECTED']=1;
      $out['USER_TITLE']=$users[$i]['NAME'];
      $out['USER_AVATAR']=$users[$i]['AVATAR'];
     }
    }
   }

   if ($out['USER_TITLE']) {
    Define('USER_TITLE', $out['USER_TITLE']);
    Define('USER_AVATAR', $out['USER_AVATAR']);
   } else {
    Define('USER_TITLE', '');
    Define('USER_AVATAR', '');
   }

   if ($out["DOC_NAME"]) {
    //$doc=SQLSelectOne("SELECT ID FROM cms_docs WHERE NAME LIKE '".DBSafe($out['DOC_NAME'])."'");
    if ($doc['ID']) {
     $this->doc=$doc['ID'];
    }
   }

   if ($session->data["AUTHORIZED"]) {
    $out['AUTHORIZED_ADMIN']=1;
   }

   if ($this->action=='' || $this->action=='pages') {
    $res=SQLSelect("SELECT * FROM layouts WHERE HIDDEN!=1 ORDER BY PRIORITY DESC, TITLE");
    if ($this->action!='admin') {
     $total=count($res);
     $res2=array();
     for($i=0;$i<$total;$i++) {
      if (checkAccess('layout', $res[$i]['ID'])) {
       $res2[]=$res[$i];
      }
     }
     $res=$res2;
     unset($res2);
    }
    $out['LAYOUTS']=$res;

    $total=count($out['LAYOUTS']);
    for($i=0;$i<$total;$i++) {
     $out['LAYOUTS'][$i]['NUM']=$i;
    }
    $out['TOTAL_LAYOUTS']=count($out['LAYOUTS']);
   } else {
    $out['TOTAL_LAYOUTS']=0;
   }

   if ($this->doc) $this->doc_id=$this->doc;
   $out["DOC_ID"]=$this->doc_id;

   if ($session->data['MY_MEMBER']) {
    $out['MY_MEMBER']=$session->data['MY_MEMBER'];
    $tmp=SQLSelectOne("SELECT ID FROM users WHERE ID='".(int)$out['MY_MEMBER']."' AND ACTIVE_CONTEXT_ID!=0 AND TIMESTAMPDIFF(SECOND, ACTIVE_CONTEXT_UPDATED, NOW())>600");
    if ($tmp['ID']) {
     SQLExec("UPDATE users SET ACTIVE_CONTEXT_ID=0, ACTIVE_CONTEXT_EXTERNAL=0 WHERE ID='".$tmp['ID']."'");
    }
   }

   $out['AJAX']=$this->ajax;
   $out['POPUP']=$this->popup;
   
   $days=array(LANG_WEEK_SUN,LANG_WEEK_MON,LANG_WEEK_TUE,LANG_WEEK_WED,LANG_WEEK_THU,LANG_WEEK_FRI,LANG_WEEK_SAT);
   
   $out['TODAY']=$days[date('w')].', '.date('d.m.Y');
   Define('TODAY', $out['TODAY']);
   $out['REQUEST_URI']=$_SERVER['REQUEST_URI'];

   global $from_scene;
   if ($from_scene) {
    $out['FROM_SCENE']=1;
   }


   global $ajt;
   if ($ajt=='') {
    $template_file=DIR_TEMPLATES.$this->name.".html";
   } else {
    $template_file=ROOT.'templates_ajax/'.$this->name."_".$ajt.".html";
   }

   if ($this->action=='menu') {
    $template_file=DIR_TEMPLATES."menu.html";
   }
   if ($this->action=='pages') {
    $template_file=DIR_TEMPLATES."pages.html";
   }
   if ($this->action=='scenes') {
    $template_file=DIR_TEMPLATES."scenes.html";
   }

   if (!$this->action && defined('SETTINGS_GENERAL_START_LAYOUT') && SETTINGS_GENERAL_START_LAYOUT!='') {
   
    if (SETTINGS_GENERAL_START_LAYOUT=='homepages') {
     $this->redirect(ROOTHTML.'pages.html');
    }
    if (SETTINGS_GENERAL_START_LAYOUT=='menu') {
     $this->redirect(ROOTHTML.'menu.html');
    }
    if (SETTINGS_GENERAL_START_LAYOUT=='apps') {
     $this->redirect(ROOTHTML.'apps.html');
    }
    if (SETTINGS_GENERAL_START_LAYOUT=='cp') {
     $this->redirect(ROOTHTML.'admin.php');
    }
   }


   if ($this->ajax && $this->action) {
    global $ajax;
    $ajax=1;
    if (file_exists(DIR_MODULES.$this->action)) {
     ignore_user_abort(1);
     include_once(DIR_MODULES.$this->action.'/'.$this->action.'.class.php');
     $obj="\$object$i";
     $code="";
     $code.="$obj=new ".$this->action.";\n";
     $code.=$obj."->owner=&\$this;\n";
     $code.=$obj."->getParams();\n";
     $code.=$obj."->ajax=1;\n";
     $code.=$obj."->run();\n";
     startMeasure("module_".$this->action);
     eval($code);
     endMeasure("module_".$this->action); 

    }
    return;
   } else {
    $this->data=$out;
    $p=new parser($template_file, $this->data, $this);
    return $p->result;
   }


  }

// --------------------------------------------------------------------

 }

?>
