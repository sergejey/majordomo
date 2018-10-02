<?php

/**
 * MySQL database class
 *
 * Used by all modules to work with MySQL database
 *
 * @package framework
 * @author Serge Dzheigalo <info@au78.com>
 * @copyright http://www.au78.com/ (c) 2002-2008
 * @version 1.0b
 */

/**
 * MySQL database class
 * @category DataBase
 * @package Framework
 * @author Serge Dzheigalo <info@au78.com>
 * @copyright 2002-2008 Atmatic
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/mysql.class.php
 */

if (!defined('MYSQL_BOTH')) {
 define('MYSQL_BOTH',MYSQLI_BOTH);
 define('MYSQL_NUM',MYSQLI_NUM);
 define('MYSQL_ASSOC',MYSQLI_ASSOC);
}


class mysql
{
   /**
    * @var string database host
    * @access private
    */
   var $host;
   
   /**
    * @var string database port
    * @access private
    */
   var $port;
   
   /**
    * @var string database access username
    * @access private
    */
   var $user;
   
   /**
    * @var string database access password
    * @access private
    */
   var $password;
   
   /**
    * @var string database name
    * @access private
    */
   var $dbName; // database name
   
   /**
    * @var int database handler
    * @access private
    */
   var $dbh;

   /**
    * @var int latest transaction timestamp
    * @access private
    */
   var $latestTransaction;

   /**
    * @var int connection check timeout
    * @access private
    */
   var $pingTimeout;
   
   /**
    * MySQL constructor
    * used to create mysql database object and connect to database
    * @param string $host     Mysql host
    * @param string $port     Mysql port
    * @param string $user     Mysql user
    * @param string $password Mysql password
    * @param string $database Mysql database name
    * @access public
    */
   public function __construct($host, $port, $user, $password, $database)
   {
      $this->host     = $host;
      $this->port     = $port;
      $this->user     = $user;
      $this->password = $password;
      $this->dbName   = $database;

      $this->latestTransaction = time();
      $this->pingTimeout = 5*60;

      // Чтобы не сломать сторонние модули, если они вдруг смотрять на этот параметр
      $this->connected = true;
      //$this->Connect(); // Коннект можно делать, только при первом запросе к базе. До этого он не нужен
   }

   public function __destruct()
   {
      $this->Disconnect();
   }

   /**
    * Connects to database
    * @access private
    * @return int connection result (1 - success, 0 - failed)
    */
   public function Connect()
   {
      if ($this->dbh) return true;
      // connects to database
      if ($this->port) {
         $this->dbh = @mysql_connect(''.$this->host . ":" . $this->port, $this->user, $this->password);
      } else {
         $this->dbh = @mysql_connect(''.$this->host , $this->user, $this->password);
      }
      if (!$this->dbh) {
         Define('NO_DATABASE_CONNECTION',1);
         registerError('sqlconn', "Error connection");
         return 0;
      }
      if (!@mysql_select_db($this->dbName, $this->dbh))
      {
         Define('NO_DATABASE_CONNECTION',1);
         $this->Error("Selecting db: ".$this->dbName, 0);
         return 0;
      }
      else
      {
         $this->latestTransaction=time();
         mysql_query("SET NAMES 'utf8';", $this->dbh);
         mysql_query("SET CHARACTER SET 'utf8';", $this->dbh);
         mysql_query("set character_set_client='utf8';", $this->dbh);
         mysql_query("set character_set_results='utf8';", $this->dbh);
         mysql_query("set collation_connection='utf8_general_ci';", $this->dbh);
         
         return 1;
      }
   }

   /**
    * Execute SQL query
    * @param string $query SQL query
    * @return mixed execution result (0 - failed)
    * @access public
    */
   public function Exec($query)
   {
      if (!$this->dbh && !$this->Connect()) return false;
      
      if ((time()-$this->latestTransaction)>$this->pingTimeout) {
       $this->Ping();
      }

      $this->latestTransaction=time();
      $result = mysql_query($query, $this->dbh);
      
      if (!$result)
      {
         $this->Error($query,0);
         return 0;
      }
      
      return $result;
   }

