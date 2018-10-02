<?php //$Id:$
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
 * Used to strip "bad" symbols from sql query results
 * @param mixed $in String to make "safe"
 * @global object mysql database object
 * @return string
 */
function DbSafe1($in)
{
   global $db;
   if ($db instanceof mysql) {
      return $db->DbSafe1($in);
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
 * Truncate table in mysql
 *
 * @param string $table    Table name
 * @return mixed execution result (0 - failed)
 */
function SQLTruncateTable($table) {
   return SQLExec("TRUNCATE TABLE `".$table."`");
}

/**
 * Drop table in mysql
 *
 * @param string $table    Table name
 * @return mixed execution result (0 - failed)
 */
function SQLDropTable($table) {
   return SQLExec("DROP TABLE IF EXISTS `".$table."`");
}

/**
* Title
*
* Description
*
* @access public
*/
function SQLGetFields($table) {
   return SQLSelect("SHOW FIELDS FROM `$table`");
}

function SQLGetIndexes($table) {
   return SQLSelect("SHOW INDEX FROM `$table`");
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
