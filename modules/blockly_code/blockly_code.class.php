<?php
/**
 * Blockly_code
 *
 * Blockly_code
 *
 * @package project
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @version 0.1 (wizard, 14:09:29 [Sep 01, 2014])
 */
//
//
class blockly_code extends module
{
    /**
     * blockly_code
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "blockly_code";
        $this->title = "Blockly code";
        $this->module_category = "<#LANG_SECTION_SYSTEM#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 0)
    {
        $p = array();
        if (IsSet($this->id)) {
            $p["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['TAB'] = $this->tab;
        if ($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {
        if (!$this->code_field) {
            $this->code_field = 'code';
        }

        if ($this->type) {
            $out['TYPE'] = $this->type;
        } else {
            $out['TYPE'] = 'php';
        }
		
		if ($this->autofocus) {
			$out['AUTOFOCUS'] = $this->autofocus;
        }

        $out['CODE_FIELD'] = $this->code_field;
        $out['ONLYCODE'] = $this->onlycode;
		 //Code editor settings
        $settings = array(
            'AUTOCLOSEQUOTES'=>1,
            'WRAPLINES'=>0,
            'SHOWERROR'=>0,
            'UPTOLINE'=>0,
            'THEME'=>'codemirror',
            'MIXLINE'=>20,
            'SHOWLINE'=>20,
            'TURNONSETTINGS'=>0,
            'AUTOCOMPLETE'=>0,
            'AUTOSAVE'=>0);

        foreach($settings as $setting=>$default) {
            if (isset($this->{strtolower($setting)})) {
                $out['SETTINGS_CODEEDITOR_'.$setting] = $this->{strtolower($setting)};
            } elseif (defined('SETTINGS_CODEEDITOR_'.$setting)) {
                $out['SETTINGS_CODEEDITOR_'.$setting] = constant('SETTINGS_CODEEDITOR_'.$setting);
            } else {
                $out['SETTINGS_CODEEDITOR_'.$setting] = $default;
            }
        }


        /*
		(defined('SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES')) ? $out['SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES'] = SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES : $out['SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES'] = 1;
		(defined('SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES')) ? $out['SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES'] = SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES : $out['SETTINGS_CODEEDITOR_AUTOCLOSEQUOTES'] = 1;
		(defined('SETTINGS_CODEEDITOR_WRAPLINES')) ? $out['SETTINGS_CODEEDITOR_WRAPLINES'] = SETTINGS_CODEEDITOR_WRAPLINES : $out['SETTINGS_CODEEDITOR_WRAPLINES'] = 0;
		(defined('SETTINGS_CODEEDITOR_SHOWERROR')) ? $out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR : $out['SETTINGS_CODEEDITOR_SHOWERROR'] = 0;
		(defined('SETTINGS_CODEEDITOR_UPTOLINE')) ? $out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE : $out['SETTINGS_CODEEDITOR_UPTOLINE'] = 0;
		(defined('SETTINGS_CODEEDITOR_THEME')) ? $out['SETTINGS_CODEEDITOR_THEME'] = SETTINGS_CODEEDITOR_THEME : $out['SETTINGS_CODEEDITOR_THEME'] = 'codemirror';
		(defined('SETTINGS_CODEEDITOR_MIXLINE')) ? $out['SETTINGS_CODEEDITOR_MIXLINE'] = SETTINGS_CODEEDITOR_MIXLINE : $out['SETTINGS_CODEEDITOR_MIXLINE'] = '20';
		(defined('SETTINGS_CODEEDITOR_SHOWLINE')) ? $out['SETTINGS_CODEEDITOR_SHOWLINE'] = SETTINGS_CODEEDITOR_SHOWLINE : $out['SETTINGS_CODEEDITOR_SHOWLINE'] = '20';
		(defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) ? $out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS : $out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = '0';
		(defined('SETTINGS_CODEEDITOR_AUTOCOMPLETE')) ? $out['SETTINGS_CODEEDITOR_AUTOCOMPLETE'] = SETTINGS_CODEEDITOR_AUTOCOMPLETE : $out['SETTINGS_CODEEDITOR_AUTOCOMPLETE'] = '0';
		(defined('SETTINGS_CODEEDITOR_AUTOSAVE')) ? $out['SETTINGS_CODEEDITOR_AUTOSAVE'] = SETTINGS_CODEEDITOR_AUTOSAVE : $out['SETTINGS_CODEEDITOR_AUTOSAVE'] = '0';
        */
		if(defined('SETTINGS_CODEEDITOR_AUTOCOMPLETE')) {
			$typeAutocomplete = SETTINGS_CODEEDITOR_AUTOCOMPLETE_TYPE;
			$wordsMJDM = "'SQLSelect(', 'SQLSelectOne(', 'SQLExec(', 'SQLInsert(', 'SQLUpdate(', ' gg(', 'getGlobal(', 'sg(', 'setGlobal(', 'setTimeOut(', 'DebMes(', 'clearTimeOut(', 'say(', 'sayTo(', 'sayReply(', 'processCommand(', ' processLine(', 'getRandomLine(', 'playSound(', 'playMedia(', 'runScript(', 'runScriptSafe(', 'isOnLine(', 'addClass(', 'addClassObject(', 'description', 'callMethod(', 'getObjectsByClass(', ' getObjectsByProperty(', 'getHistoryMin(', 'getHistoryMax(', 'getHistoryCount(', 'getHistorySum(', 'getHistoryAvg(', 'getHistory(', ' getHistoryValue(', 'timeConvert(', 'timeNow(', 'isWeekEnd()', 'isWeekDay()', 'timeIs(', 'timeBefore(', 'timeAfter(', 'timeBetween(', 'recognizeTime(', ' addPattern(', 'context_activate(', 'AddScheduledJob(', 'ClearScheduledJob(', 'timeOutExists(', 'registerEvent(', 'registeredEventTime(', ' SendMail(', 'SendMail_html(', '->setProperty(', '->getProperty(', ";
			$wordsPHP = "'abstract', 'and', 'array', 'as', 'break', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'do', 'else', 'elseif', ' enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'extends', 'final', 'for', 'foreach', ' function', 'global', 'goto', 'if', 'implements', 'interface', 'instanceof', 'namespace', 'new', 'or', 'private', ' protected', 'public', 'static', 'switch', 'throw', 'trait', 'try', 'use', 'var', 'while', 'xor', 'die', 'echo', 'empty', 'exit', 'eval', ' include', 'include_once', 'isset', 'list', 'require', 'require_once', 'return', 'print', 'unset', '__halt_compiler', ' self', 'static', 'parent', 'yield', 'insteadof', 'finally', 'true', 'false', 'null', 'TRUE', 'FALSE', 'NULL', '__CLASS__', '__DIR__', '__FILE__', '__LINE__', '__METHOD__', '__FUNCTION__', ' __NAMESPACE__', '__TRAIT__', 'func_num_args', 'func_get_arg', 'func_get_args', 'strlen', 'strcmp', 'strncmp', 'strcasecmp', 'strncasecmp', 'each', ' error_reporting', 'define', 'defined', 'trigger_error', 'user_error', 'set_error_handler', 'restore_error_handler', 'get_declared_classes', ' get_loaded_extensions', 'extension_loaded', 'get_extension_funcs', 'debug_backtrace', 'constant', 'bin2hex', 'hex2bin', 'sleep', 'usleep', 'time', ' mktime', 'gmmktime', 'strftime', 'gmstrftime', 'strtotime', 'date', 'gmdate', 'getdate', 'localtime', 'checkdate', 'flush', 'wordwrap', 'htmlspecialchars', ' htmlentities', 'html_entity_decode', 'md5', 'md5_file', 'crc32', 'getimagesize', 'image_type_to_mime_type', 'phpinfo', 'phpversion', 'phpcredits', ' strnatcmp', 'strnatcasecmp', 'substr_count', 'strspn', 'strcspn', 'strtok', 'strtoupper', 'strtolower', 'strpos', 'strrpos', 'strrev', 'hebrev', 'hebrevc', ' nl2br', 'basename', 'dirname', 'pathinfo', 'stripslashes', 'stripcslashes', 'strstr', 'stristr', 'strrchr', 'str_shuffle', 'str_word_count', 'strcoll', ' substr', 'substr_replace', 'quotemeta', 'ucfirst', 'ucwords', 'strtr', 'addslashes', 'addcslashes', 'rtrim', 'str_replace', 'str_repeat', 'count_chars', ' chunk_split', 'trim', 'ltrim', 'strip_tags', 'similar_text', 'explode', 'implode', 'setlocale', 'localeconv', 'parse_str', 'str_pad', 'chop', 'strchr', 'sprintf', ' printf', 'vprintf', 'vsprintf', 'sscanf', 'fscanf', 'parse_url', 'urlencode', 'urldecode', 'rawurlencode', 'rawurldecode', 'readlink', 'linkinfo', 'link', 'unlink', ' exec', 'system', 'escapeshellcmd', 'escapeshellarg', 'passthru', 'shell_exec', 'proc_open', 'proc_close', 'rand', 'srand', 'getrandmax', 'mt_rand', ' mt_srand', 'mt_getrandmax', 'base64_decode', 'base64_encode', 'abs', 'ceil', 'floor', 'round', 'is_finite', 'is_nan', 'is_infinite', 'bindec', 'hexdec', ' octdec', 'decbin', 'decoct', 'dechex', 'base_convert', 'number_format', 'fmod', 'ip2long', 'long2ip', 'getenv', 'putenv', 'getopt', 'microtime', 'gettimeofday', ' getrusage', 'uniqid', 'quoted_printable_decode', 'set_time_limit', 'get_cfg_var', 'magic_quotes_runtime', 'set_magic_quotes_runtime', ' get_magic_quotes_gpc', 'get_magic_quotes_runtime', 'import_request_variables', 'error_log', 'serialize', 'unserialize', 'memory_get_usage', 'var_dump(', 'var_export', 'debug_zval_dump', 'print_r', 'highlight_file', 'show_source', 'highlight_string', 'ini_get', 'ini_get_all', 'ini_set', ' ini_alter', 'ini_restore', 'get_include_path', 'set_include_path', 'restore_include_path', 'setcookie', 'header', 'headers_sent', ' connection_aborted', 'connection_status', 'ignore_user_abort', 'parse_ini_file', 'is_uploaded_file', 'move_uploaded_file', 'intval', ' floatval', 'doubleval', 'strval', 'gettype', 'settype', 'is_null', 'is_resource', 'is_bool', 'is_long', 'is_float', 'is_int', 'is_integer', 'is_double', ' is_real', 'is_numeric', 'is_string', 'is_array', 'is_object', 'is_scalar', 'ereg', 'ereg_replace', 'eregi', 'eregi_replace', 'split', 'spliti', 'join', ' sql_regcase', 'dl', 'pclose', 'popen', 'readfile', 'rewind', 'rmdir', 'umask', 'fclose', 'feof', 'fgetc', 'fgets', 'fgetss', 'fread', 'fopen', 'fpassthru', 'ftruncate', ' fstat', 'fseek', 'ftell', 'fflush', 'fwrite', 'fputs', 'mkdir', 'rename', 'copy', 'tempnam', 'tmpfile', 'file', 'file_get_contents', 'file_put_contents', ' stream_select', 'stream_context_create', 'stream_context_set_params', 'stream_context_set_option', 'stream_context_get_options', ' stream_filter_prepend', 'stream_filter_append', 'fgetcsv', 'flock', 'get_meta_tags', 'stream_set_write_buffer', 'set_file_buffer', ' set_socket_blocking', 'stream_set_blocking', 'socket_set_blocking', 'stream_get_meta_data', 'stream_register_wrapper', ' stream_wrapper_register', 'stream_set_timeout', 'socket_set_timeout', 'socket_get_status', 'realpath', 'fnmatch', 'fsockopen', ' pfsockopen', 'pack', 'unpack', 'get_browser', 'crypt', 'opendir', 'closedir', 'chdir', 'getcwd', 'rewinddir', 'readdir', 'dir', 'glob', 'fileatime', ' filectime', 'filegroup', 'fileinode', 'filemtime', 'fileowner', 'fileperms', 'filesize', 'filetype', 'file_exists', 'is_writable', 'is_writeable', ' is_readable', 'is_executable', 'is_file', 'is_dir', 'is_link', 'stat', 'lstat', 'chown', 'touch', 'clearstatcache', 'mail', 'ob_start', 'ob_flush', 'ob_clean', ' ob_end_flush', 'ob_end_clean', 'ob_get_flush', 'ob_get_clean', 'ob_get_length', 'ob_get_level', 'ob_get_status', 'ob_get_contents', ' ob_implicit_flush', 'ob_list_handlers', 'ksort', 'krsort', 'natsort', 'natcasesort', 'asort', 'arsort', 'sort', 'rsort', 'usort', 'uasort', 'uksort', ' shuffle', 'array_walk', 'count', 'end', 'prev', 'next', 'reset', 'current', 'key', 'min', 'max', 'in_array', 'array_search', 'extract', 'compact', 'array_fill', ' range', 'array_multisort', 'array_push', 'array_pop', 'array_shift', 'array_unshift', 'array_splice', 'array_slice', 'array_merge', ' array_merge_recursive', 'array_keys', 'array_values', 'array_count_values', 'array_reverse', 'array_reduce', 'array_pad', 'array_flip', ' array_change_key_case', 'array_rand', 'array_unique', 'array_intersect', 'array_intersect_assoc', 'array_diff', 'array_diff_assoc', ' array_sum', 'array_filter', 'array_map', 'array_chunk', 'array_key_exists', 'array_intersect_key', 'array_combine', 'array_column', ' pos', 'sizeof', 'key_exists', 'assert', 'assert_options', 'version_compare', 'ftok', 'str_rot13', 'aggregate', 'session_name', ' session_module_name', 'session_save_path', 'session_id', 'session_regenerate_id', 'session_decode', 'session_register', ' session_unregister', 'session_is_registered', 'session_encode', 'session_start', 'session_destroy', 'session_unset', ' session_set_save_handler', 'session_cache_limiter', 'session_cache_expire', 'session_set_cookie_params', ' session_get_cookie_params', 'session_write_close', 'preg_match', 'preg_match_all', 'preg_replace', 'preg_replace_callback', ' preg_split', 'preg_quote', 'preg_grep', 'overload', 'ctype_alnum', 'ctype_alpha', 'ctype_cntrl', 'ctype_digit', 'ctype_lower', 'ctype_graph', ' ctype_print', 'ctype_punct', 'ctype_space', 'ctype_upper', 'ctype_xdigit', 'virtual', 'apache_request_headers', 'apache_note', 'apache_lookup_uri', ' apache_child_terminate', 'apache_setenv', 'apache_response_headers', 'apache_get_version', 'getallheaders', 'mysql_connect', 'mysql_pconnect', ' mysql_close', 'mysql_select_db', 'mysql_create_db', 'mysql_drop_db', 'mysql_query', 'mysql_unbuffered_query', 'mysql_db_query', 'mysql_list_dbs', ' mysql_list_tables', 'mysql_list_fields', 'mysql_list_processes', 'mysql_error', 'mysql_errno', 'mysql_affected_rows', 'mysql_insert_id', ' mysql_result', 'mysql_num_rows', 'mysql_num_fields', 'mysql_fetch_row', 'mysql_fetch_array', 'mysql_fetch_assoc', 'mysql_fetch_object', ' mysql_data_seek', 'mysql_fetch_lengths', 'mysql_fetch_field', 'mysql_field_seek', 'mysql_free_result', 'mysql_field_name', ' mysql_field_table', 'mysql_field_len', 'mysql_field_type', 'mysql_field_flags', 'mysql_escape_string', 'mysql_real_escape_string', ' mysql_stat', 'mysql_thread_id', 'mysql_client_encoding', 'mysql_get_client_info', 'mysql_get_host_info', 'mysql_get_proto_info', ' mysql_get_server_info', 'mysql_info', 'mysql', 'mysql_fieldname', 'mysql_fieldtable', 'mysql_fieldlen', 'mysql_fieldtype', 'mysql_fieldflags', ' mysql_selectdb', 'mysql_createdb', 'mysql_dropdb', 'mysql_freeresult', 'mysql_numfields', 'mysql_numrows', 'mysql_listdbs', ' mysql_listtables', 'mysql_listfields', 'mysql_db_name', 'mysql_dbname', 'mysql_tablename', 'mysql_table_name', 'pg_connect', ' pg_pconnect', 'pg_close', 'pg_connection_status', 'pg_connection_busy', 'pg_connection_reset', 'pg_host', 'pg_dbname', 'pg_port', 'pg_tty', ' pg_options', 'pg_ping', 'pg_query', 'pg_send_query', 'pg_cancel_query', 'pg_fetch_result', 'pg_fetch_row', 'pg_fetch_assoc', 'pg_fetch_array', ' pg_fetch_object', 'pg_fetch_all', 'pg_affected_rows', 'pg_get_result', 'pg_result_seek', 'pg_result_status', 'pg_free_result', 'pg_last_oid', ' pg_num_rows', 'pg_num_fields', 'pg_field_name', 'pg_field_num', 'pg_field_size', 'pg_field_type', 'pg_field_prtlen', 'pg_field_is_null', ' pg_get_notify', 'pg_get_pid', 'pg_result_error', 'pg_last_error', 'pg_last_notice', 'pg_put_line', 'pg_end_copy', 'pg_copy_to', 'pg_copy_from', ' pg_trace', 'pg_untrace', 'pg_lo_create', 'pg_lo_unlink', 'pg_lo_open', 'pg_lo_close', 'pg_lo_read', 'pg_lo_write', 'pg_lo_read_all', 'pg_lo_import', ' pg_lo_export', 'pg_lo_seek', 'pg_lo_tell', 'pg_escape_string', 'pg_escape_bytea', 'pg_unescape_bytea', 'pg_client_encoding', ' pg_set_client_encoding', 'pg_meta_data', 'pg_convert', 'pg_insert', 'pg_update', 'pg_delete', 'pg_select', 'pg_exec', 'pg_getlastoid', ' pg_cmdtuples', 'pg_errormessage', 'pg_numrows', 'pg_numfields', 'pg_fieldname', 'pg_fieldsize', 'pg_fieldtype', 'pg_fieldnum', 'pg_fieldprtlen', ' pg_fieldisnull', 'pg_freeresult', 'pg_result', 'pg_loreadall', 'pg_locreate', 'pg_lounlink', 'pg_loopen', 'pg_loclose', 'pg_loread', 'pg_lowrite', ' pg_loimport', 'pg_loexport', 'http_response_code', 'get_declared_traits', 'getimagesizefromstring', 'socket_import_stream', ' stream_set_chunk_size', 'trait_exists', 'header_register_callback', 'class_uses', 'session_status', 'session_register_shutdown', 'echo', 'print', ' global', 'static', 'exit', 'array', 'empty', 'eval', 'isset', 'unset', 'die', 'include', 'require', 'include_once', 'require_once', 'json_decode', 'json_encode', ' json_last_error', 'json_last_error_msg', 'curl_close', 'curl_copy_handle', 'curl_errno', 'curl_error', 'curl_escape', 'curl_exec', ' curl_file_create', 'curl_getinfo', 'curl_init', 'curl_multi_add_handle', 'curl_multi_close', 'curl_multi_exec', 'curl_multi_getcontent', ' curl_multi_info_read', 'curl_multi_init', 'curl_multi_remove_handle', 'curl_multi_select', 'curl_multi_setopt', 'curl_multi_strerror', ' curl_pause', 'curl_reset', 'curl_setopt_array', 'curl_setopt', 'curl_share_close', 'curl_share_init', 'curl_share_setopt', 'curl_strerror', ' curl_unescape', 'curl_version', 'mysqli_affected_rows', 'mysqli_autocommit', 'mysqli_change_user', 'mysqli_character_set_name', ' mysqli_close', 'mysqli_commit', 'mysqli_connect_errno', 'mysqli_connect_error', 'mysqli_connect', 'mysqli_data_seek', 'mysqli_debug', ' mysqli_dump_debug_info', 'mysqli_errno', 'mysqli_error_list', 'mysqli_error', 'mysqli_fetch_all', 'mysqli_fetch_array', 'mysqli_fetch_assoc', ' mysqli_fetch_field_direct', 'mysqli_fetch_field', 'mysqli_fetch_fields', 'mysqli_fetch_lengths', 'mysqli_fetch_object', 'mysqli_fetch_row', 'mysqli_field_count', 'mysqli_field_seek', 'mysqli_field_tell', 'mysqli_free_result', 'mysqli_get_charset', 'mysqli_get_client_info', 'mysqli_get_client_stats', 'mysqli_get_client_version', 'mysqli_get_connection_stats', 'mysqli_get_host_info', 'mysqli_get_proto_info', 'mysqli_get_server_info', 'mysqli_get_server_version', 'mysqli_info', 'mysqli_init', 'mysqli_insert_id', 'mysqli_kill', 'mysqli_more_results', 'mysqli_multi_query', 'mysqli_next_result', 'mysqli_num_fields', 'mysqli_num_rows', 'mysqli_options', 'mysqli_ping', 'mysqli_prepare', 'mysqli_query', 'mysqli_real_connect', 'mysqli_real_escape_string', 'mysqli_real_query', 'mysqli_reap_async_query', 'mysqli_refresh', 'mysqli_rollback', 'mysqli_select_db', 'mysqli_set_charset', 'mysqli_set_local_infile_default', 'mysqli_set_local_infile_handler', 'mysqli_sqlstate', 'mysqli_ssl_set', 'mysqli_stat', 'mysqli_stmt_init', 'mysqli_store_result', 'mysqli_thread_id', 'mysqli_thread_safe', 'mysqli_use_result', 'mysqli_warning_count', ";
			if($typeAutocomplete == 'none') {
				$out['SETTINGS_CODEEDITOR_AUTOCOMPLETE'] = 0;
			} elseif($typeAutocomplete == 'php') {
				$out['CODEEDITOR_AUTOCOMPLETE_WORDS'] = $wordsPHP;
			} elseif($typeAutocomplete == 'phpmjdm') {
				$out['CODEEDITOR_AUTOCOMPLETE_WORDS'] = $wordsMJDM;
				$out['CODEEDITOR_AUTOCOMPLETE_WORDS'] .= $wordsPHP;
			} else {
				if($typeAutocomplete != 'user') $words .= $wordsMJDM;
				if($typeAutocomplete != 'mjdmuser' && $typeAutocomplete != 'user') $words .= $wordsPHP;
				
				$allPropObjScrMet = SQLExec('select distinct t.title from (SELECT o.title FROM `objects` o union SELECT p.title FROM properties p union SELECT  m.title FROM methods m union SELECT  s.title FROM scripts s) t order by t.title');
				foreach($allPropObjScrMet as $value) {
					$words .= "'".$value['title']."', ";
				}
				
				$allScripts = SQLExec('SELECT  distinct s.title FROM scripts s order by s.title');
				foreach($allScripts as $value) {
					$words .= "'runScript(\"".$value['title']."\");', ";
					$words .= "'runScriptSafe(\"".$value['title']."\");', ";
				}
				
				$objMeth = SQLExec("SELECT CONCAT( o.title, '.', m.title ) title FROM methods m JOIN objects o ON m.object_id = o.id ORDER BY 1");
				foreach($objMeth as $value) {
					$words .= "'callMethod(\"".$value['title']."\");', ";
					$words .= "'callMethodSafe(\"".$value['title']."\");', ";
				}
				
				$objMethProp = SQLExec("SELECT concat(o.title,'.',m.title) title FROM properties m join objects o on m.object_id = o.id order by 1");
				foreach($objMethProp as $value) {
					$words .= "'setGlobal(\"".$value['title']."\", val);', ";
					$words .= "'getGlobal(\"".$value['title']."\")', ";
					$words .= "'".$value['title']."', ";
				}
				
				$objThisObj = SQLExec("SELECT distinct m.title title FROM properties m order by 1");
				foreach($objThisObj as $value) {
					$words .= "'->setProperty(\"".$value['title']."\", value);', ";
					$words .= "'->getProperty(\"".$value['title']."\")', ";
				}
				
				$out['CODEEDITOR_AUTOCOMPLETE_WORDS'] = "' ', ".$words;
			}				
			
		}
		
