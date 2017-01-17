<?php
/**
* Design Skins 
*
* Skins
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 23:11:21 [Nov 12, 2006])
*/
//
//
class skins extends module {
/**
* skins
*
* Module class constructor
*
* @access private
*/
 function skins() {
  $this->name="skins";
  $this->title="<#LANG_MODULE_SKINS#>";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
 function saveParams($data = 0) {
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

 global $install;
 global $delete;

$skins=array();

 if ($install) {
  $this->install_skin($install);
  $out['INSTALLED']=1;
 }

 if ($this->mode=='upload') {
  global $file;
  global $file_name;

  if (preg_match('/\.tgz$/is', $file_name) || preg_match('/\.tar.gz$/is', $file_name)) {
   $basename=strtolower(preg_replace('/\..+$/', '', $file_name));
   $out['BASENAME']=$basename;

   if (!is_dir(ROOT.'skins/'.$basename)) {

      require 'Tar.php';
      $tar_object = new Archive_Tar($file);
      if (mkdir(ROOT.'skins/'.$basename)) {
       if ($tar_object->extract(ROOT.'skins/'.$basename)) {
        $out['OK_EXT']=1;
       } else {
        $out['ERR_FORMAT']=1;
       }
      } else {
       $out['ERR_MKDIR']=1;
      }
   } else {
    $out['ERR_EXISTS']=1;
   }
  } else {
   $out['ERR_FORMAT']=1;
  }

 }

if ($delete) {
  $this->delete_skin($delete);
 }

if ($handle = opendir(ROOT.'skins')) { 

   /* This is the correct way to loop over the directory. */
while (false !== ($file = readdir($handle))) { 
       if (is_dir(ROOT.'skins/'.$file) && $file!='.' && $file!='..') {
        $skin=array();
        $skin['TITLE']=$file;
        $skin['TITLE_URL']=urlencode($skin['TITLE']);
        if (file_exists(ROOT.'skins/'.$file.'/preview.jpg')) {
         $skin['PREVIEW']=ROOTHTML.'skins/'.$file.'/preview.jpg';
        }
        $skins[]=$skin;
       } 
   } 

   closedir($handle); 
}

 $total=count($skins);
 if ($total) {
  $out['SKINS']=$skins;
 }

}

/**
* install_skin
*
* Description
*
* @access public
*/
 function install_skin($skin) {
 if (!is_dir(ROOT.'skins/'.$skin)) return 0;
 $patterns=array(
  '\.css$',
  '\.jpg$',
  '\.png$',
  '\.gif$',
  '\.html$',
  '\.txt$',
 );
 $this->copyTree(ROOT.'skins/'.$skin, ROOT, 1, $patterns);
}

/**
* delete_skin
*
* Description
*
* @access public
*/
 function delete_skin($skin) {
 if (!is_dir(ROOT.'skins/'.$skin)) return 0;
 $this->removeTree(ROOT.'skins/'.$skin);
 $this->redirect("?");
}

/**
* copyTree
*
* Copy source directory tree to destination directory
*
* @access public
*/
 function copyTree($source, $destination, $over=0, $patterns=0) {

  $res=1;

  if (!Is_Dir($source)) {
   return 0; // cannot create destination path
  }

  if (!Is_Dir($destination)) {
   if (!mkdir($destination)) {
    return 0; // cannot create destination path
   }
  }


 if ($dir = @opendir($source)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) {
     $res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
    } elseif (Is_File($source."/".$file) && (!file_exists($destination."/".$file) || $over)) {
     if (!is_array($patterns)) {
      $ok_to_copy=1;
     } else {
      $ok_to_copy=0;
      $total=count($patterns);
      for($i=0;$i<$total;$i++) {
       if (preg_match('/'.$patterns[$i].'/is', $file)) {
        $ok_to_copy=1;
       }
      }
     }
     if ($ok_to_copy) {
      $res=copy($source."/".$file, $destination."/".$file);
     }
    }
  }   
  closedir($dir); 
 }
 return $res;
 }


/**
* removeTree
*
* remove directory tree
*
* @access public
*/
 function removeTree($destination) {

  $res=1;

  if (!Is_Dir($destination)) {
    return 0; // cannot create destination path
  }
 if ($dir = @opendir($destination)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($destination."/".$file) && ($file!='.') && ($file!='..')) {
     $res=$this->removeTree($destination."/".$file);
    } elseif (Is_File($destination."/".$file)) {
     $res=unlink($destination."/".$file);
    }
  }     
  closedir($dir); 
  $res=rmdir($destination);
 }
 return $res;
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
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  if (!Is_Dir(ROOT."./skins")) {
   mkdir(ROOT."./skins", 0777);
  }
  parent::install($parent_name);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDEyLCAyMDA2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>