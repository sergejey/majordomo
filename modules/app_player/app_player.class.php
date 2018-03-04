<?php
/**
* Media Player Application
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 11:02:35 [Feb 23, 2009])
*/
//
//
class app_player extends module {
/**
* player
*
* Module class constructor
*
* @access private
*/
function app_player() {
  $this->name="app_player";
  $this->title="<#LANG_APP_PLAYER#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
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
 $this->getConfig();
 if ($this->mode=='update') {
  global $enabled;
  $this->config['ENABLED']=(int)$enabled;
  $this->saveConfig();
  $out['OK']=1;
 }
 $this->usual($out);
 $out['MODE']=$this->mode;
 $out['ENABLED']=(int)($this->config['ENABLED']);
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 global $play;
 global $rnd;
 global $rnd;
 global $session;
 global $play_terminal;
 global $terminal_id;
 global $volume;


 if ($this->play) {
  $play=$this->play;
 }

 if ($this->terminal_id) {
  $terminal_id=$this->terminal_id;
 }

 if ($terminal_id) {
  $terminal=SQLSelectOne("SELECT * FROM terminals WHERE ID='".(int)$terminal_id."'");
  $session->data['PLAY_TERMINAL']=$terminal['NAME'];
 }



 if ($session->data['PLAY_TERMINAL']=='') {
  $session->data['PLAY_TERMINAL']=$session->data['TERMINAL'];
 }

 if ($play_terminal!='') {
  $session->data['PLAY_TERMINAL']=$play_terminal;
 }

 if ($session->data['PLAY_TERMINAL']!='') {
  $terminal=SQLSelectOne("SELECT * FROM terminals WHERE NAME='".DBSafe($session->data['PLAY_TERMINAL'])."'");
 }

 if (!$terminal['HOST']) {
  $terminal['HOST']='localhost';
 }

 if (!$terminal['CANPLAY']) {
  $terminal=SQLSelectOne("SELECT * FROM terminals WHERE NAME='HOME' OR NAME='MAIN'");
 }

 if (!$terminal['CANPLAY']) {
  $terminal=SQLSelectOne("SELECT * FROM terminals WHERE CANPLAY=1 ORDER BY IS_ONLINE DESC LIMIT 1");
 }

 if (!$play && $session->data['LAST_PLAY']) {
  $play=$session->data['LAST_PLAY'];
  $out['LAST_PLAY']=1;
 } elseif ($play) {
  $session->data['LAST_PLAY']=$play;
 }

 if ($play!='') {
  $out['PLAY']=$play;
 }

 if ($rnd!='') {
  $out['RND']=$rnd;
 }

 $current_level=getGlobal('ThisComputer.volumeLevel');
 for($i=0;$i<=100;$i+=5) {
  $rec=array('VALUE'=>$i);
  if ($i==$current_level) {
   $rec['SELECTED']=1;
  }
  $out['VOLUMES'][]=$rec;
 }




 global $ajax;
 if ($this->ajax) {
  $ajax=1;
 }


 if ($ajax!='') {
  global $command;
  if ($command!='') {
   if (!$this->intCall) {
    echo $command.' ';
   }
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

   if ($terminal['PLAYER_USERNAME'] || $terminal['PLAYER_PASSWORD']) {
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
    curl_setopt($ch, CURLOPT_USERPWD, $terminal['PLAYER_USERNAME'].':'.$terminal['PLAYER_PASSWORD']);
   }

   if (!$terminal['PLAYER_PORT'] && $terminal['PLAYER_TYPE']=='foobar') {
    $terminal['PLAYER_PORT']='8888';
   } elseif (!$terminal['PLAYER_PORT'] && $terminal['PLAYER_TYPE']=='xbmc') {
    $terminal['PLAYER_PORT']='8080';
   } elseif (!$terminal['PLAYER_PORT']) {
    $terminal['PLAYER_PORT']='80';
   }

   if ($terminal['NAME']=='MAIN' && $command=='volume') {
    setGlobal('ThisComputer.volumeLevel', $volume);
   }

    if ($terminal['PLAYER_TYPE']=='vlc' || $terminal['PLAYER_TYPE']=='') {

      $terminal['PLAYER_PORT']='80';

      if ($command=='refresh') {
       $out['PLAY']=preg_replace('/\\\\$/is', '', $out['PLAY']);
       $out['PLAY']=preg_replace('/\/$/is', '', $out['PLAY']);
       if (preg_match('/^http/', $out['PLAY'])) {
        $path=urlencode($out['PLAY']);
       } else {
        $path=urlencode(''.str_replace('/', "\\", ($out['PLAY'])));
       }
       curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_play&param=".$path);
       $res=curl_exec($ch);
      }

      if ($command=='fullscreen') {
       curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_fullscreen");
       $res=curl_exec($ch);
      }


      if ($command=='pause') {
       curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_pause");
       $res=curl_exec($ch);
      }

      if ($command=='next') {
       curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_next");
       $res=curl_exec($ch);
      }

      if ($command=='prev') {
       curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_prev");
       $res=curl_exec($ch);
      }

      if ($command=='close') {
       curl_setopt($ch, CURLOPT_URL, "http://".$terminal['HOST'].":".$terminal['PLAYER_PORT']."/rc/?command=vlc_close");
       $res=curl_exec($ch);
      }

     if ($command=='volume') {
       setGlobal('ThisComputer.volumeLevel', $volume);
       callMethod('ThisComputer.VolumeLevelChanged', array('VALUE'=>$volume, 'HOST'=>$terminal['HOST']));
      }

   } elseif ($terminal['PLAYER_TYPE']=='xbmc') {
    include(DIR_MODULES.'app_player/xbmc.php');
    } elseif ($terminal['PLAYER_TYPE']=='ghn') {
     include(DIR_MODULES.'app_player/ghn.php');
   } elseif ($terminal['PLAYER_TYPE']=='foobar') {
    include(DIR_MODULES.'app_player/foobar.php');
   } elseif ($terminal['PLAYER_TYPE']=='vlcweb') {
    include(DIR_MODULES.'app_player/vlcweb.php');
   } elseif ($terminal['PLAYER_TYPE']=='mpd') {
    include(DIR_MODULES.'app_player/mpd.php');
    } elseif ($terminal['PLAYER_TYPE']=='chromecast') {
     include(DIR_MODULES.'app_player/chromecast.php');
   } elseif ($terminal['MAJORDROID_API'] || $terminal['PLAYER_TYPE']=='majordroid') {
   include(DIR_MODULES.'app_player/majordroid.php');
   }

   // close cURL resource, and free up system resources
   curl_close($ch);    

  }


  if (!$this->intCall) {

   if ($session->data['PLAY_TERMINAL']!='') {
    echo " on ".$session->data['PLAY_TERMINAL'].' ';
   }

   echo "OK";
   if ($res) {
    echo " (".$res.")";
   }
   $session->save();
   exit;
  }
 }

   $terminals=SQLSelect("SELECT * FROM terminals WHERE CANPLAY=1 ORDER BY TITLE");
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    if ($terminals[$i]['NAME']==$session->data['PLAY_TERMINAL']) {
     $terminals[$i]['SELECTED']=1;
     $out['TERMINAL_TITLE']=$terminals[$i]['TITLE'];
    }
   }
   $out['TERMINALS_TOTAL']=count($terminals);
   if ($out['TERMINALS_TOTAL']==1 || !$session->data['PLAY_TERMINAL']) {
    $terminals[0]['SELECTED']=1;
   }

   $out['TERMINALS']=$terminals;



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
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDIzLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>