        $rec = SQLSelectOne("SELECT * FROM blockly_code WHERE SYSTEM_NAME LIKE '" . DBSafe($this->system_name) . "'");
        $out['CODE_TYPE'] = (int)$rec['CODE_TYPE'];
        if (!$rec['ID'] && $this->owner->xml) {
            $rec['XML'] = $this->owner->xml;
        }
        if (preg_match_all('/<block type="(.+?)\_turnoff"(.+?)>/uis',$rec['XML'],$m)) {
            $total = count($m[0]);
            for($i=0;$i<$total;$i++) {
                $new_line = $m[0][$i];
                $closed_block = 0;
                if (preg_match('/\/>/',$new_line)) {
                    $closed_block = 1;
                }
                $new_line = str_replace('_turnOff','_switch',$new_line);
                $new_line .= "\n<field name=\"MODE\">OFF</field>";
                if ($closed_block) {
                    $new_line = str_replace('/>','>',$new_line);
                    $new_line.="\n</block>";
                }
                $rec['XML'] = str_replace($m[0][$i],$new_line,$rec['XML']);
            }
        }
        if (preg_match_all('/<block type="(.+?)\_turnOn"(.+?)>/uis',$rec['XML'],$m)) {
            $total = count($m[0]);
            for($i=0;$i<$total;$i++) {
                $new_line = $m[0][$i];
                $closed_block = 0;
                if (preg_match('/\/>/',$new_line)) {
                    $closed_block = 1;
                }
                $new_line = str_replace('_turnOn','_switch',$new_line);
                $new_line .= "\n<field name=\"MODE\">ON</field>";
                if ($closed_block) {
                    $new_line = str_replace('/>','>',$new_line);
                    $new_line.="\n</block>";
                }
                $rec['XML'] = str_replace($m[0][$i],$new_line,$rec['XML']);
            }
        }

