<?php
/**
 * Scripts
 *
 * Scripts
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <sergejey@gmail.com> https://majordomohome.com/
 * @version 0.4 (wizard, 18:09:04 [Sep 13, 2010])
 */
//
//
class scripts extends module
{
    /**
     * scripts
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "scripts";
        $this->title = "<#LANG_MODULE_SCRIPTS#>";
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
    function saveParams($data = 1)
    {
        $data = array();
        if (isset($this->id)) {
            $data["id"] = $this->id;
        }
        if (isset($this->view_mode)) {
            $data["view_mode"] = $this->view_mode;
        }
        if (isset($this->edit_mode)) {
            $data["edit_mode"] = $this->edit_mode;
        }
        if (isset($this->tab)) {
            $data["tab"] = $this->tab;
        }
        return parent::saveParams($data);
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
        global $data_source;
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

        if (isset($data_source)) {
            $this->data_source = $data_source;
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
        if (isset($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (isset($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['DATA_SOURCE'] = $this->data_source;
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
     * Title
     *
     * Description
     *
     * @access public
     */
    function runScript($id, $params = '')
    {

        verbose_log("Script [" . $id . "] (" . is_array($params) ? json_encode($params) : '' . ")");

        $rec = SQLSelectOne("SELECT * FROM scripts WHERE ID='" . (int)$id . "' OR TITLE = '" . DBSafe($id) . "'");
        if (isset($rec['ID'])) {
            $update_rec = array('ID' => $rec['ID']);
            $update_rec['EXECUTED'] = date('Y-m-d H:i:s');
            if (defined('CALL_SOURCE')) {
                $source = CALL_SOURCE;
            } else {
                $source = urldecode($_SERVER['REQUEST_URI']);
            }
            if (mb_strlen($source) > 250) {
                $source = mb_substr($source, 0, 250) . '...';
            }
            $update_rec['EXECUTED_SRC'] = $source;
            if ($params) {
                $update_rec['EXECUTED_PARAMS'] = json_encode($params, JSON_UNESCAPED_UNICODE);
                if (mb_strlen($update_rec['EXECUTED_PARAMS']) > 250) {
                    $update_rec['EXECUTED_PARAMS'] = mb_substr($update_rec['EXECUTED_PARAMS'], 0, 250);
                }
            }
            SQLUpdate('scripts', $update_rec);

            if (isItPythonCode($rec['CODE'])) {
                python_run_code($rec['CODE'], $params);
            } else {
                try {
                    $code = trim($rec['CODE']);
                    if ($code != '') {
                        setEvalCode($code);
                        $success = eval($code);
                    } else {
                        $success = true;
                    }
                    if ($success === false) {
                        //getLogger($this)->error(sprintf('Error in script "%s". Code: %s', $rec['TITLE'], $code));
                        registerError('script', sprintf('Error in script "%s". Code: %s', $rec['TITLE'], $code));
                    }
                    return $success;
                } catch (Exception $e) {
                    //getLogger($this)->error(sprintf('Error in script "%s"', $rec['TITLE']), $e);
                    registerError('script', sprintf('Error in script "%s": ' . $e->getMessage(), $rec['TITLE']));
                }
            }

        }
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

        if ($this->mode == 'scheduled') {
            $this->checkScheduledScripts();
        }

        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'scripts' || $this->data_source == '') {

            if ($this->view_mode == 'multiple') {
                $this->export($out);
            }

            if ($this->view_mode == 'import') {
                global $file;
                $this->import($out, $file);
                $this->redirect("?");
            }

            if ($this->view_mode == '' || $this->view_mode == 'search_scripts') {
                $this->search_scripts($out);
            }
            if ($this->view_mode == 'run_script') {
                $this->runScript($this->id);
                exit;
                //$this->redirect("?");
            }

            if ($this->view_mode == 'clone' && $this->id) {
                $this->clone_script($this->id);
            }

            if ($this->view_mode == 'edit_scripts') {
                $this->edit_scripts($out, $this->id);
            }
            if ($this->view_mode == 'delete_scripts') {
                $this->delete_scripts($this->id);
                $this->redirect("?");
            }
        }

