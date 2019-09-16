<?php
/**
 * Patterns
 *
 * Patterns
 *
 * @package project
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @version 0.1 (wizard, 15:12:59 [Dec 13, 2011])
 */
//
//
class patterns extends module
{
    /**
     * patterns
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "patterns";
        $this->title = "<#LANG_MODULE_PATTERNS#>";
        $this->module_category = "<#LANG_SECTION_OBJECTS#>";
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
        if (IsSet($this->script_id)) {
            $out['IS_SET_SCRIPT_ID'] = 1;
        }
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
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'patterns' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_patterns') {
                $this->search_patterns($out);
            }
            if ($this->view_mode == 'edit_patterns') {
                $this->edit_patterns($out, $this->id);
            }

            if ($this->view_mode == 'clone' && $this->id) {
                $this->clone_pattern($this->id);
            }

            if ($this->view_mode == 'moveup' && $this->id) {
                $this->reorder_items($this->id, 'up');
                $this->redirect("?");
            }
            if ($this->view_mode == 'movedown' && $this->id) {
                $this->reorder_items($this->id, 'down');
                $this->redirect("?");
            }


            if ($this->view_mode == 'delete_patterns') {
                $this->delete_patterns($this->id);
                $this->redirect("?");
            }
        }
    }

    function reorder_items($id, $direction = 'up')
    {
        $element = SQLSelectOne("SELECT * FROM patterns WHERE ID='" . (int)$id . "'");
        if ($element['PARENT_ID']) {
            $all_elements = SQLSelect("SELECT * FROM patterns WHERE PARENT_ID=" . $element['PARENT_ID'] . " ORDER BY PRIORITY DESC, TITLE");
        } else {
            $all_elements = SQLSelect("SELECT * FROM patterns WHERE PARENT_ID=0 ORDER BY PRIORITY DESC, TITLE");
        }
        $total = count($all_elements);
        for ($i = 0; $i < $total; $i++) {
            if ($all_elements[$i]['ID'] == $id && $i > 0 && $direction == 'up') {
                $tmp = $all_elements[$i - 1];
                $all_elements[$i - 1] = $all_elements[$i];
                $all_elements[$i] = $tmp;
                break;
            }
            if ($all_elements[$i]['ID'] == $id && $i < ($total - 1) && $direction == 'down') {
                $tmp = $all_elements[$i + 1];
                $all_elements[$i + 1] = $all_elements[$i];
                $all_elements[$i] = $tmp;
                break;
            }
        }
        $priority = ($total) * 10;
        for ($i = 0; $i < $total; $i++) {
            $all_elements[$i]['PRIORITY'] = $priority;
            $priority -= 10;
            SQLUpdate('patterns', $all_elements[$i]);
        }
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function clone_pattern($id)
    {
        $rec = SQLSelectOne("SELECT * FROM patterns WHERE ID='" . (int)$id . "'");
        $rec['TITLE'] .= ' (copy)';
        unset($rec['ID']);
        $rec['LOG'] = '';
        $rec['ID'] = SQLInsert('patterns', $rec);
        $this->redirect("?view_mode=edit_patterns&id=" . $rec['ID']);
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
     * patterns search
     *
     * @access public
     */
    function search_patterns(&$out)
    {
        require(DIR_MODULES . $this->name . '/patterns_search.inc.php');
    }

