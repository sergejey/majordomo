<?php //$Id:$
// --------------------------------------------------------------------
// DATABASE FUNCTIONS
// easy database manipulation
// --------------------------------------------------------------------

/**
 * Execute SQL query
 *
 * @param string $query SQL query
 * @return mixed execution result (0 - failed)
 * @global object mysql database object
 */
if (!function_exists('SQLExec')) {
    function SQLExec($query, $ignore_errors = false)
    {
        if (($query[0] == "#") || ($query == "")) return;
        if (preg_match('/^ALTER TABLE/', $query)) {
            global $alter_executed;
            if (isset($alter_executed[$query])) return false;
            $alter_executed[$query] = 1;
        }
        global $db;
        return $db->Exec($query, $ignore_errors);
    }
}
/**
 * Used to strip "bad" symbols from sql query results
 * @param mixed $in String to make "safe"
 * @return string
 * @global object mysql database object
 */
if (!function_exists('DbSafe')) {
    function DbSafe($in)
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->DbSafe($in);
        } else {
            return false;
        }
    }
}
/**
 * Used to strip "bad" symbols from sql query results
 * @param mixed $in String to make "safe"
 * @return string
 * @global object mysql database object
 */
if (!function_exists('DbSafe1')) {
    function DbSafe1($in)
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->DbSafe1($in);
        } else {
            return false;
        }
    }
}
/**
 * Execute SQL SELECT query and return all records
 *
 * This function returns records as array of assosiated arrays (by field names)
 *
 * @param string $query SQL SELECT query
 * @return array execution result
 * @global object mysql database object
 */
if (!function_exists('SQLSelect')) {
    function SQLSelect($query)
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->Select($query);
        } else {
            return false;
        }
    }
}
/**
 * Execute SQL SELECT query and return first record
 *
 * This function returns record assosiated array (by field names)
 *
 * @param string $query SQL SELECT query
 * @return array execution result
 * @global object mysql database object
 */
if (!function_exists('SQLSelectOne')) {
    function SQLSelectOne($query)
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->SelectOne($query);
        } else {
            return false;
        }
    }
}
/**
 * Execute SQL INSERT query for one record
 *
 * Record is defined by assosiated array
 *
 * @param string $table Table for new record
 * @param array $record Record to insert
 * @return int Execution result (0 - if failed, INSERT ID - if succeed)
 * @global object Mysql database object
 */
if (!function_exists('SQLInsert')) {
    function SQLInsert($table, &$record)
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->Insert($table, $record);
        } else {
            return false;
        }
    }
}
/**
 * Execute SQL UPDATE query for one record
 * @param mixed $table Table to update
 * @param mixed $record Record to update (assosiated array)
 * @param mixed $ndx Update by this key (default ID)
 * @return int
 */
if (!function_exists('SQLUpdate')) {
    function SQLUpdate($table, $record, $ndx = 'ID')
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->Update($table, $record, $ndx);
        } else {
            return false;
        }
    }
}
/**
 * Execute SQL UPDATE or INSERT query for one record
 *
 * If ID field is defined record will be updated else it will be inserted
 *
 * @param string $table Table to update
 * @param array $record Record to update
 * @param mixed $ndx Update or insert by this key (default ID)
 * @return int
 * @global object mysql database object
 */
if (!function_exists('SQLUpdateInsert')) {
    function SQLUpdateInsert($table, &$record, $ndx = 'ID')
    {
        global $db;

        if (isset($record[$ndx])) {
            if ($db instanceof mysql) {
                return $db->Update($table, $record, $ndx);
            } else {
                return false;
            }
        } else {
            if ($db instanceof mysql) {
                $record[$ndx] = $db->Insert($table, $record);
            } else {
                return false;
            }
            return $record[$ndx];
        }
    }
}
/**
 * Alias for SQLUpdateInsert
 * Execute SQL UPDATE or INSERT query for one record
 *
 * If ID field is defined record will be updated else it will be inserted
 *
 * @param string $table Table to update
 * @param array $record Record to update
 * @param mixed $ndx Update or insert by this key (default ID)
 * @return int
 * @global object mysql database object
 */
if (!function_exists('SQLInsertUpdate')) {
    function SQLInsertUpdate($table, &$record, $ndx = 'ID')
    {
        return SQLUpdateInsert($table, $record, $ndx);
    }
}
/**
 * Truncate table in mysql
 *
 * @param string $table Table name
 * @return mixed execution result (0 - failed)
 */
if (!function_exists('SQLTruncateTable')) {
    function SQLTruncateTable($table)
    {
        return SQLExec("TRUNCATE TABLE `" . $table . "`");
    }
}
/**
 * Drop table in mysql
 *
 * @param string $table Table name
 * @return mixed execution result (0 - failed)
 */
if (!function_exists('SQLDropTable')) {
    function SQLDropTable($table)
    {
        return SQLExec("DROP TABLE IF EXISTS `" . $table . "`");
    }
}


if (!function_exists('SQLTableExists')) {
    function SQLTableExists($table)
    {
        $data = SQLSelect("SHOW TABLES LIKE '" . DBSafe($table) . "'");
        if (isset($data[0])) return true;
        return false;
    }
}

