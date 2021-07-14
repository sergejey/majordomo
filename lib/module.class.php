<?php
/**
 * Module class
 *
 * Main part of framework
 *
 *
 * @package Framework
 * @author Serge Dzheigalo <jey@activeunit.com>
 * @copyright http://www.activeunit.com/ (c) 2002
 * @since 20-Jan-2004
 * @version 2.4
 */

/**
 * Used for building query string
 */
Define("PARAMS_DELIMITER", "pz_");
/**
 * Used for building query string
 */
Define("STRING_DELIMITER", "sz_");
/**
 * Used for building query string
 */
Define("EQ_DELIMITER", "qz_");

/**
 * Module class
 * @category Modules
 * @package Framework
 * @author Serge Dzheigalo <jey@activeunit.com>
 * @copyright 2002 ActiveUnit
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/modules.class.php
 */
class module
{
    /**
     * @var string module name
     */
    var $name;
    /**
     * @var array module output data
     */
    var $data;
    /**
     * @var string module instance name (optional)
     */
    var $instance;
    /**
     * @var string module teplate file (optional)
     */
    var $template;
    /**
     * @var string module execution result
     */
    var $result;
    /**
     * @var object module owner (parent module)
     */
    var $owner;
    /**
     * @var array module configuration
     */
    var $config;

    /**
     * Module constructor
     * this method should me used in context of each module
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * Module execution
     * this method should me used in context of each module
     * @access public
     * @return void
     */
    public function run()
    {
    }

    /**
     * Getting module params from query string
     *
     * Used for getting module execution params
     * this method should me used in context of each module
     *
     * @access public
     * @return void
     */
    public function getParams()
    {
    }

    /**
     * Saving module params for correct query strings building
     * Used to prevent loosing current module state while interraction with child modules
     * @param array $data params to save
     * @access public
     * @return string|void
     */
    public function saveParams($data = 1)
    {
        if (is_array($data)) {
            if (isset($this->instance)) {
                $data["instance"] = $this->instance;
            }

            // echo $this->name." ".$this->instance."<br>";
            $res = $this->createParamsString($data, $this->name) . PARAMS_DELIMITER . $this->saveParentParams();

            return $res;
        }
    }

