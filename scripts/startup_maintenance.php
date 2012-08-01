<?
/*
* @version 0.1 (auto-set)
*/

 // BACKUP DATABASE AND FILES
 $old_mask=umask(0);
 if (!is_dir(DOC_ROOT.'/backup')) {
  mkdir(DOC_ROOT.'/backup', 0777);
 }

 $target_dir=DOC_ROOT.'/backup/'.date('Ymd');
 $full_backup=0;
 if (!is_dir($target_dir)) {
  mkdir($target_dir, 0777);
  $full_backup=1;
 }
 if ($full_backup) {
  echo "Backing up files...";
  exec(SERVER_ROOT."/server/mysql/bin/mysqldump --user=root --no-create-db --add-drop-table --databases db_terminal>".$target_dir."/db_terminal.sql");
  copyTree('./cms', $target_dir.'/cms', 1);
  copyTree('./texts', $target_dir.'/texts', 1);
  copyTree('./sounds', $target_dir.'/sounds', 1);
  echo "OK\n";
 }
 umask($old_mask);

 // CHECK/REPAIR/OPTIMIZE TABLES                
 $tables=SQLSelect("SHOW TABLES FROM db_terminal");
 $total=count($tables);
 for($i=0;$i<$total;$i++) {
  $table=$tables[$i]['Tables_in_db_terminal'];
  echo $table.' ...';
  if ($result=mysql_query("SELECT * FROM ".$table." LIMIT 1")) {
   echo "OK\n";
  } else {
   echo " broken ... repair ...";
   SQLExec("REPAIR TABLE ".$table);
   echo "OK\n";
  }
 }
 SQLExec("DELETE FROM events WHERE ADDED>NOW()");
 SQLExec("DELETE FROM phistory WHERE ADDED>NOW()");
 SQLExec("DELETE FROM history WHERE ADDED>NOW()");
 SQLExec("DELETE FROM shouts WHERE ADDED>NOW()");


?>
