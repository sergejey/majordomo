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
  function application() {
   global $session;
   global $db;
   $this->name="application";
   
 }

// --------------------------------------------------------------------

function saveParams() {
 $p=array();
 $p["action"]=$this->action;
 $p['doc_name']=$this->doc_name;
 if ($this->ajax) {
  $p['ajax']=$this->ajax;
 }
 if ($this->popup) {
  $p['popup']=$this->popup;
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

   if ($this->action=='getlatestnote') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');

    $msg=SQLSelectOne("SELECT * FROM shouts WHERE MEMBER_ID=0 ORDER BY ADDED DESC LIMIT 1");
    echo $msg['MESSAGE'];

    global $db;
    $db->Disconnect();
    exit;
   }

   if ($this->action=='getlatestmp3') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');

    if ($dir = @opendir(ROOT."cached")) { 
       while (($file = readdir($dir)) !== false) { 
       if (preg_match('/\.mp3$/', $file)) {
        $mtime=filemtime(ROOT."cached/".$file);
        if ((time()-$mtime)>60*60*24 && $mtime>0) {
         //old file, delete?
         unlink(ROOT."cached/".$file);
        } else {
         $files[]=array('FILENAME'=>$file, 'MTIME'=>filemtime(ROOT."cached/".$file));
        }
       }

       if (preg_match('/\.wav$/', $file)) {
        $mtime=filemtime(ROOT."cached/".$file);
        if ((time()-$mtime)>60*60*24 && $mtime>0) {
         //old file, delete?
         unlink(ROOT."cached/".$file);
        }
       }

      }
     closedir($dir); 
    } 

    if (is_array($files)) {
     function sortFiles($a, $b) {
         if ($a['MTIME'] == $b['MTIME']) return 0; 
         return ($a['MTIME'] > $b['MTIME']) ? -1 : 1; 
     }
     usort($files, 'sortFiles');
     echo '/cached/'.$files[0]['FILENAME'];
    }

    global $db;
    $db->Disconnect();
    exit;
   }

   if (!defined('SETTINGS_SITE_LANGUAGE') || !defined('SETTINGS_SITE_TIMEZONE') || !defined('SETTINGS_TTS_GOOGLE')) {
    $this->action='first_start';
   }

   if ($this->action=='first_start') {
    include(DIR_MODULES.'first_start.php');
   }

   $out["ACTION"]=$this->action;
   $out["TODAY"]=date('l, F d, Y');
   $out["DOC_NAME"]=$this->doc_name;

   global $username;
   
   if ($username) {
    $session->data['USERNAME']=$username;
   }
   global $terminal;
   if ($terminal) {
    $session->data['TERMINAL']=$terminal;
   }

   if (preg_match('/^app_\w+$/is', $this->action) || $this->action=='xray') {
    $out['APP_ACTION']=1;
   }


   $terminals=SQLSelect("SELECT * FROM terminals ORDER BY TITLE");
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    if ($terminals[$i]['NAME']==$session->data['TERMINAL']) {
     $terminals[$i]['SELECTED']=1;
     $out['TERMINAL_TITLE']=$terminals[$i]['TITLE'];
    }
   }
   $out['TERMINALS']=$terminals;
   if ($total==1) {
    $out['HIDE_TERMINALS']=1;
    $session->data['TERMINAL']=$terminals[0]['NAME'];
   }

   $users=SQLSelect("SELECT * FROM users ORDER BY NAME");
   $total=count($users);
   for($i=0;$i<$total;$i++) {
    if ($users[$i]['USERNAME']==$session->data['USERNAME']) {
     $users[$i]['SELECTED']=1;
     $out['USER_TITLE']=$users[$i]['NAME'];
    }
   }
   $out['USERS']=$users;
   if ($total==1) {
    $out['HIDE_USERS']=1;
    $session->data['USERNAME']=$users[0]['USERNAME'];
   }


   if ($out["DOC_NAME"]) {
    $doc=SQLSelectOne("SELECT ID FROM cms_docs WHERE NAME LIKE '".DBSafe($out['DOC_NAME'])."'");
    if ($doc['ID']) {
     $this->doc=$doc['ID'];
    }
   }

   if ($session->data["AUTHORIZED"]) {
    $out['AUTHORIZED_ADMIN']=1;
   }

   if ($this->action=='') {
    $out['LAYOUTS']=SQLSelect("SELECT * FROM layouts ORDER BY PRIORITY DESC, TITLE");
    $total=count($out['LAYOUTS']);
    for($i=0;$i<$total;$i++) {
     $out['LAYOUTS'][$i]['NUM']=$i;
    }
    $out['TOTAL_LAYOUTS']=count($out['LAYOUTS']);
   }

   if ($this->doc) $this->doc_id=$this->doc;
   $out["DOC_ID"]=$this->doc_id;

   if ($session->data['MY_MEMBER']) {
    $out['MY_MEMBER']=$session->data['MY_MEMBER'];
   }

   $out['AJAX']=$this->ajax;
   $out['POPUP']=$this->popup;
   
   $days=array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота');
   
   $out['TODAY']=$days[date('w')].', '.date('d.m.Y');
   Define(TODAY, $out['TODAY']);

   global $ajt;
   if ($ajt=='') {
    $template_file=DIR_TEMPLATES.$this->name.".html";
   } else {
    $template_file=ROOT.'templates_ajax/'.$this->name."_".$ajt.".html";
   }

   if ($this->action=='menu') {
    $template_file=DIR_TEMPLATES."menu.html";
   }

   $this->data=$out;
   $p=new parser($template_file, $this->data, $this);
   return $p->result;

  }

// --------------------------------------------------------------------

 }

?>