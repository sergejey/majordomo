<?php
/*
* @version 0.1 (auto-set)
*/


/*
Установка переменных, которые можно использовать в коммандах

SET PROJECTS_DIR=D:/jey/projects

Игнорирование папок и файлов, которые включают указанное слово

IGNORE project_files

Синхронизация (добавление новых и измененных файлов)

LOCAL_DIR/wiki => PROJECTS_DIR/jeywork/wiki
LOCAL_DIR/wiki <= PROJECTS_DIR/jeywork/wiki

Перемещение всех файлов из одной папки в другую

f:/video/daily <- /video_daily
/video_daily -> f:/video/daily

Добавление только файлов, определенной давности (более ранние файлы игнорируются)

/music/podcasts <+ D:/jey/handled/music/Podcasts 2 DAYS OLD

Удаление файлов старше определенного "возраста"

CLEAR D:/jey/handled/music/Podcasts 2 DAYS OLD

Синхронизация с полным зеркалирование, т.е. на месте назначения будут удаляться файлы и папки, которых нет на источнике

SOURCE/dir !> DESTINATION/dir
SOURCE/dir <! DESTINATION/dir

Типы путей

D:/jey/handled/music/Podcasts
/jey/sync
NET:pas/work

В путях можно использовать обозначения даты как в команде PHP date(), но с символами % или $
например:
d:/jey/foto => d:/jey/foto2/%Y/%m-%F (файлы из первой папки будут разбросаны по годам и месяцам во второй)
при этом если используется %, то в качестве времени берется время модификации файла, а если $, то текущее время

*/


 function preparePathTime($s, $mtime) {
  // %d #d &d $d
  $symbs=array('a', 'A', 'B', 'd', 'D', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'l', 'L', 'm', 'M', 'n', 'O', 'r', 's', 'S', 't', 'T', 'U', 'w', 'W', 'Y', 'y', 'z', 'Z');
  foreach($symbs as $v) {
   $s=str_replace('$'.$v, date($v), $s);
   $s=str_replace('%'.$v, date($v, $mtime), $s);
  }
  return $s;  
 }

 function is_dir2($d) {

 $d=str_replace('NET:', '//', $d);

 if (is_dir($d)) {
  return 1;
 }

  if ($node = @opendir($d)) {
   closedir($node);
   return 1;
  } else {
   return 0;
  }  

 }

function remove_old_files($path, $days) {

 $mtime=filemtime($path);

 $diff=round((time()-$mtime)/60/60/24, 2);

 if ($diff>$days) {
  echo 'Removing '.$path.' ('.$diff." days old)\n";
  unlink($path);
 }
 
}


// --------------------------------------------------------------------
/**
* Title
*
* Description
*
* @access public
*/
 function copyNewFile($path, $days) {

 global $dirs;
 global $current_dir;
 global $current_dest;
 global $acc;
 global $ignores;


 $mtime=filemtime($path);
 $diff=round((time()-$mtime)/60/60/24, 2);
 if ($diff>$days) {
  return;
 }


 foreach($ignores as $ptn) {
  if (preg_match("/".$ptn."/is", $path)) return;
 }

 $tmdiff=0;

 if (!$current_dest) {
  $dest=$dirs[$current_dir];
 } else {
  $dest=$current_dest;
 }

  $dest=str_replace($current_dir, $dest, $path);
  $dest_path=str_replace(basename($dest), '', $dest);

  $new_dest_path=preparePathTime($dest_path, $mtime);
  $dest=str_replace($dest_path, $new_dest_path, $dest);
  $dest_path=$new_dest_path;

  if (!is_dir2($dest_path)) {
   if (!makedir($dest_path)) return 0;
  }

  if (!file_exists($dest)) {
   echo $path." -> ".$dest." (new)\n";
   copyFile($path, $dest);
  }

 }

// --------------------------------------------------------------------

