<?
/*
* @version 0.1 (auto-set)
*/


 function files($parameters="") {
  global $REQUEST_URI;
  global $out;
  global $mode;

  $dir=str_replace('\\\'', "'", $_SERVER['REQUEST_URI']);
  $dir=preg_replace("/^\/.*?\//", "./", $dir);
  $dir=preg_replace("/\?.*?$/", "", $dir);
  $dir=urldecode($dir);

  //echo utf2win($dir);
  //exit;

  $paths=split("/", $dir);
  $old="";
  $history=array();
  foreach($paths as $v) {
   if ($v=="") continue;
   if ($v==".") continue;
   $rec=array();
   $rec['TITLE']=$v;
   $rec['PATH']=$old."$v/";
   $old.="$v/";
   $history[]=$rec;
  }
  $out['HISTORY']=$history;
  $out['CURRENT_DIR_TITLE']=($dir);
  $out['CURRENT_DIR']=urlencode($dir);

  $act_dir=INIT_DIR."$dir";

  if (@$mode=="descr") {
   global $file;
   global $new_descr;
   global $REMOTE_ADDR;
   global $can_edit;
   if (strpos($can_edit, $REMOTE_ADDR)>0) setDescription($act_dir, $file, $new_descr);
   $mode="";
   header("Location:?\n\n");
   exit;
  }

  $descriptions=getDescriptions($act_dir);

  $d=openDir($act_dir);
  $dirs=array();
  while ($file=readDir($d)) {
   if (($file==".") || ($file=="..")) {
    continue;
   }
   if (Is_Dir($act_dir.$file)) {
    $rec=array();
    $rec['TITLE']=$file;
    $rec['TITLE_SHORT']=$rec['TITLE'];
    if (strlen($rec['TITLE_SHORT'])>30) {
     $rec['TITLE_SHORT']=substr($rec['TITLE_SHORT'], 0, 30).'...';
    }
    if (IsSet($descriptions[$file])) {
     $rec['DESCR']=$descriptions[$file];
    }
    $rec['PATH']=urlencode("$file").'/';
    $rec['REAL_PATH']=$dir.$file;
    $dirs[]=$rec;
   }
  }
  closeDir($d);

  $dirs=mysort_array($dirs, "TITLE");

  if (count($dirs)>0) $out['DIRS']=$dirs;

  $d=openDir($act_dir);
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
    if (strlen($rec['TITLE'])>30) {
     $rec['TITLE_SHORT']=substr($rec['TITLE'], 0, 30)."...";
    } else {
     $rec['TITLE_SHORT']=$rec['TITLE'];
    }
    $rec['PATH']="$file";
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
    $files[]=$rec;
   }
  }
  closeDir($d);

  $files=mysort_array($files, "TITLE");

  if (count($files)>0) $out['FILES']=$files;
  $out['TOTAL_FILES']=count($files);
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

?>