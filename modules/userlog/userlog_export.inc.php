<?php
/*
* @version 0.1 (auto-set)
*/

  $table_name='userlog';
  if (count($ids)) {
//   $tmp=SQLSelect("SELECT * FROM $table_name WHERE ");
   $qry=" userlog.ID IN (".implode(',', $ids).")";
  } else {
//   $tmp=SQLSelect("SELECT * FROM $table_name");
   $qry=" 1";
  }
  $tmp=SQLSelect("SELECT admin_users.NAME as USER, userlog.MESSAGE, userlog.IP, DATE_FORMAT(ADDED, '%m/%d/%Y %H:%i:%s') as ADDED FROM userlog, admin_users WHERE userlog.USER_ID=admin_users.ID AND $qry ORDER BY userlog.ID DESC");
  $total=count($tmp);
  if ($total) {
   $res='';
   $fields=&$tmp[0];
   foreach($fields as $k=>$v) {
    if ($k!='ID' && (!isset($this->{strtolower($k)}) || $k=='TITLE')) {
     $f[]=$k;
    }
   }
   $res.=implode("\t", $f);
   for($i=0;$i<$total;$i++) {
    $line=array();
    foreach($tmp[$i] as $k=>$v) {
     if ($k!='ID' && (!isset($this->{strtolower($k)}) || $k=='TITLE')) {
      $line[]=trim($v);
     }
    }
    $res.="\r\n".implode("\t", $line);
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
