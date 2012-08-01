<?
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
 class mysql {
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
// --------------------------------------------------------------------   
/**
* MySQL constructor
*
* used to create mysql database object and connect to database
*
* @param string $host mysql host
* @param string $port mysql port
* @param string $user mysql user
* @param string $password mysql password
* @param string $database mysql database name
* @access public
*/
  function mysql($host, $port, $user, $password, $database) {
   $this->host=$host;
   $this->port=$port;
   $this->user=$user;
   $this->password=$password;
   $this->dbName=$database;
   $this->Connect();
  }   

// --------------------------------------------------------------------   
/**
* Connects to database
*
* @return bool connection result (1 - success, 0 - failed)
* @access private
*/
  function Connect() {
  // connects to database
   $this->dbh=@mysql_connect($this->host.":".$this->port, $this->user, $this->password);
   if (!@mysql_select_db($this->dbName, $this->dbh)) {
    $this->Error();
    return 0;
   } else {
    mysql_query("SET NAMES 'utf8';",$this->dbh);
    mysql_query("SET CHARACTER SET 'utf8';",$this->dbh);
    //mysql_query("SET SESSION collation_connection = 'utf8_general_ci';",$this->dbh);
    return 1;
   }
  }

// --------------------------------------------------------------------   
/**
* Execute SQL query
*
* @param string $query SQL query
* @return mixed execution result (0 - failed)
* @access public
*/
  function Exec($query) {
    $result=mysql_query($query, $this->dbh);
    if (!$result) {
     $this->Error($query);
     return 0;
    } else {
     return $result;
    }
  }

// --------------------------------------------------------------------   
/**
* Execute SQL SELECT query and return all records
*
* This method returns records as array of assosiated arrays (by field names)
*
* @param string $query SQL SELECT query
* @return array execution result
* @access public
*/
  function Select($query) {
   if ($result=mysql_query($query, $this->dbh)) {
    $res=array();
    for($i=0;$i<mysql_num_rows($result);$i++) {
     $rec=mysql_fetch_array($result, MYSQL_ASSOC);
     /*
     if (Is_Array($rec))
      foreach(array_keys($rec) as $k) {
       if (is_numeric($k)) UnSet($rec[$k]);
      }
      */
     $res[]=$rec;
    }
    return $res;
   } else {
    $this->Error($query);
   }
  }

// --------------------------------------------------------------------   
/**
* Execute SQL SELECT query and return first record
*
* This method returns record assosiated array (by field names)
*
* @param string $query SQL SELECT query
* @return array execution result
* @access public
*/
  function SelectOne($query) {
   if ($result=mysql_query($query, $this->dbh)) {
    $rec=mysql_fetch_array($result, MYSQL_ASSOC);
    /*
    if (Is_Array($rec))
     foreach(array_keys($rec) as $k) {
      if (is_numeric($k)) UnSet($rec[$k]);
     }
    */
    return $rec;
   } else {
    $this->Error($query);
   }
  }

// --------------------------------------------------------------------   
/**
* Execute SQL UPDATE query for one record
*
* Record is defined by assosiated array
*
* @param string $table table to update
* @param string $data record to update
* @param string $ndx index field (used in WHERE part of SQL request)
* @access public
*/
  function Update($table, $data, $ndx="ID") {
   $qry="UPDATE `$table` SET ";
   foreach($data as $field=>$value) {
    if (!is_Numeric($field)) {
     $qry.="`$field`='".$this->DBSafe1($value)."', ";
    }
   }
   $qry=substr($qry, 0, strlen($qry)-2);
   $qry.=" WHERE $ndx='".$data[$ndx]."'";
   if (!mysql_query($qry, $this->dbh)) {
    $this->Error($qry);
    return 0;
   }
   return 1;
  }

// --------------------------------------------------------------------   
/**
* Execute SQL INSERT query for one record
*
* Record is defined by assosiated array
*
* @param string $table table for new record
* @param string $data record to insert
* @return execution result (0 - if failed, INSERT ID - if succeed)
* @access public
*/
  function Insert($table, &$data) {
   $fields="";
   $values="";
   foreach($data as $field=>$value) {
    if (!is_Numeric($field)) {  
     $fields.="`$field`, ";
     $values.="'".$this->DBSafe1($value)."', ";
    }
   }
   $fields=substr($fields, 0, strlen($fields)-2);
   $values=substr($values, 0, strlen($values)-2);
   $qry="INSERT INTO `$table`($fields) VALUES($values)";
   if (!mysql_query($qry, $this->dbh)) {
    $this->error($qry);
    return 0;
   }
   return mysql_insert_id($this->dbh);
  }

// --------------------------------------------------------------------   
/**
* Disconnect from database
*
* @access public
*/
  function Disconnect() {
   mysql_close($this->dbh);
  }

// --------------------------------------------------------------------   
/**
* Used to strip "bad" symbols from sql query results
*
* @param string $str string to make "safe"
* @return string correct string
* @access public
*/
  function DbSafe($str) {
   $str=mysql_real_escape_string($str);
   /*
   $str=str_replace(chr(146), '\'', $str);
   $str=str_replace("\\", "\\\\", $str);
   $str=str_replace("\n", "\\n", $str);
   $str=str_replace("\r", "", $str);
   $str=str_replace("'", "\'", $str);
   */
   $str=str_replace("%", "\%", $str);
   return $str;
  }

  function DbSafe1($str) {
   $str=mysql_real_escape_string($str);
   /*
   $str=str_replace(chr(146), '\'', $str);
   $str=str_replace("\\", "\\\\", $str);
   $str=str_replace("\n", "\\n", $str);
   $str=str_replace("\r", "", $str);
   $str=str_replace("'", "\'", $str);
   */
   return $str;
  }


// --------------------------------------------------------------------   
/**
* MySQL database error handler
*
* @param string $query used query string
* @access private
*/
  function Error($query="") {
   $err=new error(mysql_errno().": ".mysql_error()."<br>$query", 1);
   return 1;
  }

// --------------------------------------------------------------------   
/**
* Returns mysql table definition string (CREATE TABLE ... ;)
*
* @param string $table table to get structure
* @return string table definition
* @access public
*/
 function get_mysql_def($table) {
  $result = mysql_query('SHOW CREATE TABLE '.$table, $this->dbh);
  if ($result) {
   $row = mysql_fetch_row($result);
   if ($row) {
    $row[1]=preg_replace('/DEFAULT CHARSET=(\w+)/', '', $row[1]);
    $row[1]=str_replace(' default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP', '', $row[1]);
    $row[1]=str_replace("\n", ' ', $row[1]);
    $strs = trim($row[1]);
    return (empty($strs))? '': 'DROP TABLE IF EXISTS '.$table.';'."\n".$row[1].';'."\n";
   } else {
    return '';
   }
  } else {
   return '';
  }
 }

// --------------------------------------------------------------------   
/**
* Returns mysql table content string (INSERT INTO ... ;)
*
* @param string $table table to get content
* @return string table content
* @access public
*/
  function get_mysql_content($table) {
     $content="";
     $result = mysql_query("SELECT * FROM $table", $this->dbh);
     while($row = mysql_fetch_row($result)) {
         $insert = "INSERT INTO $table VALUES (";
         for($j=0; $j<mysql_num_fields($result);$j++) {
            if(!isset($row[$j])) $insert .= "NULL,";
            else if($row[$j] != "") $insert .= "'".$this->DbSafe($row[$j])."',";
            else $insert .= "'',";
         }
         $insert = ereg_replace(",$","",$insert);
         $insert .= ");\n";
         $content .= $insert;
     }
     return $content;
  }
// --------------------------------------------------------------------   

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
 function SQLExec($query) {
  if (($query{0}=="#") || ($query=="")) return;
  global $db;
  return $db->Exec($query);
 }
// --------------------------------------------------------------------   
/**
* Used to strip "bad" symbols from sql query results
*
* @param string $str string to make "safe"
* @global object mysql database object
* @return string correct string
*/
 function DbSafe($in) {
  global $db;
  return $db->DbSafe($in);
 }

// --------------------------------------------------------------------   
/**
* Execute SQL SELECT query and return all records
*
* This function returns records as array of assosiated arrays (by field names)
*
* @param string $query SQL SELECT query
* @global object mysql database object
* @return array execution result
*/
 function SQLSelect($query) {
  global $db;
  return $db->Select($query);
 }

// --------------------------------------------------------------------   
/**
* Execute SQL SELECT query and return first record
*
* This function returns record assosiated array (by field names)
*
* @param string $query SQL SELECT query
* @global object mysql database object
* @return array execution result
*/
 function SQLSelectOne($query) {
  global $db;
  return $db->SelectOne($query);
 }

// --------------------------------------------------------------------   
/**
* Execute SQL INSERT query for one record
*
* Record is defined by assosiated array
*
* @param string $table table for new record
* @param string $record record to insert
* @global object mysql database object
* @return execution result (0 - if failed, INSERT ID - if succeed)
*/
 function SQLInsert($table, &$record) {
  global $db;
  return $db->Insert($table, &$record);
 }

// --------------------------------------------------------------------   
/**
* Execute SQL UPDATE query for one record
*
* Record is defined by assosiated array
*
* @param string $table table to update
* @param string $record record to update
* @global object mysql database object
*/
 function SQLUpdate($table, $record) {
  global $db;
  return $db->Update($table, $record);
 }

// --------------------------------------------------------------------   
/**
* Execute SQL UPDATE or INSERT query for one record
*
* If ID field is defined record will be updated else it will be inserted
*
* @param string $table table to update
* @param string $record record to update
* @global object mysql database object
*/
 function SQLUpdateInsert($table, &$record) {
  global $db;
  if (IsSet($record["ID"])) {
   return $db->Update($table, $record);
  } else {
   $record["ID"]=$db->Insert($table, $record);
   return $record["ID"];
  }
 }

// --------------------------------------------------------------------   
/**
* Execute SQL UPDATE or INSERT query for one record
*
* If ID field is defined record will be updated else it will be inserted
*
* @param string $table table to update
* @param string $record record to update
* @global object mysql database object
*/
 function SQLInsertUpdate($table, &$record) {
  return SQLUpdateInsert($table, $record);
 }

// --------------------------------------------------------------------   
/**
* Converts date format from YYYY/MM/DD to MM/DD/YYYY
*
* 
*
* @param string $source source date to convert
* @param string $delim source delimiter
* @param string $dst_delim destination delimiter
*/
 function fromDBDate($source, $delim='-', $dst_delim='/') {
  $tmp=explode($delim, $source);
  for($i=0;$i<count($tmp);$i++) {
   if ($tmp[$i]<10) {
    $tmp[$i]='0'.(int)$tmp[$i];
   }
  }
  return ($tmp[1]).$dst_delim.($tmp[2]).$dst_delim.($tmp[0]);
 }

// --------------------------------------------------------------------   
/**
* Converts date format from MM/DD/YYYY to YYYY-MM-DD
*
* 
*
* @param string $source source date to convert
* @param string $delim source delimiter
* @param string $dst_delim destination delimiter
*/
 function toDBDate($source, $delim='/', $dst_delim='-') {
  $tmp=explode($delim, $source);
  for($i=0;$i<count($tmp);$i++) {
   if ($tmp[$i]<10) {
    $tmp[$i]='0'.(int)$tmp[$i];
   }
  }
  return ($tmp[2]).$dst_delim.($tmp[0]).$dst_delim.($tmp[1]);
 }

?>