function SQLMakeDBDump($dump_file, $ignore_tables = 0)
{
    $dir = dirname($dump_file);
    if (!file_exists($dir)) mkdir($dir, 0777, true);
    if (defined('PATH_TO_MYSQLDUMP')) {
        $mysqlDumpPath = PATH_TO_MYSQLDUMP;
    } else {
        if (IsWindowsOS())
            $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
        else
            $mysqlDumpPath = "/usr/bin/mysqldump";
    }
    $mysqlDumpParam = " -h " . DB_HOST . " --user=\"" . DB_USER . "\" --password=\"" . DB_PASSWORD . "\"";
    $mysqlDumpParam .= " --no-create-db --add-drop-table " . DB_NAME;
    if (is_array($ignore_tables)) {
        foreach ($ignore_tables as $table) {
            $mysqlDumpParam .= " --ignore-table=" . DB_NAME . "." . $table;
        }
    }
    exec($mysqlDumpPath . $mysqlDumpParam . "> " . $dump_file . '.tmp', $output);
    if (file_exists($dump_file . '.tmp') && filesize($dump_file . '.tmp') > 0) {
        rename($dump_file . '.tmp', $dump_file);
        debmes('DB Backup to ' . $dump_file . ' OK.', 'db_backup');
        return true;
    } else {
        debmes('Error saving DB Backup to ' . $dump_file . ': ' . implode("\n", $output), 'db_backup');
        return false;
    }
}

function SQLMakeTableDump($dump_file, $table_name)
{
    $dir = dirname($dump_file);
    if (!file_exists($dir)) mkdir($dir, 0777, true);
    if (defined('PATH_TO_MYSQLDUMP')) {
        $mysqlDumpPath = PATH_TO_MYSQLDUMP;
    } else {
        if (IsWindowsOS())
            $mysqlDumpPath = SERVER_ROOT . "/server/mysql/bin/mysqldump";
        else
            $mysqlDumpPath = "/usr/bin/mysqldump";
    }
    $mysqlDumpParam = " -h " . DB_HOST . " --user=\"" . DB_USER . "\" --password=\"" . DB_PASSWORD . "\"";
    $mysqlDumpParam .= " " . DB_NAME;
    $mysqlDumpParam .= " " . $table_name;
    exec($mysqlDumpPath . $mysqlDumpParam . "> " . $dump_file . '.tmp', $output);
    if (file_exists($dump_file . '.tmp') && filesize($dump_file . '.tmp') > 0) {
        rename($dump_file . '.tmp', $dump_file);
        debmes('Table ' . $table_name . ' backup to ' . $dump_file . ' OK.', 'db_backup');
        return true;
    } else {
        debmes('Error saving table ' . $table_name . ' backup to ' . $dump_file . ': ' . implode("\n", $output), 'db_backup');
        return false;
    }
}

function SQLRestoreDBDump($dump_file)
{
    if (IsWindowsOS())
        $mysqlCmdPath = SERVER_ROOT . "/server/mysql/bin/mysql";
    else
        $mysqlCmdPath = "mysql";

    $data = LoadFile($dump_file);
    if (is_integer(strpos($data, '/*M!999999\- enable the sandbox mode */'))) {
        $data_new = str_replace('/*M!999999\- enable the sandbox mode */', '', $data);
        $data_new = str_replace('COLLATE=utf8mb3_uca1400_ai_ci', 'COLLATE=utf8mb3_general_ci', $data_new);
        $dump_file = $dump_file . '.updated';
        SaveFile($dump_file, $data_new);
    }

    $mysqlCmdParam = " -h " . DB_HOST . " --user=\"" . DB_USER . "\" --password=\"" . DB_PASSWORD . "\"";
    $mysqlCmdParam .= " " . DB_NAME . " <" . $dump_file;
    $cmd = $mysqlCmdPath . $mysqlCmdParam;
    $result = exec($cmd, $output);
    if ($result !== false) {
        debmes("DB restored from " . $dump_file . " OK.", 'db_backup');
        return true;
    } else {
        debmes('Error restoring ' . $dump_file . ': ' . implode("\n", $output), 'db_backup');
        return false;
    }
}


/**
 * Title
 *
 * Description
 *
 * @access public
 */
if (!function_exists('SQLGetFields')) {
    function SQLGetFields($table)
    {
        return SQLSelect("SHOW FIELDS FROM `$table`");
    }
}
if (!function_exists('SQLGetIndexes')) {
    function SQLGetIndexes($table)
    {
        return SQLSelect("SHOW INDEX FROM `$table`");
    }
}

if (!function_exists('SQLPing')) {
    function SQLPing()
    {
        global $db;
        if ($db instanceof mysql) {
            return $db->Ping();
        } else {
            return false;
        }
    }
}
/**
 * Converts date format from YYYY/MM/DD to MM/DD/YYYY
 * @param mixed $source Source date
 * @param mixed $delim Source delimiter
 * @param mixed $dst_delim Destination delimiter
 * @return string
 */
if (!function_exists('fromDBDate')) {
    function fromDBDate($source, $delim = '-', $dst_delim = '/')
    {
        $tmp = explode($delim, $source);

        $str = str_pad($tmp[1], 2, "0", STR_PAD_LEFT) . $dst_delim;
        $str .= str_pad($tmp[2], 2, "0", STR_PAD_LEFT) . $dst_delim;
        $str .= str_pad($tmp[0], 2, "0", STR_PAD_LEFT);

        return $str;
    }
}
/**
 * Converts date format from MM/DD/YYYY to YYYY-MM-DD
 *
 * @param string $source Source date to convert
 * @param string $delim Source delimiter
 * @param string $dst_delim Destination delimiter
 * @return string
 */
if (!function_exists('toDBDate')) {
    function toDBDate($source, $delim = '/', $dst_delim = '-')
    {
        $tmp = explode($delim, $source);

        $str = str_pad($tmp[2], 2, "0", STR_PAD_LEFT) . $dst_delim;
        $str .= str_pad($tmp[0], 2, "0", STR_PAD_LEFT) . $dst_delim;
        $str .= str_pad($tmp[1], 2, "0", STR_PAD_LEFT);

        return $str;
    }
}