    /**
     * patterns edit/add
     *
     * @access public
     */
    function edit_patterns(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/patterns_edit.inc.php');
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function checkAllPatterns($from_user_id = 0)
    {
        $current_context = context_getcurrent($from_user_id);
        //DebMes("current context:".$current_context);
        if ($from_user_id && preg_match('/^ext(\d+)/', $current_context, $m)) {
            $res = $this->checkExtPatterns($m[1],$from_user_id);
        } else {
            $patterns = SQLSelect("SELECT * FROM patterns WHERE 1 AND PARENT_ID='" . (int)$current_context . "' AND PATTERN_TYPE=0 ORDER BY PRIORITY DESC, TITLE");
            $total = count($patterns);
            $res = 0;
            for ($i = 0; $i < $total; $i++) {
                $matched = $this->checkPattern($patterns[$i]['ID'], $from_user_id);
                if ($matched) {
                    $res = 1;
                    if ($patterns[$i]['IS_LAST']) {
                        break;
                    }
                }
            }

        }


        if (!$res) {
            $patterns = SQLSelect("SELECT patterns.* FROM patterns LEFT JOIN patterns AS p2 ON p2.ID=patterns.PARENT_ID WHERE p2.IS_COMMON_CONTEXT=1 AND patterns.PARENT_ID!=0 ORDER BY patterns.ID");
            $total = count($patterns);
            $res = 0;
            for ($i = 0; $i < $total; $i++) {
                $res = $this->checkPattern($patterns[$i]['ID'], $from_user_id);
            }
            if (!$res && $from_user_id) {
                $res = $this->checkExtPatterns(0);
            }
        }

        return $res;

    }


    function getAvailableActions()
    {
        $current_context = context_getcurrent();

        if (preg_match('/^ext(\d+)/', $current_context, $m)) {
            $res = $this->getAvailableActionsExt($m[1]);
            return $res;
        }

        $patterns = SQLSelect("SELECT * FROM patterns WHERE 1 AND PARENT_ID='" . (int)$current_context . "' AND IS_COMMON_CONTEXT!=1 ORDER BY ID");
        $total = count($patterns);
        if (!$total) {
            return 0;
        }
        $res = array();
        for ($i = 0; $i < $total; $i++) {
            $res[] = $patterns[$i]['TITLE'];
        }
        return $res;

    }


    function checkExtPatterns($ext_context_id, $user_id=0)
    {

        $message = SQLSelectOne("SELECT MESSAGE FROM shouts ORDER BY ID DESC LIMIT 1");
        $phrase = trim($message['MESSAGE']);
        if (!$phrase) {
            return 0;
        }

        $this->getConnectDetails();
        if ($this->connect_username && $this->connect_password) {
            //check external patterns
            $data = array();
            $data['mode'] = 'check';
            $data['context_id'] = $ext_context_id;
            $data['phrase'] = $phrase;

            // POST TO SERVER
            $url = 'https://connect.smartliving.ru/patterns/';
            $fields = array(
                'data' => urlencode(serialize($data))
            );
            //DebMes("Sending data: ".serialize($data));

            //url-ify the data for the POST
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERPWD, $this->connect_username . ":" . $this->connect_password);
            //execute post
            $result = curl_exec($ch);
            //close connection
            curl_close($ch);

            DebMes("External context response: " . $result, 'patterns');

            $data = unserialize($result);

            if ($data['MATCHED_CONTEXT']) {

                if (is_array($data['PHRASES'])) {
                    foreach ($data['PHRASES'] as $details) {
                        say($details['PHRASE'], (int)$details['LEVEL']);
                    }
                }

                $data['TIMEOUT_CODE'] = '';
                if (is_array($data['TIMEOUT_PHRASES'])) {
                    foreach ($data['TIMEOUT_PHRASES'] as $details) {
                        $data['TIMEOUT_CODE'] .= 'say("' . $details['PHRASE'] . '", "' . (int)$details['LEVEL'] . '");';
                    }
                }

                if (is_array($data['URLS'])) {
                    foreach ($data['URLS'] as $url) {
                        $rec = array();
                        $rec['EVENT_TYPE'] = 'openurl';
                        $rec['WINDOW'] = 'alice';
                        $rec['TERMINAL_TO'] = '*';
                        $rec['ADDED'] = date('Y-m-d H:i:s');
                        $rec['EXPIRE'] = date('Y-m-d H:i:s', time() + 10);
                        $rec['DETAILS'] = $url;
                        $rec['ID'] = SQLInsert('events', $rec);

                        postToWebSocketQueue('TERMINAL_EVENT', $rec, 'PostEvent');

                    }
                }

                if ($data['MEDIA_URL']) {
                    playMedia($data['MEDIA_URL']);
                }

                context_activate_ext($data['NEW_CONTEXT'], (int)$data['TIMEOUT'], $data['TIMEOUT_CODE'], (int)$data['TIMEOUT_CONTEXT_ID'],$user_id);

                return $data['MATCHED_CONTEXT'];

            }

        }
        return 0;
    }


