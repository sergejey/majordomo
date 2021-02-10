<?php
/**
* Media Browser Application
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.4 (05.09.2011)
*/
//
//
class app_mediabrowser extends module {
/**
* holdingpage
*
* Module class constructor
*
* @access private
*/
function app_mediabrowser() {
  $this->name="app_mediabrowser";
  $this->title="<#LANG_APP_MEDIA_BROWSER#>";
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
 if (IsSet($this->mode)) {
  $data["mode"]=$this->mode;
 }
 if (IsSet($this->collection_id)) {
  $data["collection_id"]=$this->collection_id;
 }
 if (IsSet($this->folder)) {
  $data["folder"]=$this->folder;
 }


 if (IsSet($this->showplayer)) {
  $data["showplayer"]=$this->showplayer;
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
 /*
 $this->getConfig();
 if ($this->mode=='update') {
  global $path;
  $this->config['PATH']=$path;
  $this->saveConfig();
  $out['OK']=1;
 }
 $out['PATH']=htmlspecialchars($this->config['PATH']);
 */

 global $id;

 if ($this->view_mode=='clear_favorites') {
  SQLExec("DELETE FROM media_favorites");
  $this->redirect("?");
 }

 if ($this->view_mode=='clear_history') {
  SQLExec("DELETE FROM media_history");  
  $this->redirect("?");
 }

 if ($this->view_mode=='delete') {
  $rec=SQLSelectOne("SELECT * FROM collections WHERE ID='".(int)$id."'");
  SQLExec("DELETE FROM collections WHERE ID='".$rec['ID']."'");
  $this->redirect("?");
 }
 if ($this->view_mode=='edit') {
  $rec=SQLSelectOne("SELECT * FROM collections WHERE ID='".(int)$id."'");
  if ($this->mode=='update_collection') {
   global $title;
   global $path;
   $path = ((substr($path, -1) == DIRECTORY_SEPARATOR)?$path:$path.DIRECTORY_SEPARATOR);
   $rec['TITLE']=$title;
   $rec['PATH']=$path;
   if ($rec['TITLE'] && $rec['PATH']) {
    if ($rec['ID']) {
     SQLUpdate('collections', $rec);
    } else {
     $rec['ID']=SQLInsert('collections', $rec);
    }
    $this->redirect("?");
   }
  }
  outHash($rec, $out);
 }

 $out['COLLECTIONS']=SQLSelect("SELECT * FROM collections ORDER BY TITLE");

}


/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {

    if(DIRECTORY_SEPARATOR=='/')
        $run_linux=1;
    else
        $run_linux=0;

   $play=gr('play');
   $play = urlsafe_b64decode($play);
   if ($play!='') {
    require(DIR_MODULES.$this->name.'/stream_files.php');
   }

   $terminals = getTerminalsCanPlay(-1, 'TITLE');
   $total=count($terminals);
   //for($i=0;$i<$total;$i++) {
    //if ($terminals[$i]['NAME']==$session->data['PLAY_TERMINAL']) {
    // $terminals[$i]['SELECTED']=1;
    // $out['TERMINAL_TITLE']=$terminals[$i]['TITLE'];
    //}
   //}
   $out['TERMINALS']=$terminals;
   $out['TERMINALS_TOTAL']=count($terminals);


   if ($this->mode=='play') {
    //echo $this->owner->popup;
    global $showplayer;

    $this->showplayer=1;

    if ($this->showplayer) {
     $showplayer=1;
    }
    if ($showplayer) {
     $this->showplayer=1;
     $out['SHOWPLAYER']=1;
    }
    global $terminal_id;
    $out['TERMINAL_ID']=$terminal_id;
   }

 global $collection_id;

 if ($this->collection_id) {
  $collection_id=$this->collection_id;
 }
 $this->collection_id=$collection_id;

 $res=SQLSelect("SELECT * FROM collections ORDER BY TITLE");
    if ($this->action!='admin') {
     $total=count($res);
     $res2=array();
     for($i=0;$i<$total;$i++) {
      if (checkAccess('media', $res[$i]['ID'])) {
       $res2[]=$res[$i];
      }
     }
     $res=$res2;
     unset($res2);
    }
 $out['COLLECTIONS']=$res;


 if (count($out['COLLECTIONS'])==1) {
  $collection_id=$out['COLLECTIONS'][0]['ID'];
  $this->collection_id=$collection_id;
  $out['COLLECTIONS_TOTAL']=1;
 }





 if ($collection_id) {
  $collection=SQLSelectOne("SELECT * FROM collections WHERE ID='".(int)$collection_id."'");
  $path=($collection['PATH']);//addslashes
  $out['COLLECTION_ID']=$collection['ID']; 
 } else {
  return;
 }


 //$this->getConfig();
 //$path=$this->config['PATH'];

// echo $path;
//
  global $folder;

  if ($this->folder) {
   $folder=base64_decode($this->folder);
  } else {
   $this->folder=base64_encode($folder);
  }
  

  $favorites=SQLSelect("SELECT * FROM media_favorites WHERE 1 ORDER BY ID DESC");
  if ($favorites) {
   if(!$run_linux){
    $total=count($favorites);
    for($i=0;$i<$total;$i++) {
     $favorites[$i]['PATH']=urlencode(($favorites[$i]['PATH']));
    }
   }
   $out['FAVORITES']=$favorites;
  }

  $media_history=SQLSelect("SELECT * FROM media_history WHERE 1 ORDER BY PLAYED DESC");
  if ($media_history) {
   if(!$run_linux){
    $total=count($media_history);
    for($i=0;$i<$total;$i++) {
     $media_history[$i]['PATH']=urlencode(($media_history[$i]['PATH']));
    }
   }
   $out['MEDIA_HISTORY']=$media_history;
  }


  $folder=str_replace('././', './', $folder);
  $path=str_replace('././', './', $path);



  $act_dir=$path.$folder;


      $out['MEDIA_PATH']=$path;
      $out['CURRENT_DIR']='./'.$folder;




  $out['CURRENT_DIR']=str_replace('././', './', $out['CURRENT_DIR']);


  $out['CURRENT_DIR_TITLE']=$folder;

  $tmp=explode('/', $out['CURRENT_DIR']);
  $total=count($tmp);
  if ($total>0) {
   $spath='.';
   for($i=0;$i<$total;$i++) {
    $tmp_rec=array();
    $tmp_rec['TITLE']=$tmp[$i];
    $spath.='/'.$tmp_rec['TITLE'];
    $spath=str_replace('././', './', $spath);
    $tmp_rec['PATH']=urlencode($spath.'/');


    if ($tmp_rec['TITLE']=='.') {
     $tmp_rec['TITLE']='Home';
    }
    $out['HISTORY'][]=$tmp_rec;
    //echo $tmp_rec['PATH']."<br>";
   }
   $out['CURRENT_DIR_TITLE']=($out['HISTORY'][$total-3]['TITLE'].'/'.$out['HISTORY'][$total-2]['TITLE']);
  }


  $out['CURRENT_DIR_TITLE_HTML']=urlencode($out['CURRENT_DIR_TITLE']);
  $out['CURRENT_DIR_HTML']=urlencode('./'.($folder));

  $tmp=SQLSelectOne("SELECT ID FROM media_favorites WHERE LIST_ID='".(int)$list_id."' AND COLLECTION_ID='".$collection['ID']."' AND PATH LIKE '".DBSafe($out['CURRENT_DIR'])."'");
  if ($tmp['ID']) {
   $out['FAVORITE']=1;
  }

  global $file;
  if ($file) {

      if($run_linux){
          $out['FILE']=$file;
          $out['BASEFILE']=basename($file);
          //$file=str_replace('/', '\\', $file);
          $out['FULLFILE']=addslashes($path).$file;
      } else {
          $out['FILE']=($file);
          $out['BASEFILE']=(basename($file));
          $file=str_replace('/', '\\', $file);
          $out['FULLFILE']=(($path).$file);
      }

   //$out['FULLFILE_S']=str_replace('\\\\', '\\', $out['FULLFILE']);
   $out['FULLFILE_S']=$out['FULLFILE'];
   if (is_dir(($path).$file)) {
	   //$file_ext = 'm3u';
	   $file_ext = 'html';
       $out['FULLFILE_URL']='http://'.$_SERVER['HTTP_HOST']."/module/app_mediabrowser.$file_ext?play=".urlsafe_b64encode($out['FULLFILE'])."&type=m3u";
   } else {
	   if(!$file_ext = pathinfo($out['FULLFILE'], PATHINFO_EXTENSION)) {
		   $file_ext = 'html';
	   }
//        $out['FULLFILE_URL']='http://'.$_SERVER['HTTP_HOST']."/module/app_mediabrowser.$file_ext?play=".urlsafe_b64encode($out['FULLFILE']);
	   $out['FULLFILE_URL']= "http://" . $_SERVER['HTTP_HOST']. "/module/app_mediabrowser.html?play=" . urlsafe_b64encode($out['FULLFILE']);
   }
   //dprint($out['FULLFILE_URL']);

   if ($this->mode=='play') {
    //FULLFILE_S
    $rec=array();
    $rec['TITLE']=$out['CURRENT_DIR_TITLE'];
    $rec['PATH']=($folder);
    $rec['LIST_ID']=(int)$list_id;
    $rec['COLLECTION_ID']=$collection_id;
    $rec['PLAYED']=date('Y-m-d H:i:s');
    SQLExec("DELETE FROM media_history WHERE PATH LIKE '".DBSafe($rec['PATH'])."'");
    SQLInsert('media_history', $rec);

    $last10=SQLSelect("SELECT ID FROM media_history ORDER BY PLAYED DESC LIMIT 10");
    $total=count($last10);
    $ids=array();
    for($i=0;$i<$total;$i++) {
     $ids[]=$last10[$i]['ID'];
    }
    SQLExec("DELETE FROM media_history WHERE ID NOT IN (".implode(',', $ids).")");

    if ($_GET['full_url']) {
     echo $out['FULLFILE_URL'];
     exit;
    }

   }

  }

  if (preg_match('/foto/is', $act_dir) || preg_match('/photo/is', $act_dir)) {
   $out['LIST_MODE']='foto';
  }

  $descriptions=$this->getDescriptions($act_dir);

  global $media_ajax;
  if ($media_ajax) {
   global $op;
   global $list_id;
   global $title;
   global $dir;

   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');

   $tmp_path=$dir;

   if ($op=='favorite_add') {
    $rec=array();
    $rec['TITLE']=$title;
    $rec['PATH']=$tmp_path;
    $rec['LIST_ID']=(int)$list_id;
    $rec['COLLECTION_ID']=$collection_id;
    SQLInsert('media_favorites', $rec);
    echo "OK";
   }
   if ($op=='favorite_remove') {
    SQLExec("DELETE FROM media_favorites WHERE LIST_ID='".(int)$list_id."' AND COLLECTION_ID='".$collection['ID']."' AND PATH LIKE '".DBSafe($tmp_path)."'");
    echo "OK";
   }
   exit;
  }


 /*
   function sort_files($a, $b) {
    return strcmp(strtoupper($a["TITLE"]), strtoupper($b["TITLE"])); 
   }
*/


  $dirs=array();
  //$act_dir='\\\\home\\media\\';

  if (substr($act_dir, -1)!='/' && substr($act_dir, -1)!='\\') {
   $act_dir.='/';
  }

  if (!is_dir($act_dir)) {
   return;
  }

  $d=openDir($act_dir);
  //exit;

  if ($d) {
  while ($file=readDir($d)) {
   if (($file==".") || ($file=="..")) {
    continue;
   }
   if (Is_Dir($act_dir.$file)) {
    $rec=array();
    $rec['TITLE']=$file;
    $rec['TITLE_SHORT']=$rec['TITLE'];
    if (strlen($rec['TITLE_SHORT'])>50) {
     $rec['TITLE_SHORT']=substr($rec['TITLE_SHORT'], 0, 50).'...';
    }

        $rec['TITLE']=$rec['TITLE'];
        $rec['TITLE_SHORT']=$rec['TITLE_SHORT'];



    if (IsSet($descriptions[$file])) {
     $rec['DESCR']=$descriptions[$file];
    }
    $rec['PATH']=urlencode(($folder.$file)).'/';
    $rec['REAL_PATH']=$dir.$file;
    $rec['ID']=md5($rec['REAL_PATH']);
    $dirs[]=$rec;
   }
  }

  closeDir($d);
  }



  //$dirs=mysort_array($dirs, "TITLE");
  usort($dirs, function($a,$b) {
   return strcmp(strtoupper($a["TITLE"]), strtoupper($b["TITLE"]));
  });

  //print_r($dirs);

  if (count($dirs)>0) $out['DIRS']=$dirs;

  @$d=openDir($act_dir);
  if ($d) {

  $cover=$this->getCover($act_dir);
  if ($cover) {
   $out['COVER']=$cover;
   $out['COVER_PATH']=urlencode(str_replace('\\\\', '\\', $act_dir).$cover);
   $out['COVER_PATH']=urlencode(($act_dir).$cover);
  }


  $files=array();
  while ($file=readDir($d)) {
   if (($file==".") || ($file=="..") || ($file=="Descript.ion")) {
    continue;
   }
   if (Is_File($act_dir.$file)) {
    $rec=array();
    $rec['TITLE']=$file;
    if (IsSet($descriptions[$file])) {
     $rec['DESCR']=$descriptions[$file];
    }
    if (strlen($rec['TITLE'])>50) {
     $rec['TITLE_SHORT']=substr($rec['TITLE'], 0, 50)."...";
    } else {
     $rec['TITLE_SHORT']=$rec['TITLE'];
    }

        $rec['TITLE']=$rec['TITLE'];
        $rec['TITLE_SHORT']=$rec['TITLE_SHORT'];

    $rec['REAL_PATH']=($folder.$file);
    $rec['PATH']=urlencode($folder.$file);
    //$rec['FULL_PATH']=urlencode(str_replace('\\\\', '\\', $act_dir).$file);
    $rec['FULL_PATH']=urlencode($act_dir.$file);
    $size=@filesize($act_dir.$file);
    $total_size+=$size;
    if ($size>1024) {
     if ($size>1024*1024) {
      $size=(((int)(($size/1024/1024)*10))/10)." Mb";
     } else {
      $size=(int)($size/1024)." Kb";
     }
    } else {
     $size.=" b";
    }
    $rec['SIZE']=$size;
    $rec['ID']=md5($rec['PATH']);
    $files[]=$rec;
   }
  }
  closeDir($d);
  }

  //$files=mysort_array($files, "TITLE");
  usort($files, function($a,$b) {
   return strcmp(strtoupper($a["TITLE"]), strtoupper($b["TITLE"]));
  });

  if (count($files)>0) {
   $total=count($files);
   $out['TOTAL_FILES']=$total;
   for($i=0;$i<$total;$i++) {
    if (preg_match('/\.jpg$/is', $files[$i]['PATH']) || preg_match('/\.jpeg$/is', $files[$i]['PATH']) || preg_match('/\.png$/is', $files[$i]['PATH'])) {
     $files[$i]['IS_FOTO']=1;
     $total_photos++;
    }
    if (($i+1)%4==0) {
     $files[$i]['NEWROW']=1;
    }
   }
   if ($total_photos==$total) {
    $out['LIST_MODE']='foto';
   }
   $out['FILES']=$files;
  }



  $out['TOTAL_DIRS']=count($dirs);

    if ($total_size>1024) {
     if ($total_size>1024*1024) {
      $total_size=(((int)(($total_size/1024/1024)*10))/10)." Mb";
     } else {
      $total_size=(int)($total_size/1024)." Kb";
     }
    } else {
     $total_size.=" b";
    }
    $out['TOTAL_SIZE']=$total_size;


}

   function getDescriptions($dir)
   {
      $descr = array();

      if (file_exists($dir . "Descript.ion"))
      {
         $data    = LoadFile($dir . "Descript.ion");
         $strings = explode("\n", $data);
         $strCnt  = count($strings);

         for ($i = 0; $i < $strCnt; $i++)
         {
            $fields      = explode("\t", $strings[$i]);
            $filename    = str_replace("\"", "", $fields[0]);
            $description = $fields[1];

            $descr[$filename] = $description;
         }
      }

      return $descr;
   }

/**
 * Set directory description
 * @param mixed $dir   Directory
 * @param mixed $file  File
 * @param mixed $descr Description
 */
function setDescription($dir, $file, $descr)
{
   $descriptions = self::getDescriptions($dir);
   
   $descriptions[$file] = $descr;
   
   $data = array();
  
   foreach($descriptions as $k => $v)
   {
      $data[] = "\"$k\"\t$v";
   }
   
   SaveFile($dir . "Descript.ion", join("\n", $data));
}


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function getCover($path) {
   $common_covers=array('cover.jpg');
   foreach($common_covers as $file) {
    if (file_exists($path.$file)) {
     return $file;
    }
   }

   //search for images
  @$d=openDir($path);
  if ($d) {
   $files=array();
   $biggest_size=0;
   $biggest_file='';
   while ($file=readDir($d)) {
   if (preg_match('/\.jpg$/is', $file) 
      || preg_match('/\.jpeg$/is', $file) 
      || preg_match('/\.gif$/is', $file)
      || preg_match('/\.png$/is', $file)
   ) {
    $file_size=@filesize($path.$file);
    if ($file_size>$biggest_size) {
     $biggest_size=$file_size;
     $biggest_file=$file;
    }
    //$files=array('FILENAME'=>$file);
   }
   }
   closeDir($d);
   if ($biggest_file) {
    return $biggest_file;
   }
  }



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

 function dbInstall($data) {
/*
terminals - Terminals
*/
  $data = <<<EOD
  collections: ID int(10) unsigned NOT NULL auto_increment
  collections: PATH varchar(255) NOT NULL DEFAULT ''
  collections: TITLE varchar(255) NOT NULL DEFAULT ''

  media_favorites: ID int(10) unsigned NOT NULL auto_increment
  media_favorites: PATH varchar(255) NOT NULL DEFAULT ''
  media_favorites: TITLE varchar(255) NOT NULL DEFAULT ''
  media_favorites: LIST_ID int(10) unsigned NOT NULL DEFAULT '0'
  media_favorites: COLLECTION_ID int(10) unsigned NOT NULL DEFAULT '0'

  media_history: ID int(10) unsigned NOT NULL auto_increment
  media_history: PATH varchar(255) NOT NULL DEFAULT ''
  media_history: TITLE varchar(255) NOT NULL DEFAULT ''
  media_history: LIST_ID int(10) unsigned NOT NULL DEFAULT '0'
  media_history: COLLECTION_ID int(10) unsigned NOT NULL DEFAULT '0'
  media_history: PLAYED datetime


EOD;
  parent::dbInstall($data);
 }


// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDIzLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