   /**
    * Execute SQL SELECT query and return all records
    *
    * This method returns records as array of assosiated arrays (by field names)
    *
    * @param string $query SQL SELECT query
    * @return array execution result
    * @access public
    */
   public function Select($query)
   {
      $res = array();
      
      if ($result = $this->Exec($query))
      {
         while ($rec = mysql_fetch_array($result, MYSQL_ASSOC))
         {
            $res[] = $rec;
         }
      }
      else
      {
         $this->Error($query,0);
      }

      return $res;
   }

   /**
    * Execute SQL SELECT query and return first record
    *
    * This method returns record assosiated array (by field names)
    *
    * @param string $query SQL SELECT query
    * @return array|void execution result
    * @access public
    */
   public function SelectOne($query)
   {
      if ($result = $this->Exec($query))
      {
         $rec = mysql_fetch_array($result, MYSQL_ASSOC);
         
         return $rec;
      }
      else
      {
         $this->Error($query);
      }
   }

   public function Ping()
   {
      if (!$this->dbh && !$this->Connect()) return false;
      
      $test_query = "SHOW TABLES FROM ".$this->dbName;
      $result = @mysql_query($test_query,$this->dbh);
      $tblCnt = 0;
      if ($result) {
         while($tbl = mysql_fetch_array($result)) {
            $tblCnt++;
         }
      }
      if ($tblCnt>0) {
         return true;
      }
   }

   /**
    * Execute SQL UPDATE query for one record
    *
    * Record is defined by assosiated array
    *
    * @param string $table Table to update
    * @param array  $data  Record to update
    * @param string $ndx   Index field (used in WHERE part of SQL request)
    * @return int
    * @access public
    */
   public function Update($table, $data, $ndx = "ID")
   {
      $qry = "UPDATE `$table` SET ";
      
      foreach ($data as $field => $value)
      {
         if (!is_Numeric($field))
            $qry .= "`$field`='" . $this->DBSafe1($value) . "', ";
      }
      
      $qry  = substr($qry, 0, strlen($qry) - 2);

      if (!isset($data[$ndx])) {
       $data[$ndx]='';
      }

      $qry .= " WHERE $ndx = '" . $data[$ndx] . "'";

     
      if (!$this->Exec($qry))
      {
         $this->Error($qry);
         return 0;
      }
      
      return 1;
   }

   /**
    * Execute SQL INSERT query for one record
    *
    * Record is defined by assosiated array
    *
    * @param string $table Table for new record
    * @param array  $data  Record to insert
    * @return int Execution result (0 - if failed, INSERT ID - if succeed)
    * @access public
    */
   public function Insert($table, &$data)
   {
      $fields = "";
      $values = "";
      
      foreach ($data as $field => $value)
      {
         if (!is_numeric($field))
         {
            $fields .= "`$field`, ";
            $values .= "'" . $this->DBSafe1($value) . "', ";
         }
      }
      
      $fields = substr($fields, 0, strlen($fields) - 2);
      $values = substr($values, 0, strlen($values) - 2);
      
      $qry = "INSERT INTO `$table`($fields) VALUES($values)";
      
      if (!$this->Exec($qry))
      {
         $this->Error($qry);
         return 0;
      }
      
      return mysql_insert_id($this->dbh);
   }

   /**
    * Disconnect from database
    *
    * @access public
    * @return void
    */
   public function Disconnect()
   {
      if ($this->dbh) mysql_close($this->dbh);
      $this->dbh = null;
   }

   /**
    * Used to strip "bad" symbols from sql query results
    *
    * @param string $str string to make "safe"
    * @return string correct string
    * @access public
    */
   public function DbSafe($str)
   {
      $str = mysql_real_escape_string($str);
      $str = str_replace("%", "\%", $str);
      return $str;
   }

   /**
    * Used to strip "bad" symbols from sql query results
    * @param mixed $str string to make "safe"
    * @access public
    * @return string
    */
   public function DbSafe1($str)
   {
      $str = mysql_real_escape_string((string)$str);
      
      return $str;
   }