    function getAvailableActionsExt($ext_context_id)
    {
        $this->getConnectDetails();
        if ($this->connect_username && $this->connect_password) {
            //check external actions
            $data = array();
            $data['mode'] = 'actions';
            $data['context_id'] = $ext_context_id;

            // POST TO SERVER
            $url = 'https://connect.smartliving.ru/patterns/';
            $fields = array(
                'data' => urlencode(serialize($data))
            );

            //url-ify the data for the POST
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->connect_username . ":" . $this->connect_password);
            //execute post
            $result = curl_exec($ch);
            //close connection
            curl_close($ch);
            $data = unserialize($result);

            DebMes("External actions response: " . $result, 'patterns');

            if (is_array($data['ACTIONS'])) {
                return $data['ACTIONS'];
            }
        }
        return 0;
    }


    function getConnectDetails()
    {
        if (!$this->connect_username && !$this->connect_password) {
            include_once(DIR_MODULES . 'connect/connect.class.php');
            $cn = new connect();
            $cn->getConfig();
            if ($cn->config['CONNECT_USERNAME'] && $cn->config['CONNECT_PASSWORD']) {
                $this->connect_username = $cn->config['CONNECT_USERNAME'];
                $this->connect_password = $cn->config['CONNECT_PASSWORD'];
            } else {
                $this->connect_username = 'anonymous';
                $this->connect_password = '';
            }
        }
    }


    function runPatternAction($id, $matches = array(), $original = '', $from_user_id = 0)
    {
        $rec = SQLSelectOne("SELECT * FROM patterns WHERE ID='" . (int)$id . "'");

        //global $noPatternMode;
        //$noPatternMode=1;
        if ($rec['SCRIPT_ID']) {
            runScriptSafe($rec['SCRIPT_ID'], $matches);
        } elseif ($rec['SCRIPT']) {

            try {
                if (isset($this->morphy)) {
                    $total = count($matches);
                    $bases = array();
                    for ($i = 0; $i < $total; $i++) {
                        $Word = mb_strtoupper($matches[$i], 'UTF-8');
                        $form_bases = $this->morphy->getBaseForm($Word);
                        $bases[$i] = $form_bases[0];
                    }
                }

                $code = $rec['SCRIPT'];
                $success = eval($code);
                if ($success === false) {
                    DebMes("Error in pattern code: " . $code, 'patterns');
                    registerError('patterns', "Error in pattern code: " . $code);
                }
            } catch (Exception $e) {
                DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.', 'patterns');
                registerError('patterns', get_class($e) . ', ' . $e->getMessage());
            }

        }
        //$noPatternMode=0;

    }

    function runPatternExitAction($id, $script_exit)
    {

        if ($script_exit) {
            try {
                $code = $script_exit;
                $success = eval($code);
                if ($success === false) {
                    DebMes("Error in pattern exit code: " . $code, 'patterns');
                    registerError('patterns', "Error in pattern exit code: " . $code);
                }
            } catch (Exception $e) {
                DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.', 'patterns');
                registerError('patterns', get_class($e) . ', ' . $e->getMessage());
            }
        }

    }

    function propertySetHandle($object, $property, $value)
    {
        $patterns = SQLSelect("SELECT ID FROM patterns WHERE PATTERN_TYPE=1 AND LINKED_OBJECT = '" . DBSafe($object) . "' AND LINKED_PROPERTY = '" . DBSafe($property) . "'");
        $total = count($patterns);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                $this->checkPattern($patterns[$i]['ID']);
            }
        }
    }


    /**
     *
     * Generate all the possible combinations among a set of nested arrays. *
     * @param array $data The entrypoint array container.
     * @param array $all The final container (used internally).
     * @param array $group The sub container (used internally).
     * @param mixed $val The value to append (used internally).
     * @param int $i The key index (used internally).
     */

    function generate_combinations(array $data, array &$all = array(), array $group = array(), $value = null, $i = 0, $key = null)
    {
        $keys = array_keys($data);
        if (isset($value) === true) {
            $group[$key] = $value;
        }
        if ($i >= count($data)) {
            array_push($all, $group);
        } else {
            $currentKey = $keys[$i];
            $currentElement = $data[$currentKey];
            if (count($data[$currentKey]) <= 0) {
                $this->generate_combinations($data, $all, $group, null, $i + 1, $currentKey);
            } elseif (is_array($currentElement)) {
                foreach ($currentElement as $val) {
                    $this->generate_combinations($data, $all, $group, $val, $i + 1, $currentKey);
                }
            }
        }
        return $all;
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function checkPattern($id, $from_user_id = 0)
    {
        global $session;
        global $pattern_matched;


        $this_pattern_matched = 0;
        $condition_matched = 0;

        if (!checkAccess('pattern', $id)) return 0;

        $rec = SQLSelectOne("SELECT * FROM patterns WHERE ID='" . (int)$id . "'");

        if ($rec['PATTERN_TYPE'] == 1) {
            //conditional pattern
            $value = getGlobal($rec['LINKED_OBJECT'] . '.' . $rec['LINKED_PROPERTY']);

            $condition_value = $rec['CONDITION_VALUE'];

            if (($rec['CONDITION'] == 2 || $rec['CONDITION'] == 3)
                && $condition_value != ''
                && !is_numeric($condition_value)
                && !preg_match('/^%/', $condition_value)
            ) {
                $condition_value = '%' . $condition_value . '%';
            }


            if (is_integer(strpos($condition_value, "%"))) {
                $condition_value = processTitle($condition_value);
            }

            if ($rec['CONDITION'] == 1 && $value == $condition_value) {
                $status = 1;
            } elseif ($rec['CONDITION'] == 2 && (float)$value >= (float)$condition_value) {
                $status = 1;
            } elseif ($rec['CONDITION'] == 3 && (float)$value < (float)$condition_value) {
                $status = 1;
            } elseif ($rec['CONDITION'] == 4 && $value != $condition_value) {
                $status = 1;
            } else {
                $status = 0;
            }

            if ($status == 1 && !$rec['ACTIVE_STATE']) {

                $rec['ACTIVE_STATE'] = 1;
                SQLUpdate('patterns', $rec);
                $condition_matched = 1;

            } elseif ($status == 0 && $rec['ACTIVE_STATE']) {

                $rec['ACTIVE_STATE'] = 0;
                SQLUpdate('patterns', $rec);

                if ($rec['SCRIPT_EXIT']) {
                    $this->runPatternExitAction($rec['ID'], $rec['SCRIPT_EXIT']);
                }
                //to-do: state exit script

            }


        } else {

            if ($rec['SKIPSYSTEM'] && !$from_user_id) {
                return 0;
            }

            $messages_qry = 1;
            if ($rec['SKIPSYSTEM']) {
                $messages_qry .= " AND shouts.MEMBER_ID!=0";
            }

            if (!$rec['PATTERN']) {
                $pattern = $rec['TITLE'];
            } else {
                $pattern = $rec['PATTERN'];
            }
            $pattern = str_replace("\r", '', $pattern);
            if ($pattern == '') {
                return 0;
            }

            if ($rec['EXECUTED'] > 0 && $rec['TIME_LIMIT'] && (time() - $rec['EXECUTED']) <= $rec['TIME_LIMIT']) {
                return 0;
            }


            $lines_pattern = explode("\n", $pattern);
            $total_lines = count($lines_pattern);
            if (!$rec['TIME_LIMIT']) {
                $messages = SQLSelect("SELECT MESSAGE FROM shouts WHERE $messages_qry ORDER BY ID DESC LIMIT " . (int)$total_lines);
                $messages = array_reverse($messages);
            } else {
                $start_from = time() - $rec['TIME_LIMIT'];
                $messages = SQLSelect("SELECT MESSAGE FROM shouts WHERE $messages_qry AND ADDED>=('" . date('Y-m-d H:i:s', $start_from) . "') ORDER BY ADDED");
            }

            $total = count($messages);
            if (!$total) {
                return 0;
            }

            $lines = array();
            for ($i = 0; $i < $total; $i++) {
                $lines[] = $messages[$i]['MESSAGE'];
            }
            $history = implode('@@@@', $lines);

            if ($total == 1 && $rec['USEMORPHY'] && file_exists(ROOT . "lib/phpmorphy/common.php")) {
                require_once(ROOT . "lib/phpmorphy/common.php");
                $opts = array(
                    // storage type, follow types supported
                    // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
                    // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
                    // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
                    'storage' => PHPMORPHY_STORAGE_MEM,
                    // Enable prediction by suffix
                    'predict_by_suffix' => true,
                    // Enable prediction by prefix
                    'predict_by_db' => true,
                    // TODO: comment this
                    'graminfo_as_text' => true,
                );
                $dir = ROOT . 'lib/phpmorphy/dicts';

                $lang = SETTINGS_SITE_LANGUAGE_CODE;

                try {
                    $morphy = new phpMorphy($dir, $lang, $opts);
                    $this->morphy =& $morphy;
                } catch (phpMorphy_Exception $e) {
                    die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
                }
                $words = explode(' ', $lines[0]);
                $base_forms = array();
                $total = count($words);
                for ($i = 0; $i < $total; $i++) {
                    if (!preg_match('/[\(\)\+\.]/', $words[$i])) {
                        $Word = mb_strtoupper($words[$i], 'UTF-8');
                        $base_forms[$i] = $morphy->getBaseForm($Word);
                    } else {
                        $base_forms[$i] = array($words[$i]);
                    }
                }
                $combos = $this->generate_combinations($base_forms);
                $total = count($combos);
                for ($i = 0; $i < $total; $i++) {
                    $lines[] = implode(' ', $combos[$i]);
                }
            }
            $check = implode('@@@@', $lines_pattern);
            if (preg_match('/' . $check . '/isu', implode('@@@@', $lines), $matches)) {
                $condition_matched = 1;
            }


        }


        if ($condition_matched) {

            DebMes("Pattern matched: " . $rec['TITLE'], 'patterns');

            $is_common = 0;
            if ($rec['PARENT_ID']) {
                $parent_rec = SQLSelectOne("SELECT IS_COMMON_CONTEXT FROM patterns WHERE ID='" . $rec['PARENT_ID'] . "'");
                $is_common = (int)$parent_rec['IS_COMMON_CONTEXT'];
            }

            /*
            if (context_getcurrent()) {
                $history = context_get_history() . ' ' . $history;
            }
            */

            if ($rec['IS_CONTEXT']) {
                context_activate($rec['ID'], 1, $history,$from_user_id);
            } elseif ($rec['MATCHED_CONTEXT_ID']) {
                context_activate($rec['MATCHED_CONTEXT_ID'], 0, $history,$from_user_id);
            } elseif (!$is_common) {
                context_activate(0,0,'',$from_user_id);
            }

            $rec['LOG'] = date('Y-m-d H:i:s') . ' Pattern matched' . "\n" . $rec['LOG'];
            $rec['EXECUTED'] = time();
            SQLUpdate('patterns', $rec);
            $pattern_matched = 1;
            $this_pattern_matched = 1;

            $sub_patterns_matched = 0;

            if ($rec['IS_CONTEXT']) {
                $sub_patterns = SQLSelect("SELECT ID, IS_LAST FROM patterns WHERE PARENT_ID='" . $rec['ID'] . "' ORDER BY PRIORITY DESC, TITLE");
                $total = count($sub_patterns);
                for ($i = 0; $i < $total; $i++) {
                    if ($this->checkPattern($sub_patterns[$i]['ID'], $from_user_id)) {
                        $sub_patterns_matched = 1;
                        if ($sub_patterns[$i]['IS_LAST']) {
                            break;
                        }
                    }
                }
            }

            if (!$sub_patterns_matched) {
                $this->runPatternAction($rec['ID'], $matches, $history, $from_user_id);
            }

            if ($rec['ONETIME']) {
                SQLExec("DELETE FROM patterns WHERE ID='" . $rec['ID'] . "'");
            }

        } else {
            $this_pattern_matched = 0;
        }

        return $this_pattern_matched;
    }

    /**
     * patterns delete record
     *
     * @access public
     */
    function delete_patterns($id)
    {
        $rec = SQLSelectOne("SELECT * FROM patterns WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM patterns WHERE ID='" . $rec['ID'] . "'");
    }

    function buildTree_patterns($res, $parent_id = 0, $level = 0)
    {
        $total = count($res);
        $res2 = array();
        for ($i = 0; $i < $total; $i++) {
            if ($res[$i]['PARENT_ID'] == $parent_id) {
                $res[$i]['LEVEL'] = $level;
                $res[$i]['RESULT'] = $this->buildTree_patterns($res, $res[$i]['ID'], ($level + 1));
                $res2[] = $res[$i];
            }
        }
        $total2 = count($res2);
        if ($total2) {
            return $res2;
        }
    }

    function processSubscription($event, &$details)
    {
        if ($event == 'SAY' || $event == 'COMMAND') {
            $member_id = (int)$details['member_id'];

            global $context_user_id;
            $context_user_id = $member_id;

            $res = $this->checkAllPatterns($member_id);
            if ($event == 'COMMAND' && $res) {
                $details['BREAK'] = true;
                $details['PROCESSED'] = true;
            }
        }
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
        subscribeToEvent($this->name, 'SAY');
        subscribeToEvent($this->name, 'COMMAND');
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
        SQLDropTable('patterns');
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
        patterns - Patterns
        */
        $data = <<<EOD
 patterns: ID int(10) unsigned NOT NULL auto_increment
 patterns: TITLE varchar(255) NOT NULL DEFAULT ''
 patterns: PATTERN text
 patterns: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 patterns: SCRIPT text
 patterns: SCRIPT_EXIT text
 patterns: LOG text
 patterns: TIME_LIMIT int(10) NOT NULL DEFAULT '0'
 patterns: EXECUTED int(10) NOT NULL DEFAULT '0'
 patterns: IS_CONTEXT int(3) NOT NULL DEFAULT '0'
 patterns: IS_COMMON_CONTEXT int(3) NOT NULL DEFAULT '0'
 patterns: MATCHED_CONTEXT_ID int(10) NOT NULL DEFAULT '0'
 patterns: TIMEOUT int(10) NOT NULL DEFAULT '0'
 patterns: TIMEOUT_CONTEXT_ID int(10) NOT NULL DEFAULT '0'
 patterns: TIMEOUT_SCRIPT text
 patterns: PARENT_ID int(10) NOT NULL DEFAULT '0'
 patterns: IS_LAST int(3) NOT NULL DEFAULT '0'
 patterns: SKIPSYSTEM int(3) NOT NULL DEFAULT '0'
 patterns: ONETIME int(3) NOT NULL DEFAULT '0'
 patterns: USEMORPHY int(3) NOT NULL DEFAULT '0'
 patterns: PRIORITY int(10) NOT NULL DEFAULT '0'

 patterns: PATTERN_TYPE int(3) NOT NULL DEFAULT '0'
 patterns: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 patterns: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 patterns: CONDITION int(3) NOT NULL DEFAULT '0'
 patterns: CONDITION_VALUE varchar(255) NOT NULL DEFAULT ''
 patterns: LATEST_VALUE varchar(255) NOT NULL DEFAULT ''
 patterns: ACTIVE_STATE int(3) NOT NULL DEFAULT '0'


EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgRGVjIDEzLCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
