<?php

/*
 * @version 0.1 (auto-set)
 */

DebMes("Running maintenance script");

// BACKUP DATABASE AND FILES
$old_mask = umask(0);

if (!is_dir(DOC_ROOT . '/backup'))
   mkdir(DOC_ROOT . '/backup', 0777);

if (defined('SETTINGS_BACKUP_PATH') && SETTINGS_BACKUP_PATH != '' && is_dir(SETTINGS_BACKUP_PATH))
{
   $target_dir = SETTINGS_BACKUP_PATH;

   if (substr($target_dir, -1) != '/' && substr($target_dir, -1) != '\\')
      $target_dir .= '/';

   $target_dir .= date('Ymd');
}
else
{
   $target_dir  = DOC_ROOT . '/backup/' . date('Ymd');
}

$full_backup = 0;

if (!is_dir($target_dir))
{
   mkdir($target_dir, 0777);
   $full_backup = 1;
}

if (!is_dir(ROOT . 'debmes'))
   mkdir(ROOT . 'debmes', 0777);

if (!is_dir(ROOT . 'cached'))
   mkdir(ROOT . 'cached', 0777);

if (!is_dir(ROOT . 'cached/voice'))
   mkdir(ROOT . 'cached/voice', 0777);

if (!is_dir(ROOT . 'cached/urls'))
   mkdir(ROOT . 'cached/urls', 0777);


if (!defined('LOG_FILES_EXPIRE')) {
 define('LOG_FILES_EXPIRE', 5);
}
if (!defined('BACKUP_FILES_EXPIRE')) {
 define('BACKUP_FILES_EXPIRE', 10);
}
if (!defined('CACHED_FILES_EXPIRE')) {
 define('CACHED_FILES_EXPIRE', 30);
}



echo "Target: " . $target_dir . PHP_EOL;
echo "Full backup: " . $full_backup . PHP_EOL;

sleep(5);

if ($full_backup)
{
   DebMes("Backing up files...");
   echo "Backing up files...";

   if (defined('PATH_TO_MYSQLDUMP'))
      $mysqlDumpPath = PATH_TO_MYSQLDUMP;

   if ($mysqlDumpPath == '')
   {
      if (substr(php_uname(), 0, 7) == "Windows")
         $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
      else
         $mysqlDumpPath = "/usr/bin/mysqldump";
   }

   $mysqlDumpParam = " -h " . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASSWORD;
   $mysqlDumpParam .= " --no-create-db --add-drop-table --databases " . DB_NAME;
   $mysqlDumpParam .= " > " . $target_dir . "/" . DB_NAME . ".sql";

   exec($mysqlDumpPath . $mysqlDumpParam);

   copyTree('./cms', $target_dir . '/cms', 1);
   copyTree('./texts', $target_dir . '/texts', 1);
   copyTree('./sounds', $target_dir . '/sounds', 1);

   echo "OK\n";
}

//removing old log files
$dir = ROOT."debmes/";
foreach (glob($dir."*") as $file) {
 if (filemtime($file) < time() - LOG_FILES_EXPIRE*24*60*60) {
  DebMes("Removing log file ".$file);
  @unlink($file);
 }
}

//removing old backups files
$dir =$target_dir;
foreach (glob($dir."*") as $file) {
 if (filemtime($file) < time() - BACKUP_FILES_EXPIRE*24*60*60) {
  DebMes("Removing backup file ".$file);
  @unlink($file);
 }
}

//removing old cached files
$dir = ROOT."cached/";
foreach (glob($dir."*") as $file) {
 if (filemtime($file) < time() - BACKUP_FILES_EXPIRE*24*60*60) {
  DebMes("Removing cached file ".$file);
  @unlink($file);
 }
}


umask($old_mask);

// CHECK/REPAIR/OPTIMIZE TABLES
$tables = SQLSelect("SHOW TABLES FROM `" . DB_NAME . "`");
$total = count($tables);