function checkfile($path, $move) {
 global $dirs;
 global $current_dir;
 global $current_dest;
 global $acc;
 global $ignores;
 global $files_copied;

 foreach($ignores as $ptn) {
  if (preg_match("/".$ptn."/is", $path)) return;
 }

 $tmdiff=0;

 if (!$current_dest) {
  $dest=$dirs[$current_dir];
 } else {
  $dest=$current_dest;
 }

 $path=str_replace('NET:', '//', $path);
 $current_dir=str_replace('NET:', '//', $current_dir);

 $mtime=filemtime($path);

 $dest=str_replace('NET:', '//', $dest);
 $dest=str_replace($current_dir, $dest, $path);
 $dest_path=str_replace(basename($dest), '', $dest);

  $new_dest_path=preparePathTime($dest_path, $mtime);
  $dest=str_replace($dest_path, $new_dest_path, $dest);
  $dest_path=$new_dest_path;


 if (!is_dir2($dest_path)) {
  //echo "\n\n make dir: $dest_path \n\n";
  if (!makedir($dest_path)) return 0;
 }

 if (!file_exists($dest)) {
  echo $path." -> ".$dest." (new)\n";
  copyFile($path, $dest);
 } else {
  $dest_size=filesize($dest);
  $src_size=filesize($path);
  $tmdiff=filemtime($path)-filemtime($dest);
  if ($tmdiff>$acc || ($dest_size==0 && $src_size!=0)) { 
   $status="updated $tmdiff";
   echo $path." -> ".$dest." (updated ".round($tmdiff/60/60, 1)." h)\n";
   copyFile($path, $dest);
  } else {
   //echo $path." -> ".$dest." (OK ".round($tmdiff/60/60, 1)." h)\n";
   $fs=filesize($path);
   //if ($fs>(2*1024*1024)) {
    $k=basename($path).'_'.$fs;
    //$files_copied[$k]=$dest;
   //}
  }
 }

 if ($move) {
  unlink($path);
 }


}


function copyFile($src, $dst) {
 global $files_copied;
 $size_limit=2000*1024*1024;
 $fs=filesize($src);

 if ($fs==0) {
  return;
 }

 $fs_mb=round($fs/1024/1024, 2);
 if ($fs>$size_limit) {
  $k=basename($src).'_'.$fs;
  if ($files_copied[$k]=='') {
   echo "Size: ".$fs_mb."Mb\n";
   $src=str_replace('/', '\\', $src);
   $dst=str_replace('/', '\\', $dst);
   system('copy "'.$src.'" "'.$dst.'"'); // long copy
   $files_copied[$k]=$dst;
  } else {
   echo " already copied to (".$files_copied[$k].")\n";
  }
 } else {
  copy($src, $dst);
 }
 touch($dst, filemtime($src));
}

// walking directory 
function walk_dir( $dir, $callback, $move = 0 ) {
 global $ignores; 
 $dir=str_replace('NET:', '//', $dir);
 $dir .= '/';
 foreach($ignores as $ptn) {
  if (preg_match("/".$ptn."/is", $dir)) return;
 }

 //if (!preg_match('/mail.ru Blogs/is', $dir)) {
 // return;
 //}
 echo "processing $dir\n";

 if (!is_dir2($dir)) {
  return;
 }

 $handle = opendir( $dir );
 while ( false !== $thing = readdir( $handle ) ) { 
  if( $thing == '.' || $thing == '..' ) continue;
  $thing = $dir . $thing;
  if( is_dir2( $thing ) ) 
   walk_dir( $thing, $callback , $move);
  elseif ( is_file( $thing )) 
   call_user_func( $callback, $thing , $move);
 } closedir( $handle );
}

