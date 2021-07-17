<?php
/**
 * jTemplates parser
 *
 * Used to parse jTeplates-style templates and return plain html-pages
 * (for details read manual on jTempates)
 *
 *
 * @package framework
 * @author Serge Dzheigalo <jey@unit.local>
 * @copyright ActiveUnit, Inc. 2001-2003
 * @version 2.1b
 * @modified 07-Jan-2004
 */

/**
 * jTemplates parser
 * @category Parser
 * @package Framework
 * @author Serge Dzheigalo <jey@tut.by>
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/jTemplate.class.php
 */
class jTemplate
{
    /**
     * @var array input data
     */
    var $data; // data
    /**
     * @var string input template filename
     */
    var $template;
    /**
     * @var string parsing result
     */
    var $result;
    /**
     * @var object parser's owner (for correct [#module ...#] tags processing)
     */
    var $owner;

    /**
     * Object constructor
     *
     * @access public
     * @param string $template Input template filename
     * @param array $data Input data
     * @param object $owner Parser owner
     * @return void
     */
    public function __construct($template, &$data, &$owner = '')
    {
        // set current directory for template includes
        if (strpos($template, "/") !== false) {
            $root = preg_replace("/\/[^\/]*?$/", "", $template) . "/";
        } else {
            $root = "";
        }

        $this->data = &$data;
        $this->template = $template;

        if (is_object($owner))
            $this->owner = &$owner;

        if (defined('ALTERNATIVE_TEMPLATES')) {
            $alt_path = str_replace('templates/', ALTERNATIVE_TEMPLATES . '/', $template);

            if (file_exists($alt_path))
                $template = $alt_path;
        }

        if (is_file($template)) {
            $template_file = $this->loadfile($template);
        } else {
            $template_file = $template;
            $template = 'inner_code';
        }

        $res = "";
        if (defined("DEBUG_MODE")) {
            // creating layer for debugging purpose
            $res .= "<!-- begin of file $template -->";
        }

        if ($this->owner->ajax) {
            $this->ajax = 1;
            $this->div_id = $this->owner->name;

            if ($this->owner->owner->name) {
                $this->div_id = $this->div_id . '_' . $this->owner->owner->name;
            }

            $res .= "<div id=\"" . $this->div_id . "\"><!-- begin_data [" . $this->div_id . "] -->"; // dyn
        }

        $res .= $this->parse($template_file, $this->data, $root);

        if (strpos($res, '{#')) {
            //second pass
            $res = str_replace('{#', '[#', $res);
            $res = str_replace('#}', '#]', $res);
            $res = $this->parse($res, $this->data, $root);
        }

        if (isset($this->ajax) && $this->ajax) {
            $res .= "<!-- end_data [" . $this->div_id . "] --></div>"; // dyn
        }

        if (defined("DEBUG_MODE")) {
            $res .= "<!-- end of file $template -->";
        }

        $this->result = $res;
    }


    /**
     * Parsing routine
     *
     *Used to parse jTeplates-style templates and return plain html-pages
     *(for details read manual on jTempates)
     *
     * @access private
     *
     * @param string $res Template strings
     * @param array $hash Data params
     * @param string $dir Current template directory (for correct [#inc ...#] tags parsing)
     * @return string Parsed template strings
     */
    public function parse($res, &$hash, $dir)
    {
        // COMMENTS
        if (is_integer(strpos($res, '<!--#'))) {
            $res = preg_replace("/<!--#.+?#-->/is", "", $res);
        }

        //NOTE: compiler should be perfect for this template engine (����)

        // METHODS
        if (is_integer(strpos($res, '[#method '))) {
            $this->parseMethods($res, $hash);
        }

        // GLOBALS
        $this->parseGlobals($res, $hash);

        // BLOCKS
        if (is_integer(strpos($res, '[#block '))) {
            $this->parseBlocks($res, $hash, $dir);
        }

        // DYN LINKS
        if (isset($this->ajax) && $this->ajax && (is_integer(strpos($res, 'dnlnk') || is_integer(strpos($res, 'dnfrm'))))) {
            $this->parseDynLinks($res);
        }

        // ARRAYS
        if (is_integer(strpos($res, '[#begin '))) {
            $this->parseArrays($res, $hash, $dir);
        }

        // HASHES
        $this->parseHashes($res, $hash);

        // CONDITIONS
        if (is_integer(strpos($res, '[#if '))) {
            $this->parseIf($res, $hash);
        }

        // MODULES
        if (is_integer(strpos($res, '[#module '))) {
            $this->parseModules($res, $hash, $dir);
        }

        // INCLUDE FILES
        if (is_integer(strpos($res, '[#inc '))) {
            $this->parseIncludes($res, $hash, $dir);
        }

        // VARIABLES
        if (is_integer(strpos($res, '[#'))) {
            $this->parseVariables($res, $hash);
        }

        return $res;
    }