    /**
     * Generating query string for saved params
     *
     * @param array $data Params to save
     * @param string $name Name
     * @access private
     * @return string current module params query string
     */
    public function createParamsString($data, $name)
    {
        $params = "";
        $params1 = array();

        foreach ($data as $k => $v) {
            if ($v == "") {
                unset($data[$k]);
            } else {
                $params .= "m" . STRING_DELIMITER . $name . STRING_DELIMITER . $k . EQ_DELIMITER . $v . PARAMS_DELIMITER;
                $params1[] = "$k=$v";
            }
        }

        //echo $params."<br>";
        //$params=toXML($data);
        //$params=serialize($data);
        if (count($params1)) {
            $params = "$name:{" . implode(",", $params1) . "}";
            //echo $params."<br>";
            $params = urlencode(base64_encode($params));
        } else {
            $params = "";
            //echo "-<br>";
        }

        /*
        if (count($data)>0) {
        $temp[$this->name]=$data;
        $xml=new xml_data($temp);
        $res=$xml->string;
        }
         */
        $res = $params;

        return $res;
    }
    // --------------------------------------------------------------------
    /**
     * Restoring module data from query string
     *
     */
    public function restoreParams()
    {
        $pd = gr('pd');
        if (strpos($pd, 'm' . STRING_DELIMITER)) {
            $this->restoreParamsOld();
            return;
        }
        // getting params of all modules
        $modules = explode(PARAMS_DELIMITER, $pd);
        $modulesCnt = count($modules);

        for ($i = 0; $i < $modulesCnt; $i++) {
            $data = base64_decode(urldecode($modules[$i]));

            if (preg_match_all('/(.+?):{(.+?)}/', $data, $matches2, PREG_PATTERN_ORDER)) {
                $matches2Cnt = count($matches2[1]);

                for ($k = 0; $k < $matches2Cnt; $k++) {
                    $cr = array();  // creating params array for current module
                    $module_name = $matches2[1][$k];
                    $module_params = explode(",", $matches2[2][$k]);

                    $moduleParamsCnt = count($module_params);

                    for ($m = 0; $m < $moduleParamsCnt; $m++) {
                        $ar = explode("=", trim($module_params[$m]));

                        $param_name = trim($ar[0]);
                        $param_value = trim($ar[1]);

                        $cr[$param_name] = $param_value;
                    }

                    // setting params for current module
                    // module has instance in params
                    if (isset($cr['instance']) && $cr['instance'] != '') {
                        $instance_params[$module_name][$cr['instance']] = $cr;
                    } else {
                        // module has no instance
                        $global_params[$module_name] = $cr;
                    }
                }
            }
        }

        // restoring params for non-active module
        if (isset($this->instance) && isset($instance_params[$this->name][$this->instance])) {
            // setting params for current instance
            $module_data = $instance_params[$this->name][$this->instance];

            foreach ($module_data as $k => $v) {
                $this->{$k} = $v;
            }
        } elseif (!isset($this->instance)) {
            // module has no instances at all
            $module_data = $global_params[$this->name];
        }

        // setting module data
        if (isset($module_data)) {
            foreach ($module_data as $k => $v) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * Restoring module data from query string (old version)
     * @deprecated
     * @return void
     */
    public function restoreParamsOld()
    {
        $md=gr('md');             // query param - current module
        $pd=gr('pd');             // query param - all params

        $global_params = array();

        // getting params of all modules
        $pd = str_replace(EQ_DELIMITER, "=", $pd);
        $matchPattern = '/m' . STRING_DELIMITER . '(\w+?)' . STRING_DELIMITER . '(\w+?)=(.*?)' . PARAMS_DELIMITER . '/';

        if (preg_match_all($matchPattern, $pd, $matches, PREG_PATTERN_ORDER)) {
            $matchesCnt = count($matches[1]);
            for ($i = 0; $i < $matchesCnt; $i++) {
                $global_params[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
            }
        }

        $xml = new xml_data($global_params);

        if ($md != $this->name) {
            // restoring params for non-active module
            if (isset($xml->hash[$this->name])) {
                $module_data = $xml->hash[$this->name];

                if ((isset($this->instance) && ($module_data["instance"] == $this->instance)) || (!isset($this->instance))) {
                    foreach ($module_data as $k => $v) {
                        $this->{$k} = $v;
                    }
                }
            }
        }

        if ($md == $this->name) {
            // if current module then we should take params directly from query string
            $params = (is_array($_POST) && (count($_POST) > 0)) ? $_POST : $_GET;

            foreach ($params as $k => $v) {
                if (($k == "md") || ($k == "pd") || ($k == "inst") || ($k == "name"))
                    continue;

                // setting params as module properties
                $this->{$k} = $v;
            }
        }
    }

    /**
     * Saving parent module params for correct query strings building
     *
     * @access private
     * @return string parent module params query string
     */
    public function saveParentParams()
    {
        if (isset($this->owner->name))
            return $this->owner->saveParams();

        return "";
    }

    /**
     * Checking module installation status and process with installation if neccessary
     * @return void
     * @access public
     */
    public function checkInstalled()
    {

        $flag_filename = ROOT.'cms/modules_installed/'.$this->name . '.installed';
        if (!file_exists($flag_filename)) {
            $this->install();
        } else {
            /*
            $sqlQuery = "SELECT ID FROM project_modules WHERE NAME = '" . $this->name . "'";
            $rec = SQLSelectOne($sqlQuery);
            if (!isset($rec["ID"])) {
                $this->install();
            }
            */
        }
    }

    /**
     * Getting module configuration
     * Used for loading module config data in $this->config variable
     * @access public
     * @return array
     */
    public function getConfig()
    {
        $sqlQuery = "SELECT *
                     FROM project_modules
                    WHERE NAME = '" . $this->name . "'";

        $rec = SQLSelectOne($sqlQuery);
        $data = $rec["DATA"];

        $this->config = unserialize($data);

        return $this->config;
    }

    /**
     * Saving module configuration
     *
     * Used for saving $this->config variable in project module repository database for future
     * @access public
     * @return void
     */
    public function saveConfig()
    {
        $sqlQuery = "SELECT *
                     FROM project_modules
                    WHERE NAME = '" . $this->name . "'";

        $rec = SQLSelectOne($sqlQuery);

        $rec["DATA"] = serialize($this->config);

        SQLUpdate("project_modules", $rec);
    }

    /**
     * Installing current module
     *
     * Adding information about module in project registry and
     * processing to database installation routine if file "installed" does not
     * exists in module directory.
     *
     * @param string $parent_name Optional parent module for installing packages (reserved for future development)
     * @access private
     * @return void
     */
    public function install($parent_name = "")
    {
        $this->dbInstall("");

        $sqlQuery = "SELECT *
                     FROM project_modules
                    WHERE NAME = '" . $this->name . "'";

        $rec = SQLSelectOne($sqlQuery);
        $rec["NAME"] = $this->name;

        if (!isset($this->title))
            $this->title = $this->name;

        $rec["TITLE"] = $this->title;

        if (isset($this->module_category))
            $rec["CATEGORY"] = $this->module_category;

        if (!isset($rec["ID"])) {
            $rec["ID"] = SQLInsert("project_modules", $rec);
        } else {
            SQLUpdate("project_modules", $rec);
        }
        SQLExec("DELETE FROM project_modules WHERE NAME = '".$this->name."' AND ID!=".$rec["ID"]);

        if (!is_dir(ROOT.'cms/modules_installed')) {
            umask(0);
            mkdir(ROOT.'cms/modules_installed',0777);
        }
        $flag_filename = ROOT.'cms/modules_installed/'.$this->name . '.installed';
        if (!file_exists($flag_filename)) SaveFile($flag_filename, date("H:m d.M.Y"));
    }

    /**
     * UnInstalling current module
     *
     * Removing information about module in project registry and
     *
     * @access private
     * @return void
     */
    public function uninstall()
    {
        $sqlQuery = "SELECT *
                     FROM project_modules
                    WHERE NAME = '" . $this->name . "'";

        $rec = SQLSelectOne($sqlQuery);

        if (isset($rec["ID"])) {
            $sqlQuery = "DELETE
                        FROM project_modules
                       WHERE ID = '" . $rec["ID"] . "'";
            SQLExec($sqlQuery);
        }

        $flag_filename = ROOT.'cms/modules_installed/'.$this->name . '.installed';
        if (file_exists($flag_filename)) unlink($flag_filename);
    }


    // --------------------------------------------------------------------
    /**
     * Module data installation
     *
     * Installing required module data structure into project.
     * (Notes: file "initial.sql" will be executed if found in project directory)
     *
     * @access private
     * @param string $data Required database tables and fields
     * @return void
     */
    public function dbInstall($data)
    {
        $need_optimzation = array();
        $table_defined = array();

        $sql = "";

        $strings = explode("\n", $data);
        $stringsCnt = count($strings);

        for ($i = 0; $i < $stringsCnt; $i++) {
            $strings[$i] = preg_replace('/\/\/.+$/is', '', $strings[$i]);

            $fields = explode(":", $strings[$i]);
            $table = trim(array_shift($fields));

            $definition = trim(implode(':', $fields));
            $definition = str_replace("\r", "", trim($definition));

            if ($definition == "") continue;

            $tmp = explode(" ", $definition);
            $field = $tmp[0];

            if (!in_array(strtolower($field), array('key', 'index', 'fulltext'))) {
                $definition = str_replace($field . ' ', '`' . $field . '` ', $definition);
            }

            if (!isset($table_defined[$table])) {
                // new table
                if (strpos($definition, "auto_increment")) {
                    $definition .= ", PRIMARY KEY(" . $field . ")";
                    //$definition.=", KEY(".$field.")";
                }

                $sql = "CREATE TABLE IF NOT EXISTS $table ($definition) CHARACTER SET utf8 COLLATE utf8_general_ci;";

                $table_defined[$table] = 1;

                SQLExec($sql);

                $result = SQLGetFields($table);
                if (is_array($result)) {
                    foreach ($result as $row) {
                        $tbl_fields[$table][$row['Field']] = 1;
                    }
                }

            } elseif ((strtolower($field) == 'key') || (strtolower($field) == 'index') || (strtolower($field) == 'fulltext')) {
                if (!$indexes_retrieved[$table]) {
                    $result = SQLGetIndexes($table);
                    foreach ($result as $row) {
                        $tbl_indexes[$table][$row['Key_name']] = 1;
                    }


                    $indexes_retrieved[$table] = 1;
                }

                preg_match('/\((.+?)\)/', $definition, $matches);

                $key_name = trim($matches[1], " `");

                if (!isset($tbl_indexes[$table][$key_name])) {
                    $definition = str_replace('`', '', $definition);
                    $sql = "ALTER TABLE $table ADD $definition;";
                    SQLExec($sql);
                    SQLExec("FLUSH TABLES ".$table.";");
                    $to_optimize[] = $table;
                }
            } elseif (!isset($tbl_fields[$table][$field])) {
                // new field
                $sql = "ALTER TABLE $table ADD $definition;";
                SQLExec($sql);
                SQLExec("FLUSH TABLES ".$table.";");
            }
        }

        if (isset($to_optimize[0])) {
            foreach ($to_optimize as $table) {
                SQLExec("OPTIMIZE TABLE " . $table . ";");
            }
        }

        // executing initial query and comments each line to prevent execution next time
        $initialFile = DIR_MODULES . $this->name . "/initial.sql";
        if (file_exists($initialFile)) {
            $data = LoadFile($initialFile);
            $data .= "\n";
            $data = str_replace("\r", "", $data);

            $query = explode("\n", $data);
            $queryCnt = count($query) - 1;

            for ($i = 0; $i < $queryCnt; $i++) {
                if ($query[$i][0] != "#") {
                    SQLExec($query[$i]);
                    $mdf[] = "#" . $query[$i];
                } else {
                    $mdf[] = $query[$i];
                }
            }

            SaveFile($initialFile, join("\n", $mdf));
        }
    }

    /**
     * Getting list of sub-modules
     *
     * Reserved for future development
     *
     * @access private
     * @return array
     */
    public function getSubModules()
    {
        $sqlQuery = "SELECT *
                     FROM project_modules
                    WHERE PARENT_NAME = '" . $this->name . "'";

        return SQLSelect($sqlQuery);
    }

    /**
     * Redirect to another URL whithin project
     *
     * Used for redirection from one URL to another within current module or project
     *
     * @param string $url Special formatted url (ex: "?mode=new", "?(application:{action=test})&md=test&var=value1", etc)
     * @return void
     * @access private
     */
    public function redirect($url)
    {
        global $session;
        global $db;

        $url = $this->makeRealURL($url);

        $session->save();

        if ($_GET['part_load']) {
            $res = array();
            $res['CONTENT'] = '';
            $res['NEED_RELOAD'] = 1;
            $res['REDIRECT'] = $url;
            echo json_encode($res);
            exit;
        }

        if (!headers_sent()) {
            header("Location: $url\n\n");
        } else {
            print "Headers already sent in $filename on line $linenum<br>\n" . "Cannot redirect instead\n";
        }

        exit;
    }

    /**
     * Create "real" URL for current module
     * @access public
     * @param mixed $url Url
     * @return mixed
     */
    public function makeRealURL($url)
    {
        $param_str = $this->parseLinks("<a href=\"$url\">");
        preg_match("<a href=\"(.*?)\">", $param_str, $matches);

        $url = $matches[1];
        $url = str_replace("?", "?" . session_name() . "=" . session_id() . "&", $url);

        return $url;
    }

    /**
     * Summary of cached
     * @access public
     * @param mixed $content Content
     * @return string
     */
    public function cached($content)
    {
        $h = md5($content);

        $filename = ROOT . 'cms/cached/' . $this->name . '_' . $h . '.txt';
        $cache_expire = 15 * 60; // 15 minutes cache expiration time

        if (file_exists($filename)) {
            if ((time() - filemtime($filename)) <= $cache_expire) {
                $cached_result = LoadFile($filename);
            } else {
                unlink($filename);
            }
        }

        if (isset($cached_result) && $cached_result == '') {
            $p = new jTemplate(DIR_TEMPLATES . 'null.html', $this->data, $this);

            $cached_result = $p->parse($content, $this->data, DIR_TEMPLATES);

            SaveFile($filename, $cached_result);
        }

        return $cached_result;
    }

    /**
     * Summary of dynamic
     * @access public
     *
     * @param mixed $content Content
     * @return string
     */
    public function dynamic($content)
    {
        $h = md5($content);

        $content = "<!-- begin_data [aj_" . $h . "] -->" . $content . "<!-- end_data [aj_" . $h . "] -->";
        $filename = ROOT . 'templates_ajax/' . $this->name . '_' . $h . '.html';

        if (!file_exists($filename))
            SaveFile($filename, $content);

        $url = $this->makeRealURL("?");

        if (preg_match('/\?/is', $url)) {
            $url .= "&ajt=" . $h;
        } else {
            $url .= "?ajt=" . $h;
        }

        $res .= "<div id='aj_" . $h . "'>Loading...</div>";
        $res .= "<script language='javascript' type='text/JavaScript'>getBlockData('aj_" . $h . "', '" . $url . "')</script>";

        return $res;
    }

    /**
     * Parsing links to maintain modules structure and data
     *
     * Used to maintain framework structure by saving modules data
     * in query strings and hidden fields
     * Usage:
     * following will be changed
     * [#link param1=value1#]
     * <a href="?param1=value1&param2=value2&...">
     * <a href="?(module:{param1=value1, param2=value2, ...})&param1=value1&param2=value2&...">
     * (note: to prevent link modification use <a href="?...<!-- modified -->">)
     * </form> (note: to prevent "</form>" changing use "</form><!-- modified -->" construction)
     *
     * @access private
     *
     * @param mixed $result Result
     * @return mixed
     */
    public function parseLinks($result)
    {
        $md=gr('md');
        if (!isset($_SERVER['PHP_SELF'])) {
            global $PHP_SELF;
            $_SERVER['PHP_SELF'] = $PHP_SELF;
        }

        $param_str = '';

        if ($md != $this->name) {
            $param_str = $this->saveParams();
        } elseif (isset($this->owner)) {
            $param_str = $this->owner->saveParams();
        }

        // a href links like <a href="?param=value">
        if ((preg_match_all('/="\?(.*?)"/is', $result, $matches, PREG_PATTERN_ORDER))) {
            $matchesCnt = count($matches[1]);
            for ($i = 0; $i < $matchesCnt; $i++) {
                $link = $matches[1][$i];

                if (!is_integer(strpos($link, '<!-- modified -->')))   // skip custom links
                {
                    if (preg_match('/^\((.+?)\)(.*)$/', $link, $matches1)) {
                        $other = $matches1[2];
                        $res_str = $this->codeParams($matches1[1]);
                        $result = str_replace($matches[0][$i], '="' . $_SERVER['PHP_SELF'] . '?pd=' . $res_str . $other . '"', $result);
                    } elseif (strpos($link, "md=") !== 0) {
                        $replaceString = '="' . $_SERVER['PHP_SELF'] . '?pd=' . $param_str;
                        $replaceString .= '&md=' . $this->name . '&inst=' . $this->instance . '&' . $link . '"';

                        $result = str_replace($matches[0][$i], $replaceString, $result); // links
                    } else {
                        $result = str_replace('action="?', 'action="' . $_SERVER['PHP_SELF'] . '"', $result); // forms
                    }
                } else {
                    // remove modified param
                    $link = str_replace('<!-- modified -->', '', $link);
                    $result = str_replace($matches[0][$i], '="' . $link . '"', $result);
                }
            }
        }

        // form hidden params
        if (preg_match_all('/\<input([^\<\>]+?)(value="\((.*?)\)")([^\<\>]*?)\>/is', $result, $matches, PREG_PATTERN_ORDER)) {
            $matches3Cnt = count($matches[3]);

            for ($i = 0; $i < $matches3Cnt; $i++) {
                if (strpos($matches[1][$i], 'type="hidden"') !== false || strpos($matches[4][$i], 'type="hidden"') !== false) {
                    $res_str = 'value="' . $this->codeParams($matches[3][$i]) . '"';
                    $result = str_replace($matches[2][$i], $res_str, $result);
                }
            }
        }

        // [#link ...#]
        if (preg_match_all('/\[#link (.*?)#\]/is', $result, $matches, PREG_PATTERN_ORDER)) {
            $matches1Cnt = count($matches[1]);
            for ($i = 0; $i < $matches1Cnt; $i++) {
                $link = $matches[1][$i];

                if (preg_match('/^\((.+?)\)(.*)$/', $link, $matches1)) {
                    $other = $matches1[2];
                    $res_str = $this->codeParams($matches1[1]);
                    $result = str_replace($matches[0][$i], $_SERVER['PHP_SELF'] . '?pd=' . $res_str . $other, $result);
                } elseif (strpos($link, "md=") !== 0) {
                    $replaceString = $_SERVER['PHP_SELF'] . '?pd=' . $param_str . '&md=' . $this->name . '&inst=' . $this->instance . '&' . $link;

                    $result = str_replace($matches[0][$i], $replaceString, $result); // links
                }
            }
        }

        // form hidden variables (exclude </form><!-- modified -->)
        $replaceString = "<input type=\"hidden\" name=\"pd\" value=\"$param_str\">\n";
        $replaceString .= "<input type=\"hidden\" name=\"md\" value=\"" . $this->name . "\">\n";
        $replaceString .= "<input type=\"hidden\" name=\"inst\" value=\"" . $this->instance . "\">\n";
        $replaceString .= "</FORM><!-- modified -->";

        // forms
        $result = preg_replace("/<\/form>(?!<!-- modified -->)/is", $replaceString, $result);

        return $result;
    }

    /**
     * Parsing params in coded string
     *
     * Used to maintain framework structure by saving modules data
     * in query strings and hidden fields
     *
     * @access private
     *
     * @param mixed $in In
     * @return string
     */
    public function codeParams($in)
    {
        $res_str = '';

        if (preg_match_all('/(.+?):{(.+?)}/', $in, $matches2, PREG_PATTERN_ORDER)) {
            $total = count($matches2[1]);

            for ($k = 0; $k < $total; $k++) {
                $data = array();

                $module_name = $matches2[1][$k];
                $module_params = explode(',', $matches2[2][$k]);

                $totalp = count($module_params);
                for ($m = 0; $m < $totalp; $m++) {
                    $ar = explode("=", trim($module_params[$m]));

                    $data[trim($ar[0])] = trim($ar[1]);
                }

                $res_str .= $this->createParamsString($data, $module_name) . PARAMS_DELIMITER;
            }
        }

        return $res_str;
    }
}