// walking directory 2 (removing destination if neccessary)
function walk_dir2( $dir, $callback, $move = 0 ) {
 global $ignores; 
 global $dirs;
 global $acc;
 global $current_dir;
 global $current_dest;


 $dir=str_replace('NET:', '//', $dir);
 $dir .= '/';
 foreach($ignores as $ptn) {
  if (preg_match("/".$ptn."/is", $dir)) return;
 }

 $tmpdir=$current_dir;
 $tmpdir=str_replace('NET:', '//', $tmpdir);
 $dest=$dirs[$dir];
 $dest=str_replace($tmpdir, $current_dest, $dir);

 // ADDING NEW/UPDATED FILES
 $processed=array();

 //$dir=str_replace('/', '\\', $dir);
 if (!is_dir2($dir)) {
  echo "Dir not found: $dir\n";
  return;
 }

 $handle = opendir( $dir );
 while ( false !== $thing = readdir( $handle ) ) { 
  if( $thing == '.' || $thing == '..' ) continue;
  $processed[$thing]=1;
  $thing = $dir . $thing;
  if( is_dir2( $thing ) ) 
   walk_dir2( $thing, $callback , $move);
  elseif ( is_file( $thing )) {
   call_user_func( $callback, $thing , $move);
  }
 } 
 closedir( $handle );

// print_r($processed);

 // REMOVING FILES
 $handle = opendir($dest);
 while ( false !== $thing = readdir( $handle ) ) { 
  if( $thing == '.' || $thing == '..' ) continue;
  if (!$processed[$thing]) {
   if (is_file($dest.$thing)) {
    echo "Removing file: ".$dest.$thing." \n";
    unlink($dest.$thing);
   } elseif (is_dir2($dest.$thing)) {
    echo "Removing dir: ".$dest.$thing." \n";
    removeTree($dest.$thing);
   }
  }
 }
 closedir( $handle );
// exit;


}


// creating new directory

function makeDir($dir, $sep='/') {
 $tmp=explode($sep, $dir);
 $cr="";
 for($i=0;$i<count($tmp);$i++) {
  $cr.=$tmp[$i]."$sep";
  if (!Is_Dir2($cr)) {
   echo "Making folder [$cr]\n";
   mkDir($cr);
  }
 }
}


// removing dir
 function removeTree($destination) {

  $res=1;

  if (!Is_Dir2($destination)) {
    return 0; // cannot create destination path
  }
 if ($dir = @opendir($destination)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir2($destination."/".$file) && ($file!='.') && ($file!='..')) {
     $res=removeTree($destination."/".$file);
    } elseif (Is_File($destination."/".$file)) {
     $res=unlink($destination."/".$file);
    }
  }     
  closedir($dir); 
  $res=rmdir($destination);
 }
 return $res;
 }


// loading file


// processing lines

function processLines($data) {
 global $ignores;

 $hash=array();
 $data=str_replace("\r", '', $data);
 $lines=explode("\n", $data);
 $total=count($lines);
 for($i=0;$i<$total;$i++) {
  processLine($lines[$i], $hash);
 }
 return $total;

}

// processing line