for ($i = 0; $i < $total; $i++)
{
   $table = $tables[$i]['Tables_in_' . DB_NAME];

   echo 'Checking table [' . $table . '] ...';

   if ($result = SQLExec("CHECK TABLE " . $table . ";"))
   {
      echo "OK\n";
   }
   else
   {
      echo " broken ... repair ...";
      SQLExec("REPAIR TABLE " . $table . ";");
      echo "OK\n";
   }
}

setGlobal('ThisComputer.started_time', time());
if (time()>=getGlobal('ThisComputer.started_time')) {
 SQLExec("DELETE FROM events WHERE ADDED > NOW()");
 SQLExec("DELETE FROM phistory WHERE ADDED > NOW()");
 SQLExec("DELETE FROM history WHERE ADDED > NOW()");
 SQLExec("DELETE FROM shouts WHERE ADDED > NOW()");
 SQLExec("DELETE FROM jobs WHERE PROCESSED = 1");
 SQLExec("DELETE FROM history WHERE (TO_DAYS(NOW()) - TO_DAYS(ADDED)) >= 5");
}


// CHECKING DATA
/*
$tables = array('commands'         => 'commands',
                'owproperties'     => 'onewire',
                'snmpproperties'   => 'snmpdevices',
                'zwave_properties' => 'zwave',
                'mqtt'             => 'mqtt',
                'modbusdevices'    => 'modbus');

$value_ids = array();

foreach ($tables as $k => $v)
{
   $sqlQuery = "SELECT *
                  FROM $k
                 WHERE LINKED_OBJECT   != ''
                   AND LINKED_PROPERTY != ''";

   $data = SQLSelect($sqlQuery);
   $total = count($data);

   for ($i = 0; $i < $total; $i++)
   {
      $module = $v;
      $property = $data[$i]['LINKED_OBJECT'] . '.' . $data[$i]['LINKED_PROPERTY'];

      if (!$value_ids[$property])
         $value_ids[$property] = getValueIdByName($data[$i]['LINKED_OBJECT'], $data[$i]['LINKED_PROPERTY']);

      if ($value_ids[$property])
      {
         $sqlQuery = "SELECT *
                        FROM pvalues
                       WHERE ID = " . (int)$value_ids[$property];

         $value = SQLSelectOne($sqlQuery);

         if (!$value['LINKED_MODULES'])
            $tmp = array();
         else
            $tmp = explode(',', $value['LINKED_MODULES']);


         if (!in_array($v, $tmp))
         {
            echo "$property adding linked" . PHP_EOL;
            $tmp[] = $v;
            $value['LINKED_MODULES'] = implode(',', $tmp);

            SQLUpdate('pvalues', $value);
         }
      }
      else
      {
         echo "$property removing linked" . PHP_EOL;
      }
   }
}
*/

$sqlQuery = "SELECT pvalues.*, objects.TITLE as OBJECT_TITLE, properties.TITLE as PROPERTY_TITLE
               FROM pvalues
               JOIN objects ON pvalues.OBJECT_ID = objects.id
               JOIN properties ON pvalues.PROPERTY_ID = properties.id
              WHERE pvalues.PROPERTY_NAME != CONCAT_WS('.', objects.TITLE, properties.TITLE)";

$data = SQLSelect($sqlQuery);
$total = count($data);

for ($i = 0; $i < $total; $i++)
{
   $objectProperty = $data[$i]['OBJECT_TITLE'] . "." . $data[$i]['PROPERTY_TITLE'];

   if ($data[$i]['PROPERTY_NAME'])
      echo "Incorrect: " . $data[$i]['PROPERTY_NAME'] . " should be $objectProperty" . PHP_EOL;
   else
      echo "Missing: " . $objectProperty . PHP_EOL;

   $sqlQuery = "SELECT *
                  FROM pvalues
                 WHERE ID = '" . $data[$i]['ID'] . "'";

   $rec = SQLSelectOne($sqlQuery);

   $rec['PROPERTY_NAME'] = $data[$i]['OBJECT_TITLE'] . "." . $data[$i]['PROPERTY_TITLE'];

   SQLUpdate('pvalues', $rec);
}