    /**
     * Dynlink parsing
     *
     * @access private
     *
     * @param string $res Template strings
     * @return void
     */
    public function parseDynLinks(&$res)
    {
        $res = str_replace('dnlnk', " onClick='return getBlockData(\"" . $this->div_id . "\", this.href);'", $res);
        $res = str_replace('dnfrm', " onClick='return getBlockDataForm(\"" . $this->div_id . "\", this.form);'", $res);
    }

    /**
     * Method tag parsing
     *
     * Used to process block content
     *
     * @access private
     * @param string $res Template strings
     * @param mixed $hash Hash (not used)
     * @return string parsed template strings
     */
    public function parseMethods(&$res, &$hash)
    {
        if (preg_match_all('/\[#method (\w+?)#\](.+?)\[#endmethod#\]/is', $res, $matches)) {
            $count_matches_0 = count($matches[0]);

            for ($i = 0; $i < $count_matches_0; $i++) {
                $method = $matches[1][$i];
                $content = $matches[2][$i];

                if (method_exists($this->owner, $method)) {
                    $o = $this->owner->$method($content);
                } elseif (function_exists($method)) {
                    $o = $method($content, $this->owner);
                } else {
                    $o = "<!-- method \"$method\" not found -->" . $content;
                }

                $res = str_replace($matches[0][$i], $o, $res);
            }
        }

        return $res;
    }