   /**
    * MySQL database error handler
    *
    * @param string $query used query string
    * @access private
    * @return int
    */
   public function Error($query = "", $stop = 0)
   {
      if (!$this->dbh) return false;
      $err_no = mysql_errno();
      $err_details = mysql_error();
      if (preg_match('/Unknown column/is',$err_details)) {
         unlink(DIR_MODULES.'control_modules/installed');
         //header("Location:".ROOTHTML);exit;
      }
      registerError('sql', $err_no . ": " . $err_details . "\n$query");
      new custom_error($err_no . ": " . $err_details . "<br>$query", $stop);
      return 1;
   }

   /**
    * Returns mysql table definition string (CREATE TABLE ... ;)
    *
    * @param string $table table to get structure
    * @return string table definition
    * @access public
    */
   public function get_mysql_def($table)
   {
      $result = $this->Exec('SHOW CREATE TABLE ' . $table);
      
      if ($result)
      {
         $row = mysql_fetch_row($result);
         
         if (!$row)
            return '';
         
         $row[1] = preg_replace('/DEFAULT CHARSET=(\w+)/', '', $row[1]);
         $row[1] = str_replace(' default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP', '', $row[1]);
         $row[1] = str_replace("\n", ' ', $row[1]);
         
         $strs = trim($row[1]);
            
         return (empty($strs)) ? '' : 'DROP TABLE IF EXISTS ' . $table . ';' . "\n" . $row[1] . ';' . "\n";
      }
      
      return '';
   }

   /**
    * Returns mysql table content string (INSERT INTO ... ;)
    *
    * @param string $table table to get content
    * @return string table content
    * @access public
    */
   public function get_mysql_content($table)
   {
      $content = "";
      $result  = $this->Exec("SELECT * FROM $table");
      
      while ($row = mysql_fetch_row($result))
      {
         $insert = "INSERT INTO $table VALUES (";
         
         $filedsNum = mysql_num_fields($result);

         for ($j = 0; $j < $filedsNum; $j++)
         {
            if (!isset($row[$j]))    $insert .= "NULL,";
            else if ($row[$j] != "") $insert .= "'" . $this->DbSafe($row[$j]) . "',";
            else                     $insert .= "'',";
         }
         
         $insert   = preg_replace("/,$/", "", $insert);
         $insert  .= ");\n";
         $content .= $insert;
      }
      
      return $content;
   }
}

// --------------------------------------------------------------------
// DATABASE FUNCTIONS
// easy database manipulation
// --------------------------------------------------------------------

/**
 * Execute SQL query
 *
 * @param string $query SQL query
 * @global object mysql database object
 * @return mixed execution result (0 - failed)
 */
function SQLExec($query)
{
   if (($query{0} == "#") || ($query == "")) return;
   global $db;
   return $db->Exec($query);
}

/**
 * Used to strip "bad" symbols from sql query results
 * @param mixed $in String to make "safe"
 * @global object mysql database object
 * @return string
 */
function DbSafe($in)
{
   global $db;
   if ($db instanceof mysql) {
      return $db->DbSafe($in);
   } else {
      return false;
   }
}

/**
 * Execute SQL SELECT query and return all records
 *
 * This function returns records as array of assosiated arrays (by field names)
 *
 * @param string $query SQL SELECT query
 * @global object mysql database object
 * @return array execution result
 */
function SQLSelect($query)
{
   global $db;
   if ($db instanceof mysql) {
      return $db->Select($query);
   } else {
      return false;
   }
}

/**
 * Execute SQL SELECT query and return first record
 *
 * This function returns record assosiated array (by field names)
 *
 * @param string $query SQL SELECT query
 * @global object mysql database object
 * @return array execution result
 */
function SQLSelectOne($query)
{
   global $db;
   if ($db instanceof mysql) {
      return $db->SelectOne($query);
   } else {
      return false;
   }
}

/**
 * Execute SQL INSERT query for one record
 *
 * Record is defined by assosiated array
 *
 * @param string $table  Table for new record
 * @param array  $record Record to insert
 * @global object Mysql database object
 * @return int Execution result (0 - if failed, INSERT ID - if succeed)
 */
