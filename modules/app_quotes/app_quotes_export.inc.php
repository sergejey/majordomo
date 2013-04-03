<?php
/*
* @version 0.1 (wizard)
*/
  $table_name='app_quotes';
  if (count($ids)) {
   $tmp=SQLSelect("SELECT * FROM $table_name WHERE ID IN (".implode(',', $ids).")");
  } else {
   $tmp=SQLSelect("SELECT * FROM $table_name");
  }
  $total=count($tmp);
  if ($total) {
   $res='';
   /*
   $fields=&$tmp[0];
   foreach($fields as $k=>$v) {
    if ($k!='ID' && (!isset($this->{strtolower($k)}) || $k=='TITLE')) {
     $f[]=$k;
    }
   }
   $res.=implode("\t", $f);
   */
   for($i=0;$i<$total;$i++) {
    $line=array();
    foreach($tmp[$i] as $k=>$v) {
     if ($k!='ID' && (!isset($this->{strtolower($k)}) || $k=='TITLE')) {
      $line[]=trim($v);
     }
    }
    $res.=implode("\t", $line)."\n";
   }
$filename=$table_name."_export.txt";
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=".$filename);
header("Pragma: no-cache");
header("Expires: 0");
echo $res;
   exit;
  }
?>
