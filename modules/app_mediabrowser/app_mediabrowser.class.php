<?
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
function saveParams() {
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
 if (IsSet($this->mode)) {
  $p["mode"]=$this->mode;
 }
 if (IsSet($this->collection_id)) {
  $p["collection_id"]=$this->collection_id;
 }
 if (IsSet($this->folder)) {
  $p["folder"]=$this->folder;
 }


 if (IsSet($this->showplayer)) {
  $p["showplayer"]=$this->showplayer;
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


   $terminals=SQLSelect("SELECT * FROM terminals WHERE CANPLAY=1 ORDER BY TITLE");
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    //if ($terminals[$i]['NAME']==$session->data['PLAY_TERMINAL']) {
    // $terminals[$i]['SELECTED']=1;
    // $out['TERMINAL_TITLE']=$terminals[$i]['TITLE'];
    //}
   }
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

 $out['COLLECTIONS']=SQLSelect("SELECT * FROM collections ORDER BY TITLE");

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
   $total=count($favorites);
   for($i=0;$i<$total;$i++) {
    $favorites[$i]['PATH']=urlencode(utf2win($favorites[$i]['PATH']));
   }
   $out['FAVORITES']=$favorites;
  }

  $folder=str_replace('././', './', $folder);
  $path=str_replace('././', './', $path);


  $act_dir=$path.$folder;



  $out['MEDIA_PATH']=win2utf($path);
  $out['CURRENT_DIR']=win2utf('./'.$folder);
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
    $tmp_rec['PATH']=urlencode(utf2win($spath).'/');
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
   $out['FILE']=win2utf($file);
   $out['BASEFILE']=win2utf(basename($file));
   $file=str_replace('/', '\\\\', $file);
   $out['FULLFILE']=win2utf(addslashes($path).$file);
   $out['FULLFILE_S']=str_replace('\\\\', '\\', $out['FULLFILE']);
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


   function sort_files($a, $b) {
    return strcmp(strtoupper($a["TITLE"]), strtoupper($b["TITLE"])); 
   }

  $dirs=array();
  //$act_dir='\\\\home\\media\\';
  //echo $act_dir;
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

    $rec['TITLE']=win2utf($rec['TITLE']);
    $rec['TITLE_SHORT']=win2utf($rec['TITLE_SHORT']);

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
  usort($dirs, 'sort_files');

  //print_r($dirs);

  if (count($dirs)>0) $out['DIRS']=$dirs;

  @$d=openDir($act_dir);
  if ($d) {

  $cover=$this->getCover($act_dir);
  if ($cover) {
   $out['COVER']=$cover;
   $out['COVER_PATH']=urlencode(str_replace('\\\\', '\\', $act_dir).$cover);
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
    $rec['TITLE']=win2utf($rec['TITLE']);
    $rec['TITLE_SHORT']=win2utf($rec['TITLE_SHORT']);
    $rec['REAL_PATH']=($folder.$file);
    $rec['PATH']=urlencode($folder.$file);
    $rec['FULL_PATH']=urlencode(str_replace('\\\\', '\\', $act_dir).$file);
    $size=filesize($act_dir.$file);
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
  usort($files, 'sort_files');

  if (count($files)>0) {
   $total=count($files);
   $out['TOTAL_FILES']=$total;
   for($i=0;$i<$total;$i++) {
    if (preg_match('/\.jpg$/is', $files[$i]['PATH'])) {
     $files[$i]['IS_FOTO']=1;
    }
    if (($i+1)%4==0) {
     $files[$i]['NEWROW']=1;
    }
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

 function getDescriptions($dir) {
  $descr=array();
  if (file_exists($dir."Descript.ion")) {
   $data=LoadFile($dir."Descript.ion");
   $strings=explode("\n", $data);
   for($i=0;$i<count($strings);$i++) {
    $fields=explode("\t", $strings[$i]);
    $filename=$fields[0];
    $filename=str_replace("\"", "", $filename);
    $description=$fields[1];
    $descr[$filename]=$description;
   }
  }
  return $descr;
 }

 function setDescription($dir, $file, $descr) {
  $descriptions=getDescriptions($dir);
  $descriptions[$file]=$descr;
  $data=array();
  foreach($descriptions as $k=>$v) {
   $data[]="\"$k\"\t$v";
  }
  SaveFile($dir."Descript.ion", join("\n", $data));
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
    $file_size=filesize($path.$file);
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
 function install() {
  parent::install();
 }

 function dbInstall() {
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