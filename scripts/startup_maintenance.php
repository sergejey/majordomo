<?php
/*
* @version 0.1 (auto-set)
*/

// BACKUP DATABASE AND FILES
$old_mask = umask(0);


$filename  = ROOT . '/database_backup/db.sql';
if (file_exists($filename)) {
 echo ("Running: mysql -u ".DB_USER." -p".DB_PASSWORD." ".DB_NAME." <".$filename."\n");
 exec("mysql -u ".DB_USER." -p".DB_PASSWORD." ".DB_NAME." <".$filename);
}
 
if (!is_dir(DOC_ROOT . '/backup')) 
{
   mkdir(DOC_ROOT . '/backup', 0777);
}

if (defined('SETTINGS_BACKUP_PATH') && SETTINGS_BACKUP_PATH!='' && is_dir(SETTINGS_BACKUP_PATH)) {
 $target_dir=SETTINGS_BACKUP_PATH;
 if (substr($target_dir, -1)!='/' && substr($target_dir, -1)!='\\') {
  $target_dir.='/';
 }
 $target_dir.=date('Ymd');
} else {
 $target_dir  = DOC_ROOT . '/backup/' . date('Ymd');
}
$full_backup = 0;

if (!is_dir($target_dir)) 
{
   mkdir($target_dir, 0777);
   $full_backup=1;
}

echo "Target: ".$target_dir."\n";
echo "Full backup: ".$full_backup."\n";
sleep(5);

if ($full_backup) 
{
   echo "Backing up files...";
   if (defined('PATH_TO_MYSQLDUMP')) {
    exec(PATH_TO_MYSQLDUMP." --user=".DB_USER." --password=".DB_PASSWORD." --no-create-db --add-drop-table --databases ".DB_NAME.">". $target_dir . "/" . DB_NAME . ".sql");
   } else {
    if (substr(php_uname(), 0, 7) == "Windows") {
      exec(SERVER_ROOT . "/server/mysql/bin/mysqldump --user=".DB_USER." --password=".DB_PASSWORD." --no-create-db --add-drop-table --databases " . DB_NAME . ">" . $target_dir . "/" . DB_NAME . ".sql");
    }
    else {
      exec("/usr/bin/mysqldump --user=" . DB_USER . " --password=".DB_PASSWORD." --no-create-db --add-drop-table --databases ". DB_NAME . ">" . $target_dir . "/" . DB_NAME . ".sql");
    }
   }
   copyTree('./cms',    $target_dir . '/cms',    1);
   copyTree('./texts',  $target_dir . '/texts',  1);
   copyTree('./sounds', $target_dir . '/sounds', 1);
   echo "OK\n";
}

umask($old_mask);

// CHECK/REPAIR/OPTIMIZE TABLES                
$tables = SQLSelect("SHOW TABLES FROM `" . DB_NAME . "`");
$total = count($tables);
 
for( $i = 0; $i < $total; $i++)
{
   $table = $tables[$i]['Tables_in_' . DB_NAME];
  
   echo 'Checking table ['.$table.'] ...';
  
   //mysql_query("CHECK TABLE ".$table."");
   if ($result=mysql_query("CHECK TABLE ".$table.";"))
   {
      echo "OK\n";
   }
   else 
   {
      echo " broken ... repair ...";
      SQLExec("REPAIR TABLE " . $table.";");
      echo "OK\n";
   }
}

SQLExec("DELETE FROM events WHERE ADDED > NOW()");
SQLExec("DELETE FROM phistory WHERE ADDED > NOW()");
SQLExec("DELETE FROM history WHERE ADDED > NOW()");
SQLExec("DELETE FROM shouts WHERE ADDED > NOW()");
SQLExec("DELETE FROM jobs WHERE PROCESSED = 1");
SQLExec("DELETE FROM history WHERE (TO_DAYS(NOW()) - TO_DAYS(ADDED)) >= 5");


// CHECKING DATA

 $tables=array('commands'=>'commands', 'owproperties'=>'onewire', 'snmpproperties'=>'snmpdevices', 'zwave_properties'=>'zwave', 'mqtt'=>'mqtt', 'modbusdevices'=>'modbus');

 $value_ids=array();

 foreach($tables as $k=>$v) {
  $data=SQLSelect("SELECT * FROM $k WHERE LINKED_OBJECT!='' AND LINKED_PROPERTY!=''");
  $total=count($data);
  for($i=0;$i<$total;$i++) {
   $module=$v;
   $property=$data[$i]['LINKED_OBJECT'].'.'.$data[$i]['LINKED_PROPERTY'];
   if (!$value_ids[$property]) {
    $value_ids[$property]=getValueIdByName($data[$i]['LINKED_OBJECT'], $data[$i]['LINKED_PROPERTY']);
   }

   //echo "Checking $v: $property<br>";
   if ($value_ids[$property]) {
    $value=SQLSelectOne("SELECT * FROM pvalues WHERE ID=".(int)$value_ids[$property]);
    if (!$value['LINKED_MODULES']) {
     $tmp=array();
    } else {
     $tmp=explode(',', $value['LINKED_MODULES']);
    }
    if (!in_array($v, $tmp)) {
     echo "$property adding linked\n";
     $tmp[]=$v;
     $value['LINKED_MODULES']=implode(',', $tmp);
     SQLUpdate('pvalues', $value);
    }
   } else {
    echo "$property removing linked\n";
    //
    /*
    $data[$i]['LINKED_OBJECT']='';
    $data[$i]['LINKED_PROPERTY']='';
    SQLUpdate($k, $data[$i]);
    */
   }
  }
 }

 $data=SQLSelect("SELECT pvalues.*, objects.TITLE as OBJECT_TITLE, properties.TITLE as PROPERTY_TITLE  FROM pvalues JOIN objects ON pvalues.OBJECT_ID = objects.id JOIN properties ON pvalues.PROPERTY_ID = properties.id WHERE pvalues.PROPERTY_NAME != CONCAT_WS('.', objects.TITLE, properties.TITLE )");
 $total=count($data);
 for($i=0;$i<$total;$i++) {
  if ($data[$i]['PROPERTY_NAME']) {
   echo "Incorrect: ".$data[$i]['PROPERTY_NAME']." should be ".$data[$i]['OBJECT_TITLE'].".".$data[$i]['PROPERTY_TITLE']."\n";
  } else {
   echo "Missing: ".$data[$i]['OBJECT_TITLE'].".".$data[$i]['PROPERTY_TITLE']."\n";
  }
  $rec=SQLSelectOne("SELECT * FROM pvalues WHERE ID='".$data[$i]['ID']."'");
  $rec['PROPERTY_NAME']=$data[$i]['OBJECT_TITLE'].".".$data[$i]['PROPERTY_TITLE'];
  SQLUpdate('pvalues', $rec);
 }