function SQLInsert($table, &$record)
{
   global $db;
   if ($db instanceof mysql) {
      return $db->Insert($table, $record);
   } else {
      return false;
   }
}

/**
 * Execute SQL UPDATE query for one record
 * @param mixed $table  Table to update
 * @param mixed $record Record to update (assosiated array)
 * @param mixed $ndx    Update by this key (default ID)
 * @return int
 */
function SQLUpdate($table, $record, $ndx = 'ID')
{
   global $db;
   if ($db instanceof mysql) {
      return $db->Update($table, $record, $ndx);
   } else {
      return false;
   }
}

/**
 * Execute SQL UPDATE or INSERT query for one record
 *
 * If ID field is defined record will be updated else it will be inserted
 *
 * @param string $table  Table to update
 * @param array  $record Record to update
 * @param mixed  $ndx    Update or insert by this key (default ID)
 * @global object mysql database object
 * @return int
 */
function SQLUpdateInsert($table, &$record, $ndx = 'ID')
{
   global $db;
 
   if (isset($record[$ndx]))
   {
      if ($db instanceof mysql) {
         return $db->Update($table, $record, $ndx);
      } else {
         return false;
      }
   }
   else
   {
      if ($db instanceof mysql) {
         $record[$ndx] = $db->Insert($table, $record);
      } else {
         return false;
      }
      return $record[$ndx];
   }
}

/**
 * Alias for SQLUpdateInsert
 * Execute SQL UPDATE or INSERT query for one record
 *
 * If ID field is defined record will be updated else it will be inserted
 *
 * @param string $table  Table to update
 * @param array  $record Record to update
 * @param mixed  $ndx    Update or insert by this key (default ID)
 * @global object mysql database object
 * @return int
 */
function SQLInsertUpdate($table, &$record, $ndx = 'ID')
{
   return SQLUpdateInsert($table, $record, $ndx);
}

/**
* Title
*
* Description
*
* @access public
*/
function SQLGetFields($table) {
   global $db;
   if (!($db instanceof mysql)) return false;

   $result = SQLExec("SHOW FIELDS FROM $table");  
   $res=array();
   while ($rec = mysql_fetch_array($result, MYSQL_ASSOC))
   {
      $res[] = $rec;
   }
   return $res;
}

function SQLGetIndexes($table) {
   global $db;
   if (!($db instanceof mysql)) return false;

   $result = SQLExec("SHOW INDEX FROM $table");  
   $res=array();
   while ($rec = mysql_fetch_array($result, MYSQL_ASSOC))
   {
      $res[] = $rec;
   }
   return $res;
}

function SQLPing() {
   global $db;
   if ($db instanceof mysql) {
      return $db->Ping();
   } else {
      return false;
   }
}

/**
 * Converts date format from YYYY/MM/DD to MM/DD/YYYY
 * @param mixed $source    Source date
 * @param mixed $delim     Source delimiter
 * @param mixed $dst_delim Destination delimiter
 * @return string
 */
function fromDBDate($source, $delim = '-', $dst_delim = '/')
{
   $tmp = explode($delim, $source);
   
   $str  = str_pad($tmp[1], 2, "0", STR_PAD_LEFT) . $dst_delim;
   $str .= str_pad($tmp[2], 2, "0", STR_PAD_LEFT) . $dst_delim;
   $str .= str_pad($tmp[0], 2, "0", STR_PAD_LEFT);

   return $str;
}

/**
 * Converts date format from MM/DD/YYYY to YYYY-MM-DD
 *
 * @param string $source    Source date to convert
 * @param string $delim     Source delimiter
 * @param string $dst_delim Destination delimiter
 * @return string
 */
function toDBDate($source, $delim = '/', $dst_delim = '-')
{
   $tmp = explode($delim, $source);
   
   $str  = str_pad($tmp[2], 2, "0", STR_PAD_LEFT) . $dst_delim;
   $str .= str_pad($tmp[0], 2, "0", STR_PAD_LEFT) . $dst_delim;
   $str .= str_pad($tmp[1], 2, "0", STR_PAD_LEFT);
   
   return $str;
}

