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


   public $connected;

   
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

      $this->Connect();
   }

   /**
    * Connects to database
    * @access private
    * @return int connection result (1 - success, 0 - failed)
    */
   public function Connect()
   {
      // connects to database

      /*if (preg_match('/firefox/is',$_SERVER['HTTP_USER_AGENT'])) {
         $this->host='127.0.0.3';
      }
      */

      if ($this->port) {
       $this->dbh = @mysqli_connect(''.$this->host . ":" . $this->port, $this->user, $this->password);
      } else {
       $this->dbh = @mysqli_connect(''.$this->host , $this->user, $this->password);
      }

      $this->connected = false;      
      
      if (!$this->dbh) {
         $err_no = mysqli_connect_errno();
         $err_details = mysqli_connect_error();
         Define('NO_DATABASE_CONNECTION',1);
         registerError('sqlconn', $err_no . ": " . $err_details);
         //new custom_error($err_no . ": " . $err_details, 1);
         return 0;
      }
      $db_select = mysqli_select_db($this->dbh, $this->dbName);
      if (!$db_select) {
         Define('NO_DATABASE_CONNECTION',1);
         $this->Error("Selecting db: ".$this->dbName, 0);
         return 0;
      } else
      {
         $this->connected = true;
         $this->latestTransaction=time();
         $this->Exec("SET NAMES 'utf8';");
         $this->Exec("SET CHARACTER SET 'utf8';");
         $this->Exec("set character_set_client='utf8';");
         $this->Exec("set character_set_results='utf8';");
         $this->Exec("set collation_connection='utf8_general_ci';");
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

      if (!$this->connected) return false;
      if ((time()-$this->latestTransaction)>$this->pingTimeout) {
       $this->Ping();
      }

      $this->latestTransaction=time(); 
      $result = mysqli_query($this->dbh, $query);
      
      if (!$result)
      {

      //$e = new Exception;
      //var_dump($e->getTraceAsString());


         $this->Error($query);
         
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
      if (!$this->connected) return false;
      $res = array();
      
      if ($result = $this->Exec($query))
      {
         while ($rec = mysqli_fetch_array($result, MYSQL_ASSOC))
         {
            $res[] = $rec;
         }
      }
      else
      {
         $this->Error($query);
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
      if (!$this->connected) return false;
      if ($result = $this->Exec($query))
      {
         $rec = mysqli_fetch_array($result, MYSQL_ASSOC);
         
         return $rec;
      }
      else
      {
         $this->Error($query);
      }
   }


   public function Ping()
   {
    $test_query = "SHOW TABLES FROM ".$this->dbName;
    $result = @mysqli_query($this->dbh, $test_query);
    $tblCnt = 0;
    if ($result) {
     while($tbl = mysqli_fetch_array($result)) {
      $tblCnt++;
     }
    }
    if ($tblCnt>0) {
     return true;
    } else {
     $this->Disconnect();
     $this->Connect();
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
      if (!$this->connected) return false;
      $qry = "UPDATE `$table` SET ";
      
      foreach ($data as $field => $value)
      {
         if (is_Numeric($field)) continue;
         if (!is_null($value)) {
          $qry .= "`$field`='" . $this->DBSafe1($value) . "', ";
         } else {
          $qry .= "`$field`=NULL, ";
         }
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
      if (!$this->connected) return false;
      $fields = "";
      $values = "";
      foreach ($data as $field => $value)
      {
         if (is_Numeric($field)) continue;
         $fields .= "`$field`, ";
         $values .= "'" . $this->DBSafe1($value) . "', ";
      }
      
      $fields = substr($fields, 0, strlen($fields) - 2);
      $values = substr($values, 0, strlen($values) - 2);
      
      $qry = "INSERT INTO `$table`($fields) VALUES($values)";
      
      if (!$this->Exec($qry))
      {
         $this->error($qry);
         return 0;
      }
      
      return mysqli_insert_id($this->dbh);
   }

   /**
    * Disconnect from database
    *
    * @access public
    * @return void
    */
   public function Disconnect()
   {
      if (!$this->connected) return false;
      mysqli_close($this->dbh);
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
      if (!$this->connected) return $str;
      $str = mysqli_real_escape_string($this->dbh, $str);
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
      if (is_array($str)) {
       $str=json_encode($str);
      }
      $str = mysqli_real_escape_string($this->dbh, (string)$str);
      return $str;
   }

   /**
    * MySQL database error handler
    *
    * @param string $query used query string
    * @access private
    * @return int
    */
   public function Error($query = "", $stop = 1)
   {
      $err_no = mysqli_errno($this->dbh);
      $err_details = mysqli_error($this->dbh);
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
      $result = mysqli_query('SHOW CREATE TABLE ' . $table, $this->dbh);
      
      if ($result)
      {
         $row = mysqli_fetch_row($result);
         
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
      $result  = mysqli_query("SELECT * FROM $table", $this->dbh);
      
      while ($row = mysqli_fetch_row($result))
      {
         $insert = "INSERT INTO $table VALUES (";
         
         $filedsNum = mysqli_num_fields($result);

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
   if (is_object($db)) {
      return $db->DbSafe($in);
   } else {
      return $in;
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
   if (is_object($db)) {
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
   if (is_object($db)) {
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
   if (is_object($db)) {
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
   if (is_object($db)) {
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
      if (is_object($db)) {
         return $db->Update($table, $record, $ndx);
      } else {
         return false;
      }
   }
   else
   {
      if (is_object($db)) {
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
    if (!is_object($db) || !$db->connected) {
       return false;
    }
  $result = SQLExec("SHOW FIELDS FROM $table");  
  $res=array();
  while ($rec = mysqli_fetch_array($result, MYSQL_ASSOC))
   {
   $res[] = $rec;
  }
  return $res;
 }

 function SQLGetIndexes($table) {
  $result = SQLExec("SHOW INDEX FROM $table");  
  $res=array();
  while ($rec = mysqli_fetch_array($result, MYSQL_ASSOC))
   {
   $res[] = $rec;
  }
  return $res;
 }

 function SQLPing() {
  global $db;
  if (is_object($db)) {
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

