<?php
/**
 * actions_log
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 15:02:10 [Feb 16, 2018])
 */
//
//
class actions_log extends module
{
    /**
     * actions_log
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "actions_log";
        $this->title = "<#LANG_MODULE_ACTIONS_LOG#>";
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
        if ($this->data_source == 'actions_log' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_actions_log') {
                if (gr('clear_history')) {
                    SQLExec("TRUNCATE TABLE actions_log;");
                    $this->redirect("?");
                }
                $this->search_actions_log($out);
            }
            if ($this->view_mode == 'edit_actions_log') {
                $this->edit_actions_log($out, $this->id);
            }
            if ($this->view_mode == 'delete_actions_log') {
                $this->delete_actions_log($this->id);
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
     * actions_log search
     *
     * @access public
     */
    function search_actions_log(&$out)
    {
        require(DIR_MODULES . $this->name . '/actions_log_search.inc.php');
    }

    /**
     * actions_log edit/add
     *
     * @access public
     */
    function edit_actions_log(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/actions_log_edit.inc.php');
    }

    /**
     * actions_log delete record
     *
     * @access public
     */
    function delete_actions_log($id)
    {
        $rec = SQLSelectOne("SELECT * FROM actions_log WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM actions_log WHERE ID='" . $rec['ID'] . "'");
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
        SQLExec('DROP TABLE IF EXISTS actions_log');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data = '')
    {
        /*
        actions_log -
        */
        $data = <<<EOD
 actions_log: ID int(10) unsigned NOT NULL auto_increment
 actions_log: ADDED datetime 
 actions_log: ACTION_TYPE varchar(100) NOT NULL DEFAULT '' 
 actions_log: TITLE varchar(255) NOT NULL DEFAULT ''
 actions_log: USER varchar(255) NOT NULL DEFAULT ''
 actions_log: TERMINAL varchar(255) NOT NULL DEFAULT ''
 actions_log: IP varchar(100) NOT NULL DEFAULT ''
 actions_log: INDEX (ACTION_TYPE)  
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDE2LCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