        if (!$rec['ID'] && !$this->type) {
            $rec['CODE_TYPE'] = 0;
            $rec['CODE_TYPE_UNKNOWN'] = 1;
        }


        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $out['TYPE'] == 'php') {
            global $xml;
            global $code;

            global ${$this->code_field . "_code_type"};
            SQLExec("DELETE FROM blockly_code WHERE SYSTEM_NAME LIKE '" . DBSafe($this->system_name) . "'");
            $rec = array();
            $rec['XML'] = $xml;
            $rec['CODE'] = $code;
            $rec['UPDATED'] = date('Y-m-d H:i:s');
            $rec['SYSTEM_NAME'] = $this->system_name;
            if (isset(${$this->code_field . "_code_type"})) {
                $rec['CODE_TYPE'] = (int)${$this->code_field . "_code_type"};
            } else {
                $rec['CODE_TYPE'] = 0;
            }
            if (!$rec['CODE_TYPE']) {
                //$rec['XML']='';
            }
            $rec['ID'] = SQLInsert('blockly_code', $rec);

        }

        $rec['XML'] = preg_replace('/id="\?/', 'id="Q', $rec['XML']);
        $out['XML'] = $rec['XML'];
        //$out['XML']='';

        //dprint($rec);

        $out['CODE_TYPE'] = (int)$rec['CODE_TYPE'];


        $out['DEVICES'] = SQLSelect("SELECT ID,TITLE,TYPE,LINKED_OBJECT FROM devices WHERE TYPE IN ('relay','dimmer','button','thermostat') ORDER BY TITLE");

        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'blockly_code' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_blockly_code') {
                $this->search_blockly_code($out);
            }
            if ($this->view_mode == 'edit_blockly_code') {
                $this->edit_blockly_code($out, $this->id);
            }
            if ($this->view_mode == 'delete_blockly_code') {
                $this->delete_blockly_code($this->id);
                $this->redirect("?");
            }
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    /**
     * blockly_code search
     *
     * @access public
     */
    function search_blockly_code(&$out)
    {
        require(DIR_MODULES . $this->name . '/blockly_code_search.inc.php');
    }

    /**
     * blockly_code edit/add
     *
     * @access public
     */
    function edit_blockly_code(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/blockly_code_edit.inc.php');
    }

    /**
     * blockly_code delete record
     *
     * @access public
     */
    function delete_blockly_code($id)
    {
        $rec = SQLSelectOne("SELECT * FROM blockly_code WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM blockly_code WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        parent::install();
        SQLExec("UPDATE project_modules SET HIDDEN=1 WHERE NAME LIKE 'blockly_code'");
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLDropTable('blockly_code');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        blockly_code - Blockly_code
        */
        $data = <<<EOD
 blockly_code: ID int(10) unsigned NOT NULL auto_increment
 blockly_code: SYSTEM_NAME varchar(255) NOT NULL DEFAULT ''
 blockly_code: CODE_TYPE int(3) NOT NULL DEFAULT '0'
 blockly_code: CODE text
 blockly_code: XML text
 blockly_code: UPDATED datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDAxLCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>