    /**
     * Block tag parsing
     * Used to include template blocks with params [#block name param="value"#]..[#endblock name#]
     *
     * @access private
     *
     * @param string $res Template strings
     * @param mixed $hash Hash (not used)
     * @return void
     */
    public function parseBlocks(&$res, &$hash)
    {
        if (preg_match_all('/\[#block (.*?)#\]/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_0 = count($matches[0]);

            for ($i = 0; $i < $count_matches_0; $i++) {
                $raw = $matches[1][$i];

                if (is_integer(strpos($raw, '="'))) {
                    // inc file parameters
                    $new_hash = $hash;

                    preg_match_all('/(\w+?)="(.*?)"/', $raw, $matches1, PREG_PATTERN_ORDER);

                    $count_matches1_0 = count($matches1[0]);

                    for ($k = 0; $k < $count_matches1_0; $k++) {
                        $new_hash[$matches1[1][$k]] = $matches1[2][$k];

                        $raw = str_replace($matches1[0][$k], '', $raw);
                    }
                }

                $block_name = $dir . trim($raw);
                $file_name = $block_name . ".html";

                if (!file_exists(ROOT . "templates/blocks/" . $file_name)) {
                    $res = str_replace($matches[0][$i], "<!-- Cannot find file $file_name -->", $res);
                } else {
                    $tmp = explode("[#...#]", LoadFile(ROOT . "templates/blocks/" . $file_name));

                    if (isset($new_hash) && (is_integer(strpos($tmp[0], '[#')) || is_integer(strpos($tmp[0], '<#')))) {
                        // CONDITIONS
                        $this->parseGlobals($tmp[0], $new_hash);
                        $this->parseIf($tmp[0], $new_hash);

                        // [#VARIABLE#] - general variables
                        if (preg_match_all('/\[#(\w+?)#\]/', $tmp[0], $matches1, PREG_PATTERN_ORDER)) {
                            $matches1Cnt = count($matches1[1]);
                            for ($l = 0; $l < $matches1Cnt; $l++) {
                                $tmp[0] = str_replace($matches1[0][$l], $new_hash[$matches1[1][$l]], $tmp[0]);
                            }
                        }
                    }

                    if (isset($new_hash) && (is_integer(strpos($tmp[1], '[#')) || is_integer(strpos($tmp[1], '<#')))) {
                        // CONDITIONS
                        $this->parseGlobals($tmp[1], $new_hash);
                        $this->parseIf($tmp[1], $new_hash);

                        // [#VARIABLE#] - general variables
                        if (preg_match_all('/\[#(\w+?)#\]/', $tmp[1], $matches1, PREG_PATTERN_ORDER)) {
                            $matches1Cnt = count($matches1[1]);
                            for ($l = 0; $l < $matches1Cnt; $l++) {
                                $tmp[1] = str_replace($matches1[0][$l], $new_hash[$matches1[1][$l]], $tmp[1]);
                            }
                        }
                    }

                    $res = str_replace($matches[0][$i], $tmp[0], $res);
                    $res = str_replace("[#endblock $block_name#]", $tmp[1], $res);

                    unset($new_hash);
                }
            }
        }
    }

    /**
     * <#VARIABLE#> tag parsing
     *
     * @access private
     * @param string $res Template strings
     * @param array $hash Data params
     * @return void
     */
    public function parseGlobals(&$res, &$hash)
    {
        // <#VARIABLE#> - global variables
        if (preg_match_all('/[<#]#(\w+?)#[>#]/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_1 = count($matches[1]);

            for ($i = 0; $i < $count_matches_1; $i++) {
                if (defined($matches[1][$i])) {
                    $res = str_replace($matches[0][$i], constant($matches[1][$i]), $res);
                } else {
                    if (!isset($hash[$matches[1][$i]])) $hash[$matches[1][$i]]=null;
                    $res = str_replace($matches[0][$i], $hash[$matches[1][$i]], $res);
                }
            }
        }
    }

    /**
     * [#beging ...#]...[#end ...#] tag parsing
     *
     * @access private
     * @param string $res Template strings
     * @param array $hash Data params
     * @param mixed $dir Dir
     * @return void
     */
    public function parseArrays(&$res, &$hash, $dir)
    {
        // [#begin ARRAY#][#DATA#][#end ARRAY#] - arrays/blocks
        while (preg_match_all('/\[#begin (.*?)#\](.*?)\[#end \1#\]/is', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_0 = count($matches[0]);

            for ($i = 0; $i < $count_matches_0; $i++) {
                if (!isset($hash[$matches[1][$i]])) $hash[$matches[1][$i]]=null;
                $var = $hash[$matches[1][$i]];
                $line1 = $matches[2][$i];
                $res1 = "";

                if ((is_array($var)) && (count($var) > 0) && (!isset($var[0]))) {
                    // hashtable
                    if (preg_match_all("/<#{$matches[1][$i]}\.(\w+?)#>/", $line1, $matches2, PREG_PATTERN_ORDER)) {
                        $matches2Cnt = count($matches2[1]);

                        for ($m = 0; $m < $matches2Cnt; $m++) {
                            @$line1 = str_replace($matches2[0][$m], $this->templateSafe($var[$matches2[1][$m]]), $line1);
                        }
                    }

                    $res1 .= $this->parse($line1, $var, $dir);
                } elseif ((is_array($var)) && (count($var) > 0) && (is_array($var[0]))) {
                    //    echo $matches[1][$i]."<br>";
                    // index array
                    $count_var = count($var);

                    for ($k = 0; $k < $count_var; $k++) {
                        $line2 = $line1;
                        $matches1 = array();

                        // <#ARRAY.VARIABLE#> - array variables
                        if (preg_match_all("/<#{$matches[1][$i]}\.(\w+?)#>/", $line1, $matches1, PREG_PATTERN_ORDER)) {
                            $count_matches1_1 = count($matches1[1]);

                            for ($m = 0; $m < $count_matches1_1; $m++) {
                                @$line2 = str_replace($matches1[0][$m], $this->templateSafe($var[$k][$matches1[1][$m]]), $line2);
                            }
                        }

                        // IF operations if no sub-arrays
                        if ((!is_integer(strpos($line2, "[#begin")))) {
                            // CONDITIONS
                            if (is_integer(strpos($line2, "[#if"))) {
                                $this->parseIf($line2, $var[$k]);
                            }

                            $this->parseVariables($line2, $var[$k]);
                        }

                        // SELF-CALL FOR ARRAY ELEMENT
                        $searchStr = "[#tree " . $matches[1][$i] . "#]";
                        $replaceStr = $this->parse("[#begin " . $matches[1][$i] . "#]" . $line1 . "[#end " . $matches[1][$i] . "#]", $var[$k], $dir);

                        $line2 = str_replace($searchStr, $replaceStr, $line2);

                        if (is_integer(strpos($line2, "[#"))) {
                            $res1 .= $this->parse($line2, $var[$k], $dir);
                        } else {
                            $res1 .= $line2;
                        }
                    }
                } elseif ((is_array($var)) && (count($var) > 0) && isset($var[0]) && (!is_array($var[0]))) {
                    // NOT HASH TABLE - PLAIN ARRAY
                    $this->parseHashes($line1, $hash);

                    if (isset($hash['VALUE']))
                        $tmp = $hash['VALUE'];

                    $varCnt = count($var);

                    $hash['__Count__'] = $varCnt;

                    for ($k = 0; $k < $varCnt; $k++) {
                        $hash['VALUE'] = &$var[$k];
                        $hash['__Key__'] = $k;

                        $res1 .= $this->parse($line1, $hash, $dir);
                    }

                    if (isset($tmp))
                        $hash['VALUE'] = $tmp;

                    unset($tmp);
                    unset($hash['__Count__']);
                    unset($hash['__Key__']);
                }

                $res = str_replace($matches[0][$i], $res1, $res);
            }
        }
    }

    /**
     * <#VARIABLE.VARIABLE#> tag parsing
     *
     * @access private
     * @param string $res Template strings
     * @param array $hash Data params
     * @return void
     */
    public function parseHashes(&$res, &$hash)
    {
        // <#VARIABLE.VALUE#> - hash variables (pre-conditions)
        if (preg_match_all('/<#(\w+?)\.(\w+?)#>/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_1 = count($matches[1]);
            for ($i = 0; $i < $count_matches_1; $i++) {
                if (!isset($hash[$matches[1][$i]][$matches[2][$i]])) $hash[$matches[1][$i]][$matches[2][$i]]=null;
                $res = str_replace($matches[0][$i], $this->templateSafe($hash[$matches[1][$i]][$matches[2][$i]]), $res);
            }
        }
    }

    /**
     * If tag parsing
     *
     * @access private
     * @param string $res Template strings
     * @param array $hash Data params
     * @return void
     */
    public function parseIf(&$res, &$hash)
    {
        // removing old-slyle "else" and "endif" expressions
        $res = preg_replace("/\[#else .+?#\]/", "[#else#]", $res);
        $res = preg_replace("/\[#endif .+?#\]/", "[#endif#]", $res);

        $last_level = 0;

        // looking through all if coditions
        $old_res = $res;
        $old_res_count = substr_count($old_res, "[#if");

        for ($k = 1; $k <= $old_res_count; $k++) {
            $begin = 0;
            $replaced = 0;

            while (!$replaced) {
                $begin = strpos($res, "[#if", $begin);
                $end = strpos($res, "[#endif#]", $begin);
                $middle = strpos($res, "[#if", ($begin + 1));

                if ((is_integer($begin)) && (is_integer($end)) && ($end > $begin) && ((!is_integer($middle)) || ($middle > $end))) {
                    $bdy = substr($res, $begin, ($end - $begin));
                    $bdy_old = $bdy . "[#endif#]";
                    $tmp2 = strpos($bdy, '#]');
                    $condition = trim(substr($bdy, 4, ($tmp2 - 4)));
                    $body = substr($bdy, ($tmp2 + 2), strlen($bdy));

                    $true_part = "";
                    $false_part = "";

                    $temp = array();
                    $temp = explode("[#else#]", $body);

                    $true_part = $temp[0];
                    $false_part = isset($temp[1]) ? $temp[1] : "";

                    $condition = preg_replace('/^!(\w+)$/', '!IsSet($hash[\'\\1\'])', $condition);
                    $condition = preg_replace('/^(\w+)$/', 'IsSet($hash[\'\\1\'])', $condition);
                    $condition = preg_replace('/(\w+)(?=[=!<>])/', '$hash[\'\\1\']', $condition);
                    $condition = preg_replace('/(\w+)[[:space:]](?=[=!<>])/', '$hash[\'\\1\']', $condition);
                    $condition = preg_replace('/\((\w+)\)/', '($hash[\'\\1\'])', $condition);
                    $condition = preg_replace('/\]=(?=[^\w=])/', ']==', $condition);

                    $str = "if ($condition) {\$res1=\$true_part;} else {\$res1=\$false_part;}";
                    @eval($str);

                    $bdy = $res1;
                    $res = str_replace($bdy_old, $bdy, $res);
                    $replaced = 1;
                } elseif ((is_integer($begin)) && (is_integer($end)) && ($end > $begin) && ((!is_integer($middle)) || ($middle < $end))) {
                    $replaced = 0;
                    $begin = $middle;
                } else {
                    $replaced = 1;
                }
            }
        }
    }


    /**
     * Module tag parsing
     *
     * Used to include other object-modules in current workspace
     *
     * @access private
     * @param string $res Template strings
     * @param array $hash Data params
     * @param string $dir Current template directory (for correct [#inc ...#] tags parsing)
     * @return void
     */
    public function parseModules(&$res, &$hash, $dir)
    {
        global $md;
        global $inst;

        $instance = $inst;

        if (preg_match_all('/\[#module (.*?)#\]/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_0 = count($matches[0]);

            for ($i = 0; $i < $count_matches_0; $i++) {
                $data = $matches[1][$i];
                $tmp = "";

                // reading module data from module including directive
                $module_data = array();

                if (preg_match_all('/(\w+?)="(.*?)"/i', $data, $matches1, PREG_PATTERN_ORDER)) {
                    $count_matches1_0 = count($matches1[0]);

                    for ($k = 0; $k < $count_matches1_0; $k++) {
                        $key = $matches1[1][$k];
                        $value = $matches1[2][$k];

                        if ($key == "template") {
                            $value = $dir . $value;
                        }

                        $module_data[$key] = $value;
                    }
                }

                if ((file_exists(DIR_MODULES . $module_data["name"] . '/' . $module_data["name"] . ".class.php")) || (class_exists($module_data["name"]))) {
                    // including module class
                    if (!class_exists($module_data["name"])) {
                        include_once(DIR_MODULES . $module_data["name"] . '/' . $module_data["name"] . ".class.php");
                    }
                    // creating code for module creation and running

                    $obj = "\$object$i";
                    $code = "";
                    $code .= "$obj=new " . $module_data["name"] . "();\n";
                    $code .= $obj . "->owner=&\$this->owner;\n";

                    // setting module parameters from module including directive
                    foreach ($module_data as $k => $v) {
                        if ($k == "name") continue;

                        $code .= $obj . "->" . $k . "='" . addslashes($v) . "';\n";
                    }

                    // setting other module parameters
                    // if current request is to this module, then run get params otherwise get params from encoded query
                    if (($md != $module_data["name"])
                        || (($module_data["instance"] != '') && ($module_data["instance"] != $instance) && ($instance != ''))) {
                        // restoring module params from coded string (module should not overwrite this method)
                        $code .= $obj . "->restoreParams();\n";
                    } elseif (($module_data["name"] == $md)
                        && (($module_data["instance"] == '') || ($module_data["instance"] == $instance) || ($instance == ''))) {
                        // getting module params from query string (every module should handle this method)
                        $code .= $obj . "->getParams();\n";
                    }

                    // repeating module set parameters for security reasons
                    foreach ($module_data as $k => $v) {
                        if ($k == "name") continue;

                        $code .= $obj . "->" . $k . "='" . addslashes($v) . "';\n";
                    }

                    StartMeasure("module_" . $module_data["name"]);

                    if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . $module_data["name"] . '_' . SETTINGS_SITE_LANGUAGE . '.php'))
                        include_once(ROOT . 'languages/' . $module_data["name"] . '_' . SETTINGS_SITE_LANGUAGE . '.php');

                    if (file_exists(ROOT . 'languages/' . $module_data["name"] . '_default.php'))
                        include_once(ROOT . 'languages/' . $module_data["name"] . '_default.php');

                    // run module and insert module result in template
                    $code .= $obj . "->run();\n";
                    $code .= "\$tmp=" . $obj . "->result;\n";

                    try {
                        eval($code);
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage();
                    }

                    EndMeasure("module_" . $module_data["name"]);
                } else {
                    // module class file was not found
                    global $current_installing_module;

                    $rep_ext = '';

                    if (preg_match('/\.dev/is', $_SERVER['HTTP_HOST'])) {
                        $rep_ext = '.dev';
                        $install_dir = "/var/projects/repository/engine_2.x/modules/";
                    }

                    if (preg_match('/\.jbk/is', $_SERVER['HTTP_HOST'])) {
                        $rep_ext = '.jbk';
                        $install_dir = "d:/jey/projects/repository/engine_2.x/modules/";
                    }

                    if (!$current_installing_module[$module_data["name"]] && $rep_ext != '' && (@is_dir($install_dir . $module_data["name"]))) {
                        /*
                         $tmp  = "<div><iframe src=\"http://installer.dev/installer.php?host=" . $_SERVER['HTTP_HOST'];
                         $tmp .= "&doc_root=" . $_SERVER['DOCUMENT_ROOT'] . "&mode=install&modules[]=" . $module_data["name"];
                         $tmp .= "\" width=100% height=100></iframe></div>";
                         */
                        $wnd_name = "win" . rand(1, 10000000);

                        $tmp = "<script language='javascript' type='text/JavaScript'>";
                        $tmp .= "wnd=window.open(\"http://installer" . $rep_ext . "/installer.php?host=" . $_SERVER['HTTP_HOST'];
                        $tmp .= "&doc_root=" . $_SERVER['DOCUMENT_ROOT'] . "&mode=install&modules[]=" . $module_data["name"];
                        $tmp .= "\", \"" . $wnd_name . "\", \"height=400,width=400\");</script>";

                        $current_installing_module[$module_data["name"]] = 1;

                        echo $tmp;
                    } else {
                        $tmp = "<p align=center><font color='red'><b>Module \"" . $module_data["name"] . "\" not found</b>";
                        $tmp .= " (" . str_replace('#', '', $matches[0][$i]) . ")</font></p>";
                    }
                }

                //echo $matches[0][$i];
                //echo htmlspecialchars($tmp)."\n\n";
                $res = str_replace($matches[0][$i], $tmp, $res);
            }
        }
    }


    /**
     * [#inc ...#] tag parsing
     *
     * @access private
     * @param string $res Template strings
     * @param array $hash Data params
     * @param string $dir Current template directory (for correct [#inc ...#] tags parsing)
     * @return void
     */
    public function parseIncludes(&$res, &$hash, $dir)
    {
        if (preg_match_all('/\[#inc (.*?)#\]/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_0 = count($matches[0]);

            for ($i = 0; $i < $count_matches_0; $i++) {
                $raw = $matches[1][$i];

                if (is_integer(strpos($raw, '="'))) {
                    // inc file parameters
                    $new_hash = $hash;
                    preg_match_all('/(\w+?)="(.*?)"/', $raw, $matches1, PREG_PATTERN_ORDER);

                    $count_matches1_0 = count($matches1[0]);

                    for ($k = 0; $k < $count_matches1_0; $k++) {
                        $new_hash[$matches1[1][$k]] = $matches1[2][$k];

                        $raw = str_replace($matches1[0][$k], '', $raw);
                    }
                } else {
                    $new_hash = &$hash;
                }

                $file_name = $dir . trim($raw);
                $new_root = dirname($file_name) . "/";

                if (defined('ALTERNATIVE_TEMPLATES')) {
                    $alt_path = str_replace('templates/', ALTERNATIVE_TEMPLATES . '/', $file_name);

                    if (file_exists($alt_path)) {
                        $file_name = $alt_path;
                    }
                }

                if (!file_exists($file_name)) {
                    $res = str_replace($matches[0][$i], "<!-- Cannot find file $file_name -->", $res);
                } else {
                    if ((defined("DEBUG_MODE")) && !is_integer(strpos($file_name, ".js"))) {
                        $id = "block" . (int)rand(0, 100000);

                        /*
                        $replaceStr  = "<!-- begin of file $file_name -->";
                        $replaceStr .= $this->parse($this->loadfile($file_name) . "<!-- end of file $file_name -->", $new_hash, $new_root);
                        $res = str_replace($matches[0][$i], $replaceStr, $res);
                        */

                        $res = str_replace($matches[0][$i], "" . $this->parse($this->loadfile($file_name) . "", $new_hash, $new_root), $res);
                    } else {
                        $res = str_replace($matches[0][$i], $this->parse($this->loadfile($file_name), $new_hash, $new_root), $res);
                    }
                }
            }
        }
    }

    /**
     * [#VARIABLE#] tag parsing
     *
     * @access private
     * @param string $res template strings
     * @param array $hash data params
     * @return void
     */
    public function parseVariables(&$res, &$hash)
    {
        // [#VARIABLE#] - general variables
        if (preg_match_all('/\[#(\w+?)#\]/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_1 = count($matches[1]);

            for ($i = 0; $i < $count_matches_1; $i++) {
                $res = str_replace($matches[0][$i], $this->templateSafe($hash[$matches[1][$i]]), $res);
            }
        }

        // [#VARIABLE.VALUE#] - hash variables
        if (preg_match_all('/\[#(\w+?)\.(\w+?)#\]/', $res, $matches, PREG_PATTERN_ORDER)) {
            $count_matches_1 = count($matches[1]);

            for ($i = 0; $i < $count_matches_1; $i++) {
                $res = str_replace($matches[0][$i], $this->templateSafe($hash[$matches[1][$i]][$matches[2][$i]]), $res);
            }
        }
    }

    /**
     * Summary of templateSafe
     *
     * @access private
     *
     * @param mixed $val Param
     * @return mixed
     */
    public function templateSafe($val)
    {
        $res = $val;
        $res = str_replace('[#', '&#091#', $res);
        $res = str_replace('#]', '#&#093', $res);
        $res = str_replace('{#', '&#123#', $res);
        $res = str_replace('#}', '#&#125', $res);
        $res = str_replace('<#', '&#060#', $res);
        $res = str_replace('#>', '#&#062', $res);

        return $res;
    }

    /**
     * File loading
     *
     * Simple text-file loader
     *
     * @access private
     * @param string $filename filename to load
     * @return string file content
     */
    public function loadfile($filename)
    {
        global $preload;

        $data = "";

        /*
        if (@$preload[$filename]!="")
        {
           return $preload[$filename];
        }
        */

        if (!is_file($filename)) return '';

        $f = fopen("$filename", "r");
        $data = "";

        if ($f) {
            $fsize = filesize($filename);

            if ($fsize == 0) {
                fclose($f);
                return '';
            }

            $data = fread($f, $fsize);

            $preload[$filename] = $data;
            fclose($f);
        }

        return $data;
    }
}