        if ($this->data_source == 'categories') {
            if ($this->view_mode == '' || $this->view_mode == 'search_categories') {
                //$this->search_scripts($out);
                $result = SQLSelect("SELECT * FROM script_categories ORDER BY TITLE");
                if ($result) {
                    $out['RESULT'] = $result;
                }
            }
            if ($this->view_mode == 'edit_categories') {
                $this->edit_categories($out, $this->id);
            }
            if ($this->view_mode == 'delete_categories') {
                $this->delete_categories($this->id);
                $this->redirect("?data_source=" . $this->data_source);
            }
        }

    }


    function export(&$out)
    {
        $ids = gr('ids');
        if (is_array($ids)) {
            $ids[] = 0;
        } else {
            $ids = array(0);
        }
        $fields = implode(',', array(
            'scripts.ID', 'scripts.TITLE', 'scripts.DESCRIPTION', 'CODE', 'RUN_PERIODICALLY', 'RUN_DAYS', 'RUN_TIME',
            'AUTO_LINK', 'LINKED_OBJECT', 'LINKED_PROPERTY', 'script_categories.TITLE as CATEGORY_TITLE'
        ));
        $scripts = SQLSelect("SELECT $fields FROM scripts LEFT JOIN script_categories ON scripts.CATEGORY_ID=script_categories.ID WHERE scripts.ID IN (" . implode(',', $ids) . ")");
        $total = count($scripts);
        $result = array();
        for ($i = 0; $i < $total; $i++) {
            $rec = $scripts[$i];
            $blockly_sysname = 'script' . $scripts[$i]['ID'];
            $blockly_rec = SQLSelectOne("SELECT * FROM blockly_code WHERE SYSTEM_NAME='" . $blockly_sysname . "'");
            if ($blockly_rec['ID'] && $blockly_rec['CODE_TYPE'] == 1) {
                $rec['BLOCKLY_CODE_TYPE'] = $blockly_rec['CODE_TYPE'];
                $rec['BLOCKLY_CODE'] = $blockly_rec['CODE'];
                $rec['BLOCKLY_XML'] = $blockly_rec['XML'];
            }
            unset($rec['ID']);
            $result['SCRIPTS'][] = $rec;
        }

        if ($total == 1) {
            $filename = 'script_' . $result['SCRIPTS'][0]['TITLE'] . '_' . date('Y_m_d__H_i') . '.json';
        } else {
            $filename = 'scripts_' . date('Y_m_d__H_i') . '.json';
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . ($filename) . '"');
        echo json_encode($result);
        exit;
    }

    function import(&$out, $file)
    {
        if (file_exists($file)) {
            $data = json_decode(LoadFile($file), true);
            if (is_array($data['SCRIPTS'])) {
                $scripts = $data['SCRIPTS'];
                $total = count($scripts);
                for ($i = 0; $i < $total; $i++) {
                    $script_rec = SQLSelectOne("SELECT * FROM scripts WHERE TITLE LIKE '" . DBSafe($scripts[$i]['TITLE']) . "'");
                    if (!$script_rec['ID']) {
                        $script_rec = array('TITLE' => $scripts[$i]['TITLE']);
                        $script_rec['ID'] = SQLInsert('scripts', $script_rec);
                        if ($scripts[$i]['CATEGORY_TITLE']) {
                            $category_rec = SQLSelectOne("SELECT * FROM script_categories WHERE TITLE LIKE '" . DBSafe($scripts[$i]['CATEGORY_TITLE']) . "'");
                            if (!$category_rec['ID']) {
                                $category_rec = array('TITLE' => $scripts[$i]['CATEGORY_TITLE']);
                                $category_rec['ID'] = SQLInsert('script_categories', $category_rec);
                            }
                        }
                        if ($script_rec['CATEGORY_ID'] != $category_rec['ID']) {
                            $script_rec['CATEGORY_ID'] = $category_rec['ID'];
                            SQLUpdate('scripts', $script_rec);
                        }
                    }
                    $script_rec['CODE'] = $scripts[$i]['CODE'];
                    $script_rec['DESCRIPTION'] = $scripts[$i]['DESCRIPTION'];
                    $script_rec['UPDATED'] = date('Y-m-d H:i:s');
                    $script_rec['RUN_PERIODICALLY'] = $scripts[$i]['RUN_PERIODICALLY'];
                    $script_rec['RUN_DAYS'] = $scripts[$i]['RUN_DAYS'];
                    $script_rec['RUN_TIME'] = $scripts[$i]['RUN_TIME'];
                    if ($scripts[$i]['AUTO_LINK']) {
                        $script_rec['AUTO_LINK'] = 1;
                        $script_rec['LINKED_OBJECT'] = $scripts[$i]['LINKED_OBJECT'];
                        $script_rec['LINKED_PROPERTY'] = $scripts[$i]['LINKED_PROPERTY'];
                    }
                    SQLUpdate('scripts', $script_rec);

                    $blockly_sysname = 'script' . $script_rec['ID'];
                    SQLExec("DELETE FROM blockly_code WHERE SYSTEM_NAME LIKE '" . $blockly_sysname . "'");
                    if ($scripts[$i]['BLOCKLY_CODE_TYPE']) {
                        $blockly_rec = array();
                        $blockly_rec['SYSTEM_NAME'] = $blockly_sysname;
                        $blockly_rec['CODE_TYPE'] = $scripts[$i]['BLOCKLY_CODE_TYPE'];
                        $blockly_rec['CODE'] = $scripts[$i]['BLOCKLY_CODE'];
                        $blockly_rec['XML'] = $scripts[$i]['BLOCKLY_XML'];
                        SQLInsert('blockly_code', $blockly_rec);
                    }

                }
            }

        }
        return;
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function clone_script($id)
    {
        $rec = SQLSelectOne("SELECT * FROM scripts WHERE ID='" . (int)$id . "'");
        $rec['TITLE'] .= '_copy';
        unset($rec['ID']);
        unset($rec['EXECUTED']);
        unset($rec['EXECUTED_SRC']);
        $rec['ID'] = SQLInsert('scripts', $rec);
        $this->redirect("?view_mode=edit_scripts&id=" . $rec['ID']);
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
     * scripts search
     *
     * @access public
     */
    function search_scripts(&$out)
    {
        require(DIR_MODULES . $this->name . '/scripts_search.inc.php');
    }

    /**
     * scripts edit/add
     *
     * @access public
     */
    function edit_scripts(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/scripts_edit.inc.php');
    }

    function edit_categories(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/categories_edit.inc.php');
    }

    /**
     * scripts delete record
     *
     * @access public
     */
    function delete_scripts($id)
    {
        $rec = SQLSelectOne("SELECT * FROM scripts WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM scripts WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function checkScheduledScripts()
    {
        $scripts = SQLSelect("SELECT ID, TITLE, RUN_DAYS, RUN_TIME FROM scripts WHERE RUN_PERIODICALLY=1 AND ((UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(EXECUTED))>60 OR IsNull(EXECUTED))");

        $total = count($scripts);
        for ($i = 0; $i < $total; $i++) {
            $rec = $scripts[$i];
            if ($rec['RUN_DAYS'] == '') {
                continue;
            }
            $run_days = explode(',', $rec['RUN_DAYS']);
            $today = date('w');
            if (!in_array($today, $run_days)) {
                continue;
            }
            $tm = strtotime(date('Y-m-d') . ' ' . $rec['RUN_TIME'] . ':00');
            $diff = time() - $tm;
            if ($diff < 0 || $diff > 60) {
                continue;
            }
            DebMes("Running scheduled script " . $rec['TITLE'], 'scripts');
            SQLExec("UPDATE scripts SET EXECUTED='" . date('Y-m-d H:i:s') . "' WHERE ID='" . $rec['ID'] . "'");
            runScriptSafe($rec['TITLE']);
            $rec['DIFF'] = $diff;
        }
    }

    function propertySetHandle($object, $property, $value)
    {
        $scripts = SQLSelect("SELECT ID FROM scripts WHERE AUTO_LINK=1 AND LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND LINKED_PROPERTY LIKE '" . DBSafe($property) . "'");
        $total = count($scripts);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                $this->runScript($scripts[$i]['ID'], array('VALUE' => $value));
            }
        }
    }

    function processSubscription($event, &$details)
    {
        if ($event == 'COMMAND' && $details['member_id']) {
            $command = $details['message'];
            $script = SQLSelectOne("SELECT ID FROM scripts WHERE TITLE LIKE '" . DBSafe($command) . "'");
            if ($script['ID']) {
                $this->runScript($script['ID']);
                $details['PROCESSED'] = 1;
                $details['BREAK'] = 1;
            }
        }
    }


    function delete_categories($id)
    {
        $rec = SQLSelectOne("SELECT * FROM script_categories WHERE ID='$id'");
        // some action for related tables
        SQLExec("UPDATE scripts SET CATEGORY_ID=0 WHERE CATEGORY_ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM script_categories WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($parent_name = "")
    {
        parent::install($parent_name);
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
        SQLDropTable('scripts');
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
        scripts - Scripts
        */
        $data = <<<EOD
 scripts: ID int(10) unsigned NOT NULL auto_increment
 scripts: TITLE varchar(255) NOT NULL DEFAULT ''
 scripts: DESCRIPTION text
 scripts: CODE text
 scripts: TYPE int(3) unsigned NOT NULL DEFAULT 0
 scripts: CATEGORY_ID int(10) unsigned NOT NULL DEFAULT 0
 scripts: XML text
 scripts: UPDATED datetime
 scripts: EXECUTED datetime
 scripts: EXECUTED_PARAMS varchar(255)
 scripts: EXECUTED_SRC varchar(255)
 scripts: RUN_PERIODICALLY int(3) unsigned NOT NULL DEFAULT 0
 scripts: RUN_DAYS char(30) NOT NULL DEFAULT ''
 scripts: RUN_TIME char(30) NOT NULL DEFAULT ''
 scripts: AUTO_LINK int(3) unsigned NOT NULL DEFAULT 0 
 scripts: AUTO_LINK_AVAILABLE int(3) unsigned NOT NULL DEFAULT 0 
 scripts: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 scripts: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''

 script_categories: ID int(10) unsigned NOT NULL auto_increment
 script_categories: TITLE varchar(255) NOT NULL DEFAULT ''


 safe_execs: ID int(10) unsigned NOT NULL auto_increment
 safe_execs: COMMAND text
 safe_execs: ON_COMPLETE text
 safe_execs: EXCLUSIVE int(3) NOT NULL DEFAULT 0
 safe_execs: PRIORITY int(10) NOT NULL DEFAULT 0
 safe_execs: ADDED datetime
 
EOD;
        parent::dbInstall($data);

        $scripts = SQLSelect("SELECT * FROM scripts");
        $total = count($scripts);
        for ($i = 0; $i < $total; $i++) {
            if ($scripts[$i]['EXECUTED'] == '0000-00-00 00:00:00') {
                SQLExec("UPDATE scripts SET EXECUTED=NOW() WHERE ID=" . $scripts[$i]['ID']);
            }
        }

        subscribeToEvent('scripts', 'COMMAND');

    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDEzLCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
