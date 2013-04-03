<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='app_quotes';
  if ($this->mode=='update') {
   global $file;
   if (file_exists($file)) {
    $tmp=LoadFile($file);
    $ok=1;
    //$tmp=str_replace("\r", '', $tmp);
    $lines=mb_split("\n", $tmp);
    $total_lines=count($lines);
    for($i=0;$i<$total_lines;$i++) {
     //$values=explode("\t", $lines[$i]);
     $rec=array();
     $rec_ok=1;
     $rec['BODY']=$lines[$i];
      if ($rec['BODY']=='') {
       $rec_ok=0;
      }
     if ($rec_ok) {
      $old=SQLSelectOne("SELECT ID FROM ".$table_name." WHERE BODY LIKE '".DBSafe($rec['BODY'])."'");
      if ($old['ID']) {
       $rec['ID']=$old['ID'];
       SQLUpdate($table_name, $rec);
      } else {
       SQLInsert($table_name, $rec);
      }
      $out["TOTAL"]++;
     }
    }
   }
  }
?>