function processLine($line, $hash='') {
global $current_dest;
global $current_dir;
global $ignores;

 if (!is_array($ignores)) {
  $ignores=array();
 }
 if (!is_array($hash)) {
  $hash=array();
 }


 $line=trim($line);

 foreach($hash as $k=>$v) {
  $line=str_replace($k, $v, $line);
 }
 echo $line."\n";

 if (preg_match('/^\/\//', $line)) {

  return;

 } elseif(preg_match('/^IGNORE (.+?)$/i', $line, $matches)) {

  $ignores[]=trim($matches[1]);

 } elseif(preg_match('/^SET (.+?)=(.+?)$/i', $line, $matches)) {

  $key=trim($matches[1]);
  $value=trim($matches[2]);
  $hash[$key]=$value;

 } elseif (preg_match('/^CLEAR (.+?) (\d+) DAYS OLD$/is', $line, $matches)) {

  $from=trim($matches[1]);
  $current_dir=$from;
  $days=(int)($matches[2]);

  if ($days>0) {
   walk_dir($from, "remove_old_files", $days);
  }

 } elseif (preg_match('/^(.+?)\+>(.+?) (\d+) DAYS OLD$/is', $line, $matches)) {

  $from=trim($matches[1]);
  $to=trim($matches[2]);
  $current_dir=$from;
  $current_dest=$to;
  $days=(int)($matches[3]);
  walk_dir($from, "copyNewFile", $days);

 } elseif (preg_match('/^(.+?)<\+(.+?) (\d+) DAYS OLD$/is', $line, $matches)) {

  $to=trim($matches[1]);
  $from=trim($matches[2]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   echo "\n Cannot make destination dir ($to)\n";
   return;
  }
  */

  $days=(int)($matches[3]);
  walk_dir($from, "copyNewFile", $days);

 } elseif (preg_match('/^(.+?)=>(.+?)$/is', $line, $matches)) {

  $from=trim($matches[1]);
  $to=trim($matches[2]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   echo "\n Cannot make destination dir ($to)\n";
   return;
  }
  */
  //echo "walking $from\n";

  walk_dir($from, "checkfile");

 } elseif (preg_match('/^(.+?)<=(.+?)$/is', $line, $matches)) {

  $from=trim($matches[2]);
  $to=trim($matches[1]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   return;
  }
  */

  walk_dir($from, "checkfile");

 } elseif (preg_match('/^(.+?)\!>(.+?)$/is', $line, $matches)) {

  $from=trim($matches[1]);
  $to=trim($matches[2]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   return;
  }
  */

  walk_dir2($from, "checkfile");

 } elseif (preg_match('/^(.+?)<\!(.+?)$/is', $line, $matches)) {

  $from=trim($matches[2]);
  $to=trim($matches[1]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   return;
  }
  */

  walk_dir2($from, "checkfile");


 } elseif (preg_match('/^(.+?)->(.+?)$/is', $line, $matches)) {

  $from=trim($matches[1]);
  $to=trim($matches[2]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   return;
  }
  */

  walk_dir($from, "checkfile", 1);

 } elseif (preg_match('/^(.+?)<-(.+?)$/is', $line, $matches)) {

  $from=trim($matches[2]);
  $to=trim($matches[1]);
  $current_dir=$from;
  $current_dest=$to;

  /*
  if (!is_dir2($to) && !@mkdir($to)) {
   return;
  }
  */

  walk_dir($from, "checkfile", 1);

 }
// echo $line."\n";
}

function UTF_Encode( $str, $type )
{
 // $type:
// 'w' - encodes from UTF to win
 // 'u' - encodes from win to UTF

   static $conv='';
   if (!is_array ( $conv ))
   {   
       $conv=array ();
       for ( $x=128; $x <=143; $x++ )
       {
         $conv['utf'][]=chr(209).chr($x);
         $conv['win'][]=chr($x+112);
       }

       for ( $x=144; $x <=191; $x++ )
       {
               $conv['utf'][]=chr(208).chr($x);
               $conv['win'][]=chr($x+48);
       }
 
       $conv['utf'][]=chr(208).chr(129);
       $conv['win'][]=chr(168);
       $conv['utf'][]=chr(209).chr(145);
       $conv['win'][]=chr(184);
     }
     if ( $type=='w' )
         return str_replace ( $conv['utf'], $conv['win'], $str );
     elseif ( $type=='u' )
         return str_replace ( $conv['win'], $conv['utf'], $str );
     else
       return $str;
}

 function copyTree($source, $destination, $over=0) {

  $res=1;

  if (!Is_Dir($source)) {
   return 0; // incorrect source path
  }

  if (!Is_Dir($destination)) {
   if (!mkdir($destination, 0777)) {
    return 0; // cannot create destination path
   }
  }


 if ($dir = @opendir($source)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) {
     $res=copyTree($source."/".$file, $destination."/".$file, $over);
    } elseif (Is_File($source."/".$file) && (!file_exists($destination."/".$file) || $over)) {
     $res=copy($source."/".$file, $destination."/".$file);
    }
  }   
  closedir($dir); 
 }
 return $res;
 }



?>